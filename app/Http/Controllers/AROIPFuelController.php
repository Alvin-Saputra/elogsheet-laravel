<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateAroipFuelRequest;
use App\Http\Requests\UpdateAroipApprovalRequest;
use App\Http\Requests\UpdateAroipFuelRequest;
use App\Models\AROIPFuelDetail;
use App\Models\AROIPFuelHeader;
use App\Models\MControlnumber;
use App\Models\MDataFormNo;
use App\Models\ROADetail;
use App\Models\ROAHeader;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Schema;

class AROIPFuelController extends Controller
{
    //
    // -----------------------
    // Shared helpers / utils
    // -----------------------
    //

    private function findHeaderWithId($id)
    {
        return AROIPFuelHeader::with(['details', 'roa.details'])->findOrFail($id);
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

        $status = is_string($status) ? ucfirst(strtolower(trim($status))) : $status;

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

    //
    // -----------------------
    // API: RESTful endpoints
    // -----------------------
    //

    /**
     * Create AROIP Fuel (API) — creates ROA + AROIP header + details within single transaction.
     */
    public function create(CreateAroipFuelRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = $request->user()->getDisplayNameAttribute();
            $data = $request->validated();

            $dataForm = MDataFormNo::find(18);
            if (!$dataForm) {
                throw new \Exception('Form configuration not found (f_id: 18)');
            }

            // === ROA creation (controlnumber locked)
            $roaControl = MControlnumber::where('prefix', 'Q12A')
                ->where('plantid', 'PS21')
                ->lockForUpdate()
                ->first();

            if (!$roaControl) {
                throw new \Exception('Control number configuration not found for ROA');
            }

            $roaNextNum = intval($roaControl->autonumber ?? 0) + 1;
            $paddedNum = str_pad($roaNextNum, 6, '0', STR_PAD_LEFT);
            $year = (string) $roaControl->accountingyear;
            $suffix = 'ROA' . ($roaControl->plantid ?? '') . $year . $paddedNum;
            $roaId = ($roaControl->prefix ?? 'Q12A') . $suffix;

            $roaHeader = ROAHeader::create([
                'id' => $roaId,
                'report_no' => $data['roa']['report_no'],
                'shipper' => $data['roa']['shipper'] ?? null,
                'buyer' => $data['roa']['buyer'] ?? null,
                'date_received' => $data['roa']['date_received'] ?? null,
                'date_analyzed_start' => $data['roa']['date_analyzed_start'] ?? null,
                'date_analyzed_end' => $data['roa']['date_analyzed_end'] ?? null,
                'date_reported' => $data['roa']['date_reported'] ?? null,
                'lab_sample_id' => $data['roa']['lab_sample_id'] ?? null,
                'customer_sample_id' => $data['roa']['customer_sample_id'] ?? null,
                'seal_no' => $data['roa']['seal_no'] ?? null,
                'weight_of_received_sample' => $data['roa']['weight_of_received_sample'] ?? null,
                'top_size_of_received_sample' => $data['roa']['top_size_of_received_sample'] ?? null,
                'hardgrove_grindability_index' => $data['roa']['hardgrove_grindability_index'] ?? null,
                'authorized_by' => $user,
                'authorized_date' => now(),
                'updated_by' => $user,
                'updated_date' => now(),
            ]);

            foreach ($data['roa']['details'] as $index => $detail) {
                $detailId = $roaId . 'D' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
                ROADetail::create([
                    'id' => $detailId,
                    'id_hdr' => $roaId,
                    'parameter' => $detail['parameter'],
                    'unit' => $detail['unit'] ?? null,
                    'basis' => $detail['basis'] ?? null,
                    'result' => $detail['result'] ?? null,
                ]);
            }

            DB::table('m_controlnumber')
                ->where('prefix', $roaControl->prefix)
                ->where('plantid', $roaControl->plantid)
                ->update(['autonumber' => $roaNextNum]);

            // === AROIP Fuel creation (controlnumber locked)
            $control = MControlnumber::where('prefix', 'Q12A')
                ->where('plantid', 'PS21')
                ->lockForUpdate()
                ->first();

            if (!$control) {
                throw new \Exception('Control number configuration not found for AROIP');
            }

            $nextNum = intval($control->autonumber ?? 0) + 1;
            $paddedNum2 = str_pad($nextNum, 6, '0', STR_PAD_LEFT);
            $headerId = $control->prefix . $control->plantid . $control->accountingyear . $paddedNum2;

            $header = AROIPFuelHeader::create([
                'id' => $headerId,
                'id_roa' => $roaId,
                'date' => $data['analytical']['date'] ?? null,
                'material' => $data['analytical']['material'] ?? null,
                'quantity' => $data['analytical']['quantity'] ?? null,
                'supplier' => $data['analytical']['supplier'] ?? null,
                'police_no' => $data['analytical']['police_no'] ?? null,
                'analyst' => $data['analytical']['analyst'] ?? null,
                'updated_by' => $user,
                'updated_date' => now(),
                'form_no' => $dataForm->f_code,
                'date_issued' => $dataForm->f_date_issued,
                'revision_no' => $dataForm->f_revision_no,
                'revision_date' => $dataForm->f_revision_date,
                'entry_by' => $user,
                'entry_date' => now(),
            ]);

            foreach ($data['analytical']['details'] as $index => $detail) {
                $detailId = $headerId . 'D' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
                AROIPFuelDetail::create([
                    'id' => $detailId,
                    'id_hdr' => $headerId,
                    'parameter' => $detail['parameter'],
                    'result' => $detail['result'],
                    'specification' => $detail['specification'] ?? null,
                    'status_ok' => isset($detail['status_ok']) ? strtoupper($detail['status_ok']) : null,
                    'remark' => $detail['remark'] ?? null,
                ]);
            }

            DB::table('m_controlnumber')
                ->where('prefix', $control->prefix)
                ->where('plantid', $control->plantid)
                ->update(['autonumber' => $nextNum]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'AROIP Fuel created successfully',
                'data' => [
                    'aroip_header_id' => $header->id,
                    'roa_header_id' => $roaHeader->id,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create AROIP Fuel',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update AROIP Fuel (API) — supports sync semantics for roa and analytical details.
     */
    public function update(UpdateAroipFuelRequest $request, $id)
    {
        try {
            DB::beginTransaction();

            $user = $request->user()->getDisplayNameAttribute();
            $data = $request->validated();

            $header = AROIPFuelHeader::with(['details', 'roa.details'])->findOrFail($id);

            // helper to compute next detail id for a header by scanning existing ids in DB
            $nextDetailIdForHeader = function ($headerId, $detailModelClass) {
                $rows = $detailModelClass::where('id_hdr', $headerId)->pluck('id')->toArray();
                $max = 0;
                foreach ($rows as $rid) {
                    if (preg_match('/D(\d+)$/', $rid, $m)) {
                        $num = intval($m[1]);
                        $max = max($max, $num);
                    }
                }
                $nextSeq = $max + 1;
                return $headerId . 'D' . str_pad($nextSeq, 3, '0', STR_PAD_LEFT);
            };

            // A) ROA update (if provided)
            if (isset($data['roa'])) {
                $roaPayload = $data['roa'];
                $roaHeader = ROAHeader::with('details')->findOrFail($header->id_roa);

                $roaHeader->update([
                    'report_no' => $roaPayload['report_no'] ?? $roaHeader->report_no,
                    'shipper' => $roaPayload['shipper'] ?? $roaHeader->shipper,
                    'buyer' => $roaPayload['buyer'] ?? $roaHeader->buyer,
                    'date_received' => $roaPayload['date_received'] ?? $roaHeader->date_received,
                    'date_analyzed_start' => $roaPayload['date_analyzed_start'] ?? $roaHeader->date_analyzed_start,
                    'date_analyzed_end' => $roaPayload['date_analyzed_end'] ?? $roaHeader->date_analyzed_end,
                    'date_reported' => $roaPayload['date_reported'] ?? $roaHeader->date_reported,
                    'lab_sample_id' => $roaPayload['lab_sample_id'] ?? $roaHeader->lab_sample_id,
                    'customer_sample_id' => $roaPayload['customer_sample_id'] ?? $roaHeader->customer_sample_id,
                    'seal_no' => $roaPayload['seal_no'] ?? $roaHeader->seal_no,
                    'weight_of_received_sample' => $roaPayload['weight_of_received_sample'] ?? $roaHeader->weight_of_received_sample,
                    'top_size_of_received_sample' => $roaPayload['top_size_of_received_sample'] ?? $roaHeader->top_size_of_received_sample,
                    'updated_by' => $user,
                    'updated_date' => now(),
                ]);

                if (isset($roaPayload['details']) && is_array($roaPayload['details'])) {
                    $existingIds = $roaHeader->details->pluck('id')->toArray();
                    $receivedIds = [];

                    foreach ($roaPayload['details'] as $row) {
                        if (!empty($row['id'])) {
                            $detail = ROADetail::where('id', $row['id'])->where('id_hdr', $roaHeader->id)->first();
                            if (!$detail) {
                                DB::rollBack();
                                return response()->json(['success' => false, 'message' => "ROA detail id {$row['id']} not found for ROA {$roaHeader->id}."], 404);
                            }
                            $detail->update([
                                'parameter' => $row['parameter'] ?? $detail->parameter,
                                'unit' => $row['unit'] ?? $detail->unit,
                                'basis' => $row['basis'] ?? $detail->basis,
                                'result' => $row['result'] ?? $detail->result,
                            ]);
                            $receivedIds[] = $detail->id;
                        } else {
                            $newId = $nextDetailIdForHeader($roaHeader->id, ROADetail::class);
                            $created = ROADetail::create([
                                'id' => $newId,
                                'id_hdr' => $roaHeader->id,
                                'parameter' => $row['parameter'] ?? null,
                                'unit' => $row['unit'] ?? null,
                                'basis' => $row['basis'] ?? null,
                                'result' => $row['result'] ?? null,
                            ]);
                            $receivedIds[] = $created->id;
                        }
                    }

                    $toDelete = array_diff($existingIds, $receivedIds);
                    if (!empty($toDelete)) {
                        ROADetail::whereIn('id', $toDelete)->delete();
                    }
                }
            }

            // B) Analytical header + details update (if provided)
            if (isset($data['analytical'])) {
                $analPayload = $data['analytical'];

                $header->update([
                    'date' => $analPayload['date'] ?? $header->date,
                    'material' => $analPayload['material'] ?? $header->material,
                    'quantity' => $analPayload['quantity'] ?? $header->quantity,
                    'supplier' => $analPayload['supplier'] ?? $header->supplier,
                    'police_no' => $analPayload['police_no'] ?? $header->police_no,
                    'analyst' => $analPayload['analyst'] ?? $header->analyst,
                    'updated_by' => $user,
                    'updated_date' => now(),
                    'form_no' => $analPayload['form_no'] ?? $header->form_no,
                    'date_issued' => $analPayload['date_issued'] ?? $header->date_issued,
                    'revision_no' => $analPayload['revision_no'] ?? $header->revision_no,
                    'revision_date' => $analPayload['revision_date'] ?? $header->revision_date,
                ]);

                if (isset($analPayload['details']) && is_array($analPayload['details'])) {
                    $existingIds = $header->details->pluck('id')->toArray();
                    $receivedIds = [];

                    foreach ($analPayload['details'] as $row) {
                        if (!empty($row['id'])) {
                            $detail = AROIPFuelDetail::where('id', $row['id'])->where('id_hdr', $header->id)->first();
                            if (!$detail) {
                                DB::rollBack();
                                return response()->json(['success' => false, 'message' => "Analytical detail id {$row['id']} not found for header {$header->id}."], 404);
                            }
                            $detail->update([
                                'parameter' => $row['parameter'] ?? $detail->parameter,
                                'result' => $row['result'] ?? $detail->result,
                                'specification' => $row['specification'] ?? $detail->specification,
                                'status_ok' => isset($row['status_ok']) ? strtoupper($row['status_ok']) : $detail->status_ok,
                                'remark' => $row['remark'] ?? $detail->remark,
                            ]);
                            $receivedIds[] = $detail->id;
                        } else {
                            $newId = $nextDetailIdForHeader($header->id, AROIPFuelDetail::class);
                            $created = AROIPFuelDetail::create([
                                'id' => $newId,
                                'id_hdr' => $header->id,
                                'parameter' => $row['parameter'] ?? null,
                                'result' => $row['result'] ?? null,
                                'specification' => $row['specification'] ?? null,
                                'status_ok' => isset($row['status_ok']) ? strtoupper($row['status_ok']) : null,
                                'remark' => $row['remark'] ?? null,
                            ]);
                            $receivedIds[] = $created->id;
                        }
                    }

                    $toDelete = array_diff($existingIds, $receivedIds);
                    if (!empty($toDelete)) {
                        AROIPFuelDetail::whereIn('id', $toDelete)->delete();
                    }
                }
            }

            DB::commit();

            $header->refresh();
            $header->load(['details', 'roa.details']);

            return response()->json([
                'success' => true,
                'message' => 'AROIP Fuel record updated successfully',
                'data' => ['aroip_header' => $header],
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Record not found: ' . $e->getMessage()], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to update AROIP Fuel record', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * List / query (API)
     */
    public function get(Request $request)
    {
        $query = AROIPFuelHeader::with(['details', 'roa.details']);

        if ($request->filled('entry_date')) {
            $query->whereDate('entry_date', $request->entry_date);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('entry_date', [$request->start_date, $request->end_date]);
        }

        $query->orderBy('entry_date', 'desc');

        $result = $query->get();

        if ($request->anyFilled(['entry_date', 'start_date', 'end_date']) && $result->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No data found for the given filters.'], 404);
        }

        $formattedResult = $result->map(function ($item) {
            return [
                'analytical' => array_merge(
                    $item->only([
                        'id','id_roa','date','material','quantity','analyst','supplier','police_no',
                        'entry_by','entry_date','prepared_by','prepared_date','prepared_status',
                        'prepared_status_remarks','approved_by','approved_date','approved_status',
                        'approved_status_remarks','updated_by','updated_date','form_no','date_issued',
                        'revision_no','revision_date',
                    ]),
                    ['details' => $item->details]
                ),
                'roa' => $item->roa ?: null,
            ];
        });

        return response()->json(['success' => true, 'data' => $formattedResult], 200);
    }

    /**
     * Soft-delete (API)
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $header = AROIPFuelHeader::withTrashed()->findOrFail($id);

            if ($header->trashed()) {
                return response()->json(['success' => false, 'message' => 'AROIP Fuel record is already deleted.'], 400);
            }

            $header->delete();
            AROIPFuelDetail::where('id_hdr', $id)->delete();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'AROIP Fuel record has been deleted successfully.']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'AROIP Fuel record not found.'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to delete AROIP Fuel record', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update approval (API) — uses processApprovalStatus helper.
     */
    public function updateApproval($id, UpdateAroipApprovalRequest $request)
    {
        try {
            DB::beginTransaction();

            $header = AROIPFuelHeader::findOrFail($id);

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
            return response()->json(['success' => false, 'message' => 'Failed to update approval status', 'error' => $e->getMessage()], 500);
        }
    }

    //
    // -----------------------
    // WEB: admin dashboard endpoints (render views / export PDF)
    // -----------------------
    //

    /**
     * Web index (admin) — list by date.
     */
    public function index(Request $request)
    {
        $tanggal = $request->input('filter_tanggal', now()->toDateString());

        $headers = AROIPFuelHeader::query()
            ->whereDate('entry_date', $tanggal)
            ->orderBy('entry_date', 'desc')
            ->get(['id','material','quantity','prepared_status','approved_status','entry_date']);

        return view('rpt_analytical_result_of_incoming_plant_fuel.index', compact('headers', 'tanggal'));
    }

    /**
     * Web show view.
     */
    public function show($id)
    {
        $data = $this->findHeaderWithId($id);
        return view('rpt_analytical_result_of_incoming_plant_fuel.show', ['header' => $data]);
    }

    /**
     * Web preview layout.
     */
    public function preview($id)
    {
        $data = $this->findHeaderWithId($id);
        return view('rpt_analytical_result_of_incoming_plant_fuel.preview_layout', ['header' => $data]);
    }

    /**
     * Web export to PDF.
     */
    public function export($id)
    {
        $data = $this->findHeaderWithId($id);

        $pdf = Pdf::loadView('exports.report_rpt_analytical_result_of_incoming_plant_fuel_pdf', [
            'header' => $data,
        ]);

        $pdf->setPaper('a4', 'landscape');
        $fileName = 'aroip-fuel-' . $data->id . '.pdf';

        return $pdf->stream($fileName);
    }

    /**
     * Web get by id with intention param (show|preview|export).
     */
    public function getById(Request $request, $id)
    {
        $data = $this->findHeaderWithId($id);
        $intention = $request->query('intention');

        return match ($intention) {
            'show' => view('rpt_analytical_result_of_incoming_plant_fuel.show', ['header' => $data]),
            'preview' => view('rpt_analytical_result_of_incoming_plant_fuel.preview_layout', ['header' => $data]),
            'export' => (function () use ($data) {
                    $pdf = Pdf::loadView('exports.report_rpt_analytical_result_of_incoming_plant_fuel_pdf', ['header' => $data]);
                    $pdf->setPaper('a4', 'landscape');
                    $fileName = 'aroip-fuel-' . $data->id . '.pdf';
                    return $pdf->stream($fileName);
                })(),
            default => abort(400, 'Invalid intention'),
        };
    }

    /**
     * Web approval action (POST) — similar semantics to API approval but returns redirect with flash.
     * Accepts ?status=Approved|Rejected and optional remark in request body.
     */
    public function updateApprovalStatusWeb(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $report = AROIPFuelHeader::findOrFail($id);

            $status = $request->query('status') ?? $request->input('status');
            $remark = $request->input('remark');
            $username = auth()->user()?->username ?? auth()->user()?->getDisplayNameAttribute();
            $role = auth()->user()?->roles ?? null;

            $isSuccess = $this->processApprovalStatus($report, $status, $remark, $username, $role);

            if (!$isSuccess) {
                DB::rollBack();
                return back()->with('error', 'You do not have permission to update approval status');
            }

            DB::commit();

            if ($status === 'Approved') {
                return back()->with('success-approve', "Tiket {$report->id} berhasil di-{$status}");
            } elseif ($status === 'Rejected') {
                return back()->with('success-reject', "Tiket {$report->id} berhasil di-{$status}");
            }

            return back()->with('success', 'Approval status updated');
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', $th->getMessage());
        }
    }
}
