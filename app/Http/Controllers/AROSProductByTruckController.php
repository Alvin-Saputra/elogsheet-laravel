<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateArosProductByTruckRequest;
use App\Http\Requests\UpdateArosProductByTruckApprovalRequest;
use App\Http\Requests\UpdateArosProductByTruckRequest;
use App\Models\AROSProductByTruckDetail;
use App\Models\AROSProductByTruckHeader;
use App\Models\MControlnumber;
use App\Models\MDataFormNo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Schema;

class AROSProductByTruckController extends Controller
{
    //
    // -----------------------
    // Shared helpers / utils
    // -----------------------
    //

    private function findHeaderWithId($id)
    {
        return AROSProductByTruckHeader::with(['details'])->findOrFail($id);
    }

    /**
     * Normalize roles value to an array and decide which prefix to use.
     * Returns ['prefix' => 'prepared'|'approved', 'roles' => array] or false when not allowed.
     *
     * NOTE: This mirrors AROIPFuelController decision logic (prepared = LEAD, approved = MGR/ADM)
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
            return ['prefix' => 'corrected', 'roles' => $roles];
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
    // API: RESTful endpoints (kept your original behaviors)
    // -----------------------
    //

    public function get(Request $request)
    {
        $query = AROSProductByTruckHeader::with(['details']);

        if ($request->filled('loading_date')) {
            $query->whereDate('loading_date', $request->loading_date);
        }
        $query->orderBy('loading_date', 'desc');

        $result = $query->get();
        if ($request->anyFilled(['loading_date']) && $result->isEmpty()) {
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

    public function create(CreateArosProductByTruckRequest $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->validated();
            $user = $request->user()->getDisplayNameAttribute();

            $dataForm = MDataFormNo::find(19);
            if (!$dataForm) {
                return response()->json([
                    'sucess' => false,
                    'message' => 'Form configuration not found (f_id: 19)'
                ], 400);
            }

            // Get control number for AROSProductByTruck (using prefix 'Q13' and plantid 'PS21')
            $control = MControlnumber::where('prefix', 'Q13')
                ->where('plantid', 'PS21')
                ->lockForUpdate()
                ->first();

            if (!$control) {
                return response()->json([
                    'success' => false,
                    'message' => 'Control number configuration not found',
                ], 400);
            }

            // Generate new AROSProductByTruck document number
            $nextNum = intval($control->autonumber) + 1;
            $paddedNum = str_pad($nextNum, 6, '0', STR_PAD_LEFT);
            $headerId = $control->prefix . $control->plantid . $control->accountingyear . $paddedNum;

            $header = AROSProductByTruckHeader::create([
                ...$data,
                'id' => $headerId,
                'entry_by' => $user,
                'entry_date' => now(),
                'updated_by' => $user,
                'updated_date' => now(),
                'form_no' => $dataForm->f_code,
                'date_issued' => $dataForm->f_date_issued,
                'revision_no' => $dataForm->f_revision_no,
                'revision_date' => $dataForm->f_revision_date,
            ]);
            foreach ($data['details'] as $index => $row) {
                AROSProductByTruckDetail::create([
                    ...$row,
                    'id' => $header->id . 'D' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'id_hdr' => $header->id,
                ]);
            }

            DB::table('m_controlnumber')
                ->where('prefix', $control->prefix)
                ->where('plantid', $control->plantid)
                ->update(['autonumber' => $nextNum]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Analytical Result of Outgoing Shipment Product By Truck created successfully',
                'data' => [
                    'header_id' => $header->id,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create AROS Plant',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateArosProductByTruckRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $data = $request->validated();
            $user = $request->user()->getDisplayNameAttribute();

            $header = AROSProductByTruckHeader::with('details')->find($id);
            if (!$header) {
                return response()->json([
                    'success' => false,
                    'message' => 'Header not found',
                ], 404);
            }

            $header->update([
                ...$data,
                'updated_by' => $user,
                'updated_date' => now(),
                'revision_no' => DB::raw('COALESCE(revision_no, 0) + 1'),
                'revision_date' => now(),
            ]);

            if (!empty($data['details'])) {
                foreach ($data['details'] as $row) {
                    if (!empty($row['id'])) {
                        $detail = AROSProductByTruckDetail::where('id', $row['id'])
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
                'message' => 'AROS Product By Truck updated successfully',
                'data' => $header->fresh('details'),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update AROS Product By Truck',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $header = AROSProductByTruckHeader::withTrashed()->findOrFail($id);

            if ($header->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'AROS Product By Truck record is already deleted.',
                ], 400);
            }

            $header->delete();
            AROSProductByTruckDetail::where('id_hdr', $id)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'AROS Product By Truck record has been deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'AROS Product By Truck record not found.',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete AROS Product By Truck record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    //
    // -----------------------
    // Approval endpoints (API + Web)
    // -----------------------
    //

    /**
     * API: Update approval (uses UpdateArosProductByTruckApprovalRequest)
     */
    public function updateApproval($id, UpdateArosProductByTruckApprovalRequest $request)
    {
        try {
            DB::beginTransaction();

            $header = AROSProductByTruckHeader::findOrFail($id);

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
                'message' => 'Failed to update AROS Product By Truck Approval',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Web approval action (POST) — similar semantics to API approval but returns redirect with flash.
     * Accepts ?status=Approved|Rejected and optional remark in request body.
     */
    public function updateApprovalStatusWeb(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $report = AROSProductByTruckHeader::findOrFail($id);

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
        $filterDate = $request->input('filter_tanggal', now()->toDateString());

        $headers = AROSProductByTruckHeader::query()
            ->whereDate('loading_date', $filterDate)
            ->orderBy('loading_date', 'desc')
            ->get([
                'id',
                'product_name',
                'quantity',
                'ships_name',
                'destination',
                'load_port',
                'corrected_status',
                'approved_status',
                'loading_date'
            ]);

        return view('rpt_analytical_result_of_out_going_shipment_product_by_truck.index', compact('headers'));
    }

    /**
     * Web show view.
     */
    public function show($id)
    {
        $data = $this->findHeaderWithId($id);
        return view('rpt_analytical_result_of_out_going_shipment_product_by_truck.show', ['header' => $data]);
    }

    /**
     * Web preview layout.
     */
    public function preview($id)
    {
        $data = $this->findHeaderWithId($id);
        return view('rpt_analytical_result_of_out_going_shipment_product_by_truck.preview_layout', ['header' => $data]);
    }

    /**
     * Web export to PDF.
     */
    public function export($id)
    {
        $data = $this->findHeaderWithId($id);

        $pdf = Pdf::loadView('exports.report_rpt_analytical_result_of_out_going_shipment_product_by_truck_pdf', [
            'header' => $data,
        ]);

        $pdf->setPaper('a4', 'landscape');
        $fileName = 'aros-product-by-truck-' . $data->id . '.pdf';

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
            'show' => view('rpt_analytical_result_of_out_going_shipment_product_by_truck.show', ['header' => $data]),
            'preview' => view('rpt_analytical_result_of_out_going_shipment_product_by_truck.preview_layout', ['header' => $data]),
            'export' => (function () use ($data) {
                    $pdf = Pdf::loadView('exports.report_rpt_analytical_result_of_out_going_shipment_product_by_truck_pdf', ['header' => $data]);
                    $pdf->setPaper('a4', 'landscape');
                    $fileName = 'aros-product-by-truck-' . $data->id . '.pdf';
                    return $pdf->stream($fileName);
                })(),
            default => abort(400, 'Invalid intention'),
        };
    }
}
