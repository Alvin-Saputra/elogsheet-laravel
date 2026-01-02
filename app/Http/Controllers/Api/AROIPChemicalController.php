<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateAroipChemicalRequest;
use App\Http\Requests\UpdateAroipApprovalRequest;
use App\Http\Requests\UpdateAroipChemicalRequest;
use App\Models\AROIPChemicalDetail;
use App\Models\AROIPChemicalHeader;
use App\Models\COADetail;
use App\Models\COAHeader;
use App\Models\MControlnumber;
use App\Models\MDataFormNo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Schema;

class AROIPChemicalController extends Controller
{
    /**
     * GET /api/ariopchemical
     * Get all AROIP chemical data with filtering options
     */
    public function get(Request $request)
    {
        $query = AROIPChemicalHeader::with(['details', 'coa.details']);

        // Filter by date_issued
        if ($request->filled('entry_date')) {
            $query->whereDate('entry_date', $request->entry_date);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('entry_date', [
                $request->start_date,
                $request->end_date,
            ]);
        }

        // Order by most recent first
        $query->orderBy('entry_date', 'desc');

        $result = $query->get();

        if ($request->anyFilled(['entry_date', 'start_date', 'end_date']) && $result->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No data found for the given filters.',
            ], 404);
        }

        // Format the response with 'analytical' and 'coa' keys
        $formattedResult = $result->map(function ($item) {
            return [
                'analytical' => array_merge(
                    $item->only([
                        'id',
                        'id_coa',
                        'no_ref_coa',
                        'company',
                        'plant',
                        'material',
                        'quantity',
                        'analyst',
                        'supplier',
                        'police_no',
                        'batch_lot',
                        'status',
                        'flag',
                        'entry_by',
                        'entry_date',
                        'prepared_by',
                        'prepared_date',
                        'prepared_status',
                        'prepared_status_remarks',
                        'approved_by',
                        'approved_date',
                        'approved_status',
                        'approved_status_remarks',
                        'updated_by',
                        'updated_date',
                        'form_no',
                        'date_issued',
                        'revision_no',
                        'revision_date',
                        'deleted_at',
                        'date',
                        'exp_date',
                    ]),
                    ['details' => $item->details]
                ),
                'coa' => $item->coa ?: null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedResult,
        ]);
    }

    /**
     * POST /api/ariopchemical
     * Create AROIP + COA in a single transaction.
     */
    public function create(CreateAroipChemicalRequest $request)
    {
        try {
            DB::beginTransaction();
            $user = $request->user()->getDisplayNameAttribute();
            $data = $request->validated();

            // Get form data from m_data_form_no where f_id = 17
            $dataForm = MDataFormNo::find(17);
            if (!$dataForm) {
                return response()->json([
                    'success' => false,
                    'message' => 'Form configuration not found (f_id: 17)',
                ], 400);
            }

            // Handle COA creation if coa data is provided
            $coaNoDoc = null;
            if (isset($data['coa'])) {
                // Get control number for COA (using prefix 'Q11' and plantid 'PS21')
                $coaControl = MControlnumber::where('prefix', 'Q11')
                    ->where('plantid', 'PS21')
                    ->lockForUpdate()
                    ->first();

                if (!$coaControl) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Control number configuration not found for COA',
                    ], 400);
                }

                // Generate COA document number
                $coaNextNum = intval($coaControl->autonumber ?? 0) + 1;
                $paddedNum = str_pad($coaNextNum, 6, '0', STR_PAD_LEFT);
                $year = (string) $coaControl->accountingyear;
                $suffix = 'COA' . ($coaControl->plantid ?? '') . $year . $paddedNum;
                $coaId = ($coaControl->prefix ?? 'Q11') . $suffix;

                // Create COA header
                $coaHeader = COAHeader::create([
                    ...$data['coa'],
                    'id' => $coaId,
                    'issue_by' => $user,
                    'issue_date' => now(),
                    'updated_by' => $user,
                    'updated_date' => now(),
                ]);

                // Create COA details
                if (isset($data['coa']['details']) && is_array($data['coa']['details'])) {
                    foreach ($data['coa']['details'] as $index => $detail) {
                        $detailId = $coaId . 'D' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);

                        COADetail::create([
                            ...$detail,
                            'id' => $detailId,
                            'id_hdr' => $coaId,
                        ]);
                    }
                }

                // Update COA control number
                DB::table('m_controlnumber')
                    ->where('prefix', $coaControl->prefix)
                    ->where('plantid', $coaControl->plantid)
                    ->update(['autonumber' => $coaNextNum]);

                $coaNoDoc = $coaHeader->id;
            }

            // Get control number for AROIP (using prefix 'Q11' and plantid 'PS21')
            $control = MControlnumber::where('prefix', 'Q11')
                ->where('plantid', 'PS21')
                ->lockForUpdate()
                ->first();

            if (!$control) {
                return response()->json([
                    'success' => false,
                    'message' => 'Control number configuration not found',
                ], 400);
            }

            // Generate new AROIP document number
            $nextNum = intval($control->autonumber) + 1;
            $paddedNum = str_pad($nextNum, 6, '0', STR_PAD_LEFT);
            $headerId = $control->prefix . $control->plantid . $control->accountingyear . $paddedNum;

            // Create AROIP header
            $header = AROIPChemicalHeader::create([
                ...($data['analytical'] ?? $data),
                'id' => $headerId,
                'form_no' => $dataForm->f_code,
                'date_issued' => $dataForm->f_date_issued,
                'revision_no' => $dataForm->f_revision_no,
                'revision_date' => $dataForm->f_revision_date,
                'entry_by' => $user,
                'entry_date' => now(),
                'id_coa' => $coaHeader->id,
                'updated_by' => $user,
                'updated_date' => now(),
            ]);

            // Create AROIP details
            if (isset($data['analytical']['details']) && is_array($data['analytical']['details'])) {
                foreach ($data['analytical']['details'] as $index => $detail) {
                    $detailId = $headerId . 'D' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
                    AROIPChemicalDetail::create([
                        ...$detail,
                        'id' => $detailId,
                        'id_hdr' => $headerId,
                    ]);
                }
            }

            // Update AROIP control number
            DB::table('m_controlnumber')
                ->where('prefix', $control->prefix)
                ->where('plantid', $control->plantid)
                ->update(['autonumber' => $nextNum]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'AROIP Chemical created successfully',
                'data' => [
                    'aroip_header_id' => $header->id,
                    'coa_header_id' => $coaHeader->id ?? null,
                    'aroip_detail_ids' => $header->details->pluck('id'),
                    'coa_detail_ids' => isset($coaHeader) ? $coaHeader->details->pluck('id') : [],
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create AROIP Chemical',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * PUT /api/ariopchemical/{id}
     * Update AROIP chemical record
     */
    public function update(UpdateAroipChemicalRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $user = $request->user()->getDisplayNameAttribute();
            $data = $request->validated();

            // Accept both shapes: root-level fields + details OR grouped under 'analytical'
            $analyticalPayload = $data['analytical'] ?? $data;

            $header = AROIPChemicalHeader::with(['details', 'coa.details'])->findOrFail($id);

            // ---------- A. update header ----------
            $header->update([
                'material' => $analyticalPayload['material'] ?? $header->material,
                'quantity' => $analyticalPayload['quantity'] ?? $header->quantity,
                'analyst' => $analyticalPayload['analyst'] ?? $header->analyst,
                'supplier' => $analyticalPayload['supplier'] ?? $header->supplier,
                'police_no' => $analyticalPayload['police_no'] ?? $header->police_no,
                'batch_lot' => $analyticalPayload['batch_lot'] ?? $header->batch_lot,
                'status' => $analyticalPayload['status'] ?? $header->status,
                'no_ref_coa' => $analyticalPayload['no_ref_coa'] ?? $header->no_ref_coa,
                'date' => $analyticalPayload['date'] ?? $header->date,
                'exp_date' => $analyticalPayload['exp_date'] ?? $header->exp_date,
                'form_no' => $data['form_no'] ?? $header->form_no,
                'updated_by' => $user,
                'updated_date' => now(),
                'revision_no' => DB::raw('COALESCE(revision_no, 0) + 1'),
                'revision_date' => now(),
            ]);

            // Helper to create deterministic increment id for details:
            $nextDetailIdForHeader = function ($headerId, $detailModel) {
                // find numeric suffix after last 'D' in existing ids
                $max = 0;
                $rows = $detailModel::where('id_hdr', $headerId)->pluck('id')->toArray();
                foreach ($rows as $rid) {
                    if (preg_match('/D(\d+)$/', $rid, $m)) {
                        $num = intval($m[1]);
                        if ($num > $max)
                            $max = $num;
                    }
                }
                return $headerId . 'D' . str_pad($max + 1, 3, '0');
            };

            // ---------- B. sync analytical (AROIP) details ----------
            if (isset($analyticalPayload['details']) && is_array($analyticalPayload['details'])) {
                $existingIds = $header->details->pluck('id')->toArray();
                $receivedIds = [];

                foreach ($analyticalPayload['details'] as $row) {
                    // if id provided -> update existing
                    if (!empty($row['id'])) {
                        $detail = AROIPChemicalDetail::where('id', $row['id'])
                            ->where('id_hdr', $header->id)
                            ->first();

                        if (!$detail) {
                            DB::rollBack();
                            return response()->json([
                                'success' => false,
                                'message' => "Analytical detail id {$row['id']} not found for header {$header->id}."
                            ], 404);
                        }

                        $detail->update([
                            'parameter' => $row['parameter'] ?? $detail->parameter,
                            'specification_min' => $row['specification_min'] ?? $detail->specification_min,
                            'specification_max' => $row['specification_max'] ?? $detail->specification_max,
                            'result_min' => $row['result_min'] ?? $detail->result_min,
                            'result_max' => $row['result_max'] ?? $detail->result_max,
                            'status_ok' => isset($row['status_ok']) ? strtoupper($row['status_ok']) : $detail->status_ok,
                            'remark' => $row['remark'] ?? $detail->remark,
                        ]);

                        $receivedIds[] = $detail->id;
                    } else {
                        // no id -> create new with deterministic incremental id
                        $newId = $nextDetailIdForHeader($header->id, AROIPChemicalDetail::class);

                        $created = AROIPChemicalDetail::create([
                            'id' => $newId,
                            'id_hdr' => $header->id,
                            'parameter' => $row['parameter'] ?? null,
                            'specification_min' => $row['specification_min'] ?? null,
                            'specification_max' => $row['specification_max'] ?? null,
                            'result_min' => $row['result_min'] ?? null,
                            'result_max' => $row['result_max'] ?? null,
                            'status_ok' => isset($row['status_ok']) ? strtoupper($row['status_ok']) : null,
                            'remark' => $row['remark'] ?? null,
                        ]);

                        $receivedIds[] = $created->id;
                    }
                }

                // delete details removed by the client (sync)
                $toDelete = array_diff($existingIds, $receivedIds);
                if (!empty($toDelete)) {
                    AROIPChemicalDetail::whereIn('id', $toDelete)->delete();
                }
            }

            // ---------- C. update/sync COA header & details ----------
            if (isset($data['coa'])) {
                // If there is no related COA header on AROIP, either return error or create COA.
                // Here we require an existing COA header; you can alter to create if you prefer.
                $coaHeader = $header->coa;
                if (!$coaHeader) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'No related COA header exists for this AROIP. Create COA first or send id_coa on header.'
                    ], 422);
                }

                $coaPayload = $data['coa'];
                $coaHeader->update([
                    'no_doc' => $coaPayload['no_doc'] ?? $coaHeader->no_doc,
                    'product' => $coaPayload['product'] ?? $coaHeader->product,
                    'grade' => $coaPayload['grade'] ?? $coaHeader->grade,
                    'packing' => $coaPayload['packing'] ?? $coaHeader->packing,
                    'quantity' => $coaPayload['quantity'] ?? $coaHeader->quantity,
                    'tanggal_pengiriman' => $coaPayload['tanggal_pengiriman'] ?? $coaHeader->tanggal_pengiriman,
                    'vehicle' => $coaPayload['vehicle'] ?? $coaHeader->vehicle,
                    'lot_no' => $coaPayload['lot_no'] ?? $coaHeader->lot_no,
                    'production_date' => $coaPayload['production_date'] ?? $coaHeader->production_date,
                    'expired_date' => $coaPayload['expired_date'] ?? $coaHeader->expired_date,
                    'updated_by' => $user,
                    'updated_date' => now(),
                ]);

                if (isset($coaPayload['details']) && is_array($coaPayload['details'])) {
                    $existingIds = $coaHeader->details->pluck('id')->toArray();
                    $receivedIds = [];

                    foreach ($coaPayload['details'] as $row) {
                        if (!empty($row['id'])) {
                            $detail = COADetail::where('id', $row['id'])
                                ->where('id_hdr', $coaHeader->id)
                                ->first();

                            if (!$detail) {
                                DB::rollBack();
                                return response()->json([
                                    'success' => false,
                                    'message' => "COA detail id {$row['id']} not found for COA {$coaHeader->id}."
                                ], 404);
                            }

                            $detail->update([
                                'parameter' => $row['parameter'] ?? $detail->parameter,
                                'actual_min' => $row['actual_min'] ?? $detail->actual_min,
                                'actual_max' => $row['actual_max'] ?? $detail->actual_max,
                                'standard_min' => $row['standard_min'] ?? $detail->standard_min,
                                'standard_max' => $row['standard_max'] ?? $detail->standard_max,
                                'method' => $row['method'] ?? $detail->method,
                            ]);

                            $receivedIds[] = $detail->id;
                        } else {
                            // create new COA detail with deterministic id
                            $newId = $nextDetailIdForHeader($coaHeader->id, COADetail::class);

                            $created = COADetail::create([
                                'id' => $newId,
                                'id_hdr' => $coaHeader->id,
                                'parameter' => $row['parameter'] ?? null,
                                'actual_min' => $row['actual_min'] ?? null,
                                'actual_max' => $row['actual_max'] ?? null,
                                'standard_min' => $row['standard_min'] ?? null,
                                'standard_max' => $row['standard_max'] ?? null,
                                'method' => $row['method'] ?? null,
                            ]);

                            $receivedIds[] = $created->id;
                        }
                    }

                    // delete removed COA details
                    $toDelete = array_diff($existingIds, $receivedIds);
                    if (!empty($toDelete)) {
                        COADetail::whereIn('id', $toDelete)->delete();
                    }
                }
            }

            DB::commit();

            // return fresh header with relationships
            $header->refresh();
            $header->load(['details', 'coa.details']);

            return response()->json([
                'success' => true,
                'message' => 'AROIP and COA updated successfully',
                'data' => $header,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Record not found.'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    /**
     * Generate a new COA detail id that is unique and fits the length of existing ids.
     * If existing COA detail ids show length >= 36 we will use UUIDs.
     * Otherwise we produce a compact id based on header + random suffix and ensure uniqueness.
     *
     * Throws an Exception if it cannot safely generate a unique id (prompt to increase column length).
     */
    private function generateCoaDetailId($coaHeader)
    {
        // existing ids from the header (in-memory)
        $existingIds = $coaHeader->details->pluck('id')->toArray();

        // determine current "max" id length observed in DB (if any)
        $maxExistingLen = 0;
        if (!empty($existingIds)) {
            $lengths = array_map('strlen', $existingIds);
            $maxExistingLen = max($lengths);
        }

        // If we see existing ids that are long enough, prefer UUIDs
        if ($maxExistingLen >= 36) {
            do {
                $candidate = (string) Str::uuid(); // 36 chars
            } while (COADetail::where('id', $candidate)->exists());
            return $candidate;
        }

        // Otherwise generate a compact id: HEADERPREFIX + 'D' + short hex suffix
        // choose a fallback max length; if we observed lengths use that, otherwise use 24
        $targetMaxLen = $maxExistingLen > 0 ? $maxExistingLen : 24;

        $base = (string) $coaHeader->id;
        $attempt = 0;
        do {
            $attempt++;
            // short random suffix (6 hex chars)
            $suffix = substr(bin2hex(random_bytes(3)), 0, 6); // 6 chars
            // compute prefix length allowed
            $prefixLen = max(0, $targetMaxLen - 1 - strlen($suffix)); // minus 1 for 'D'
            $prefix = substr($base, 0, $prefixLen);
            $candidate = $prefix . 'D' . $suffix;

            // safety: if candidate already exists, loop (few attempts)
            if (!COADetail::where('id', $candidate)->exists()) {
                return $candidate;
            }
        } while ($attempt < 6);

        // If still colliding, suggest schema change (safer to increase column length and use UUIDs)
        throw new \Exception('Unable to generate unique COA detail id within current id length constraints. Consider increasing id column length to support UUIDs (VARCHAR(36)) or adjust id format.');
    }


    /**
     * Remove the specified AROIP Chemical record.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Find the record (including soft deleted ones)
            $header = AROIPChemicalHeader::withTrashed()->findOrFail($id);

            // Check if already soft deleted
            if ($header->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'AROIP Chemical record is already deleted.',
                ], 400);
            }

            // Soft delete the header
            $header->delete();

            AROIPChemicalDetail::where('id_hdr', $id)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'AROIP Chemical record has been deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'AROIP Chemical record not found.',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete AROIP Chemical record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Restore a soft-deleted AROIP Chemical record.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)
    {
        try {
            $header = AROIPChemicalHeader::onlyTrashed()->findOrFail($id);
            $header->restore();

            // Optionally restore related details
            AROIPChemicalDetail::onlyTrashed()
                ->where('id_hdr', $id)
                ->restore();

            return response()->json([
                'success' => true,
                'message' => 'AROIP Chemical record has been restored successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'AROIP Chemical record not found or not trashed',
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore AROIP Chemical record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Permanently delete the specified AROIP Chemical record.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceDelete($id)
    {
        try {
            DB::beginTransaction();

            $header = AROIPChemicalHeader::withTrashed()->findOrFail($id);

            // Permanently delete the details first
            AROIPChemicalDetail::where('id_hdr', $id)->forceDelete();

            // Then delete the header
            $header->forceDelete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'AROIP Chemical record has been permanently deleted.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'AROIP Chemical record not found',
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to permanently delete AROIP Chemical record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update approval status of AROIP Chemical record
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateApproval($id, UpdateAroipApprovalRequest $request)
    {
        try {
            DB::beginTransaction();
            $header = AROIPChemicalHeader::findOrFail($id);
            $user = auth()->user();
            $status = Str::ucfirst(strtolower($request->input('status')));
            $remark = $request->input('remarks');

            $userRoles = $user->roles;
            $isSuccess = $this->processApprovalStatus($header, $status, $remark, $user->username, $userRoles);

            if (!$isSuccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update approval status',
                ], 403);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Approval status updated successfully',
                'data' => [
                    'id' => $header->id,
                    'status' => $status,
                    'remarks' => $remark,  // Include remarks in response for verification
                    'updated_by' => $user->username,
                    'updated_at' => now()->toDateTimeString(),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update approval status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process approval status update (helper method)
     */
    private function processApprovalStatus($header, $status, $remark, $username, $userRoles)
    {
        $LEAD_QC = ['LEAD', 'LEAD_QC'];
        $QC_Control_MGR = ['MGR', 'MGR_QC', 'ADM'];
        $fieldPrefix = '';

        if (in_array($userRoles, $QC_Control_MGR, true)) {
            $fieldPrefix = 'approved';
        } elseif (in_array($userRoles, $LEAD_QC, true)) {
            $fieldPrefix = 'prepared';
        } else {
            return false;
        }

        $updates = [
            "{$fieldPrefix}_status" => $status,
            "{$fieldPrefix}_by" => $username,
            "{$fieldPrefix}_date" => now(),
            "{$fieldPrefix}_status_remarks" => $remark,
            'updated_by' => $username,
            'updated_date' => now(),
        ];

        // Only include role if the column exists
        if (Schema::hasColumn($header->getTable(), "{$fieldPrefix}_role")) {
            $updates["{$fieldPrefix}_role"] = json_encode($userRoles);
        }

        if ($fieldPrefix === 'approved') {
            $updates['status'] = $status;
        }

        return $header->update($updates);
    }
}
