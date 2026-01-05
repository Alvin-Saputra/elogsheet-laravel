<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateArosByVesselRequest;
use App\Http\Requests\UpdateArosByVesselApprovalRequest;
use App\Http\Requests\UpdateArosByVesselRequest;
use App\Models\AROSByVesselDetail;
use App\Models\AROSByVesselHeader;
use App\Models\MControlnumber;
use App\Models\MDataFormNo;
use Arr;
use DB;
use Illuminate\Http\Request;
use Schema;
use Str;

class AROSByVesselController extends Controller
{
    // -----------------------
    // Shared helpers / utils
    // -----------------------

    private function findHeaderWithId(string $id)
    {
        return AROSByVesselHeader::with(['details'])->findOrFail($id);
    }

    /**
     * Find highest numeric sequence used for details of a header (Dxxx suffix),
     * returns 0 when none found.
     */
    private function getMaxDetailSequence(string $headerId): int
    {
        $rows = AROSByVesselDetail::where('id_hdr', $headerId)->pluck('id')->toArray();
        $max = 0;
        foreach ($rows as $rid) {
            if (preg_match('/D(\d+)$/', $rid, $m)) {
                $num = intval($m[1]);
                if ($num > $max)
                    $max = $num;
            }
        }
        return $max;
    }

    /**
     * Generate a detail id that fits within the detail.id column length (default 16).
     * It will try sequential suffixes until it finds an unused id or throw an Exception.
     *
     * @param string $headerId  The header id (used as prefix)
     * @param int    $startSeq  Sequence to start with (normally 1)
     * @param int    $maxLen    Maximum total length of id (detail.id column length)
     * @param int    $seqDigits Number of digits for sequence (e.g. 3 -> D001)
     * @return string
     * @throws \Exception
     */
    private function makeDetailId(string $headerId, int $startSeq = 1, int $maxLen = 16, int $seqDigits = 3): string
    {
        $seq = max(1, (int) $startSeq);
        $attempt = 0;
        $maxAttempts = 10000; // safety cap

        do {
            $suffix = 'D' . str_pad($seq, $seqDigits, '0', STR_PAD_LEFT);
            $prefixLen = $maxLen - strlen($suffix);
            if ($prefixLen <= 0) {
                throw new \Exception("Header id too long to generate detail id within {$maxLen} chars");
            }
            // use mb_substr to be safe with multibyte (but IDs are ascii)
            $prefix = mb_substr($headerId, 0, $prefixLen);
            $candidate = $prefix . $suffix;

            if (!AROSByVesselDetail::where('id', $candidate)->exists()) {
                return $candidate;
            }

            $seq++;
            $attempt++;
        } while ($attempt < $maxAttempts);

        throw new \Exception('Unable to generate unique detail id within length constraints');
    }

    /**
     * Normalize roles value to an array and decide which prefix to use.
     * Returns ['prefix' => 'prepared'|'approved', 'roles' => array] or false when not allowed.
     */
    private function decidePrefixFromRoles($userRoles)
    {
        $LEAD_QC = ['LEAD', 'LEAD_QC'];
        $QC_Control_MGR = ['MGR', 'MGR_QC', 'ADM'];

        if (is_array($userRoles)) {
            $roles = array_map(fn($r) => strtoupper((string) $r), $userRoles);
        } else {
            $roles = array_filter(array_map('trim', preg_split('/[,\|;]+/', (string) $userRoles)));
            $roles = array_map(fn($r) => strtoupper((string) $r), $roles ?: []);
        }

        if (count(array_intersect($roles, $QC_Control_MGR)) > 0) {
            return ['prefix' => 'approved', 'roles' => $roles];
        }

        if (count(array_intersect($roles, $LEAD_QC)) > 0) {
            return ['prefix' => 'prepared', 'roles' => $roles];
        }

        return false;
    }

    /**
     * Process approval status. Works for both API and Web callers.
     */
    private function processApprovalStatus($header, $status, $remark, $user_name, $user_roles)
    {
        $decision = $this->decidePrefixFromRoles($user_roles);
        if ($decision === false) {
            return false;
        }

        $fieldPrefix = $decision['prefix'];
        $rolesArr = $decision['roles'];

        $status = is_string($status) ? Str::ucfirst(strtolower(trim($status))) : $status;

        $updates = [
            "{$fieldPrefix}_status" => $status,
            "{$fieldPrefix}_by" => $user_name,
            "{$fieldPrefix}_date" => now(),
            "{$fieldPrefix}_status_remarks" => $remark,
            'updated_by' => $user_name,
            'updated_date' => now(),
        ];

        if (Schema::hasColumn($header->getTable(), "{$fieldPrefix}_role")) {
            $updates["{$fieldPrefix}_role"] = json_encode($rolesArr);
        }

        if ($fieldPrefix === 'approved' && Schema::hasColumn($header->getTable(), 'status')) {
            $updates['status'] = $status;
        }

        return $header->update($updates);
    }

    // -----------------------
    // API: RESTful endpoints
    // -----------------------

    /**
     * GET /api/arosvess
     * optional filter: sampling_date (YYYY-MM-DD)
     */
    public function get(Request $request)
    {
        $query = AROSByVesselHeader::with(['details']);

        if ($request->filled('sampling_date')) {
            $query->whereDate('sampling_date', $request->sampling_date);
        }

        $query->orderBy('sampling_date', 'desc');

        $result = $query->get();

        if ($request->filled('sampling_date') && $result->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No data found for the given filters.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ], 200);
    }

    /**
     * POST /api/arosvess
     */
    public function create(CreateArosByVesselRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $user = $request->user()->getDisplayNameAttribute();

            // Resolve form metadata using menu_id
            $dataForm = null;
            if (!empty($data['menu_id'])) {
                $dataForm = MDataFormNo::where('is_menu', $data['menu_id'])->first();
            }

            // get control number for AROS by vessel
            $control = MControlnumber::where('prefix', 'Q20')
                ->where('plantid', $data['plant'])
                ->lockForUpdate()
                ->first();

            if (!$control) {
                return response()->json([
                    'success' => false,
                    'message' => 'Control number configuration not found for this plant/prefix.',
                ], 400);
            }

            $nextNum = intval($control->autonumber) + 1;
            $paddedNum = str_pad($nextNum, 6, '0', STR_PAD_LEFT);
            $headerId = $control->prefix . $control->plantid . $control->accountingyear . $paddedNum;

            // prepare header payload — exclude 'details' from mass assignment
            $headerPayload = Arr::except($data, ['details', 'detail', 'menu_id']);
            $headerPayload = array_merge($headerPayload, [
                'id' => $headerId,
                'entry_by' => $user,
                'entry_date' => now(),
                'updated_by' => $user,
                'updated_date' => now(),
            ]);

            if ($dataForm) {
                $headerPayload['form_no'] = $dataForm->f_code;
                $headerPayload['date_issued'] = $dataForm->f_date_issued;
                $headerPayload['revision_no'] = $dataForm->f_revision_no;
                $headerPayload['revision_date'] = $dataForm->f_revision_date;
            }

            $header = AROSByVesselHeader::create($headerPayload);

            // create details (use helper to ensure detail.id fits column length)
            $detailRows = $data['details'] ?? $data['detail'] ?? [];
            if (!empty($detailRows) && is_array($detailRows)) {
                $startSeq = $this->getMaxDetailSequence($header->id) + 1;
                foreach ($detailRows as $index => $row) {
                    $detailId = $this->makeDetailId($header->id, $startSeq + $index);
                    AROSByVesselDetail::create(array_merge(Arr::except($row, ['id']), [
                        'id' => $detailId,
                        'id_hdr' => $header->id,
                    ]));
                }
            }

            // persist new controlnumber
            DB::table('m_controlnumber')
                ->where('prefix', $control->prefix)
                ->where('plantid', $control->plantid)
                ->update(['autonumber' => $nextNum]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Analytical Result Outgoing Shipment By Vessel created successfully',
                'data' => ['header_id' => $header->id],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * PUT /api/arosvess/{id}
     *
     * Contract (Option B):
     * - For existing detail rows, client MUST send details[].id.
     * - For new rows, omit id (server will create one).
     */
    public function update(UpdateArosByVesselRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $data = $request->validated();
            $user = $request->user()->getDisplayNameAttribute();

            $header = AROSByVesselHeader::with('details')->find($id);
            if (!$header) {
                return response()->json([
                    'success' => false,
                    'message' => 'Header not found',
                ], 404);
            }

            $header->update(array_merge(Arr::except($data, ['details', 'detail', 'menu_id', 'hasil_analisa_komposit_palka']), [
                'updated_by' => $user,
                'updated_date' => now(),
                'revision_no' => DB::raw('COALESCE(revision_no, 0) + 1'),
                'revision_date' => now(),
            ]));

            if (!empty($data['details'])) {
                foreach ($data['details'] as $row) {
                    if (!empty($row['id'])) {
                        $detail = AROSByVesselDetail::where('id', $row['id'])
                            ->where('id_hdr', $header->id)
                            ->first();

                        if ($detail) {
                            $detail->update(
                                collect($row)->except('id')->toArray()
                            );
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'AROS By Vessel updated successfully',
                'data' => $header->fresh('details'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update AROS By Vessel',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /api/arosvess/{id}
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $header = AROSByVesselHeader::withTrashed()->findOrFail($id);

            if ($header->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Record already deleted.',
                ], 400);
            }

            $header->delete();
            AROSByVesselDetail::where('id_hdr', $id)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Record deleted successfully.',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Record not found.',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Update approval (uses UpdateArosByVesselApprovalRequest)
     */
    public function updateApproval($id, UpdateArosByVesselApprovalRequest $request)
    {
        try {
            DB::beginTransaction();

            $header = AROSByVesselHeader::findOrFail($id);

            $user = $request->user() ?? auth()->user();
            $status = Str::ucfirst(strtolower($request->input('status')));
            $remark = $request->input('remarks');

            $username = $user->username ?? ($user->name ?? (method_exists($user, 'getDisplayNameAttribute') ? $user->getDisplayNameAttribute() : null));
            $userRoles = $user->roles ?? null;

            $isSuccess = $this->processApprovalStatus($header, $status, $remark, $username, $userRoles);

            if (!$isSuccess) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'You do not have permission to update approval status'], 403);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Approval status updated successfully',
                'data' => [
                    'id' => $header->id,
                    'status' => $status,
                    'remarks' => $remark,
                    'updated_by' => $username,
                    'updated_at' => now()->toDateTimeString(),
                ],
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Record not found.'], 404);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update approval status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
