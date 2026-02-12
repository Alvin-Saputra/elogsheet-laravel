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
            $dataForm = MDataFormNo::find(21);
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

            if (!empty($data['details']) && is_array($data['details'])) {
                foreach ($data['details'] as $index => $row) {
                    AROSByVesselDetail::create([
                        ...$row,
                        'id'     => $header->id . 'D' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                        'id_hdr' => $header->id,
                    ]);
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
    // public function update(UpdateArosByVesselRequest $request, $id)
    // {
    //     try {
    //         DB::beginTransaction();
    //         $data = $request->validated();
    //         $user = $request->user()->getDisplayNameAttribute();

    //         $header = AROSByVesselHeader::with('details')->find($id);
    //         if (!$header) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Header not found',
    //             ], 404);
    //         }

    //         $header->update(array_merge(Arr::except($data, ['details', 'detail', 'menu_id', 'hasil_analisa_komposit_palka']), [
    //             'updated_by' => $user,
    //             'updated_date' => now(),
    //             'revision_no' => DB::raw('COALESCE(revision_no, 0) + 1'),
    //             'revision_date' => now(),
    //         ]));

    //         if (!empty($data['details'])) {
    //             foreach ($data['details'] as $row) {
    //                 if (!empty($row['id'])) {
    //                     $detail = AROSByVesselDetail::where('id', $row['id'])
    //                         ->where('id_hdr', $header->id)
    //                         ->first();

    //                     if ($detail) {
    //                         $detail->update(
    //                             collect($row)->except('id')->toArray()
    //                         );
    //                     }
    //                 }
    //             }
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'AROS By Vessel updated successfully',
    //             'data' => $header->fresh('details'),
    //         ], 200);
    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to update AROS By Vessel',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }



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

            if (isset($data['details']) && is_array($data['details'])) {

                foreach ($data['details'] as $index => $row) {

                    // Cek Flag
                    $flag = $row['flag'] ?? ''; // Default kosong jika tidak ada flag

                    // --- CASE 1: DELETE ---
                    if ($flag === 'D') {
                        if (!empty($row['id'])) {
                            // Hanya hapus jika ID valid dan ada di DB
                            AROSByVesselDetail::where('id', $row['id'])
                                ->where('id_hdr', $header->id) // Pastikan milik header yang benar
                                ->delete();
                        }
                        continue; // Lanjut ke iterasi berikutnya, jangan jalankan logika update/create
                    }

                    // --- CASE 2: UPDATE ---
                    // Jika ID ada dan Flag BUKAN 'D' (bisa 'U' atau kosong)
                    if (!empty($row['id'])) {
                        $detail = AROSByVesselDetail::where('id', $row['id'])
                            ->where('id_hdr', $header->id)
                            ->first();

                        if ($detail) {
                            $updateData = Arr::except($row, ['id', 'id_hdr', 'flag']); // Buang flag agar tidak error di SQL
                            $detail->update($updateData);
                        }
                    }
                    // --- CASE 3: CREATE / INSERT ---
                    // Jika ID kosong (biasanya row baru)
                    else {
                        // Generate ID Manual (HeaderID + 'D' + Index)
                        $suffix = str_pad($index + 1, 3, '0', STR_PAD_LEFT);
                        $newId = $header->id . 'D' . $suffix;

                        // PERBAIKAN DISINI: Tambahkan withTrashed()
                        // Agar pengecekan mencakup ID yang aktif MAUPUN yang sudah dihapus (soft delete)
                        while (AROSByVesselDetail::withTrashed()->where('id', $newId)->exists()) {
                            $suffix = str_pad((int)$suffix + 1, 3, '0', STR_PAD_LEFT);
                            $newId = $header->id . 'D' . $suffix;
                        }

                        // Buang flag dari payload create
                        $createData = Arr::except($row, ['flag']);

                        AROSByVesselDetail::create(array_merge($createData, [
                            'id' => $newId,
                            'id_hdr' => $header->id,
                        ]));
                    }
                }
            }

            DB::commit();


            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diperbarui',
                // Gunakan fresh('details') agar frontend mendapat data terbaru 
                // (Data yang di-soft delete otomatis hilang dari list ini)
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

    // -----------------------
// WEB: admin dashboard endpoints
// -----------------------

    /**
     * Web index — list by sampling date
     */
    public function index(Request $request)
    {
        $filterDate = $request->input('filter_tanggal', now()->toDateString());

        $headers = AROSByVesselHeader::query()
            ->whereDate('sampling_date', $filterDate)
            ->orderBy('sampling_date', 'desc')
            ->get([
                'id',
                'product_name',
                'quantity',
                'vessel_name',
                'destination',
                'shipper',
                'prepared_status',
                'approved_status',
                'sampling_date',
            ]);

        return view(
            'rpt_analytical_result_of_outgoing_shipment_product_by_vessel.index',
            compact('headers', 'filterDate')
        );
    }

    /**
     * Web show (detail)
     */
    public function show($id)
    {
        $header = $this->findHeaderWithId($id);

        return view(
            'rpt_analytical_result_of_outgoing_shipment_product_by_vessel.show',
            compact('header')
        );
    }

    /**
     * Web preview (layout preview)
     */
    public function preview($id)
    {
        $header = $this->findHeaderWithId($id);

        return view(
            'rpt_analytical_result_of_outgoing_shipment_product_by_vessel.preview_layout',
            compact('header')
        );
    }

    /**
     * Web export PDF
     */
    public function export($id)
    {
        $header = $this->findHeaderWithId($id);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'exports.report_rpt_analytical_result_of_outgoing_shipment_product_by_vessel_pdf',
            compact('header')
        );

        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream(
            'aros-by-vessel-' . $header->id . '.pdf'
        );
    }

    /**
     * Unified getById for web (show | preview | export)
     */
    public function getById(Request $request, $id)
    {
        $header = $this->findHeaderWithId($id);
        $intention = $request->query('intention');

        return match ($intention) {
            'show' => view(
                'rpt_analytical_result_of_outgoing_shipment_product_by_vessel.show',
                compact('header')
            ),

            'preview' => view(
                'rpt_analytical_result_of_outgoing_shipment_product_by_vessel.preview_layout',
                compact('header')
            ),

            'export' => (function () use ($header) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
                    'exports.report_rpt_analytical_result_of_outgoing_shipment_product_by_vessel_pdf',
                    compact('header')
                );

                $pdf->setPaper('f4', 'landscape');

                return $pdf->stream(
                    'aros-by-vessel-' . $header->id . '.pdf'
                );
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
            $report = AROSByVesselHeader::findOrFail($id);

            // status can come from ?status=Approved or form input 'status'
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



    public function bulkApprove(Request $request)
    {
        try {
            DB::beginTransaction();

            $status   = 'Approved';
            $remark   = null; // Bulk approve biasanya tanpa remark, atau bisa diambil dari request jika ada
            $user     = auth()->user();
            $username = $user->username ?? $user->getDisplayNameAttribute();
            $roles    = $user->roles;
            $tanggal  = $request->input('tanggal') ?? now()->format('Y-m-d');
            $count    = 0;

            // 1. Tentukan user ini bertindak sebagai 'prepared' atau 'approved'
            $decision = $this->decidePrefixFromRoles($roles);

            if (!$decision) {
                return back()->with('error', 'Anda tidak memiliki hak akses untuk melakukan approval.');
            }

            $prefix = $decision['prefix']; // 'prepared' atau 'approved'

            // 2. Build Query berdasarkan Role
            $query = AROSByVesselHeader::whereDate('sampling_date', $tanggal);

            if ($prefix === 'prepared') {
                // LEAD QC: Cari yang belum di-prepared
                $query->whereNull('prepared_status');
            } elseif ($prefix === 'approved') {
                // MGR/ADM: Cari yang SUDAH di-prepared 'Approved', tapi BELUM di-approved
                $query->where('prepared_status', 'Approved')
                    ->whereNull('approved_status');
            }

            $reports = $query->get();

            if ($reports->isEmpty()) {
                return back()->with('error', "Tidak ada data yang perlu diapprove untuk tanggal $tanggal.");
            }

            // 3. Loop update
            foreach ($reports as $report) {
                // Gunakan helper processApprovalStatus agar logic update konsisten (termasuk update history log user)
                $success = $this->processApprovalStatus($report, $status, $remark, $username, $roles);
                if ($success) {
                    $count++;
                }
            }

            DB::commit();

            return back()->with('success', "Total {$count} tiket berhasil di-approve.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat bulk approve: ' . $e->getMessage());
        }
    }

    public function bulkReject(Request $request)
    {
        $request->validate([
            'remark' => 'required|string|max:255' // Remark wajib diisi jika reject
        ]);

        try {
            DB::beginTransaction();

            $status   = 'Rejected';
            $remark   = $request->remark;
            $user     = auth()->user();
            $username = $user->username ?? $user->getDisplayNameAttribute();
            $roles    = $user->roles;
            $tanggal  = $request->input('tanggal') ?? now()->format('Y-m-d');
            $count    = 0;

            // 1. Tentukan user ini bertindak sebagai 'prepared' atau 'approved'
            $decision = $this->decidePrefixFromRoles($roles);

            if (!$decision) {
                return back()->with('error', 'Anda tidak memiliki hak akses untuk melakukan reject.');
            }

            $prefix = $decision['prefix'];

            // 2. Build Query (Logic sama dengan Approve, bedanya nanti status yang di-update)
            // PERBAIKAN: Menggunakan AROSByVesselHeader dan sampling_date
            $query = AROSByVesselHeader::whereDate('sampling_date', $tanggal);

            if ($prefix === 'prepared') {
                // LEAD QC: Reject yang masih kosong statusnya
                $query->whereNull('prepared_status');
            } elseif ($prefix === 'approved') {
                // MGR: Reject yang sudah diprepare (biasanya approved), tapi belum diapprove manager
                $query->where('prepared_status', 'Approved') // Asumsi yang direject manager adalah yang lolos dari lead
                    ->whereNull('approved_status');
            }

            $reports = $query->get();

            if ($reports->isEmpty()) {
                return back()->with('error', "Tidak ada data yang bisa di-reject untuk tanggal $tanggal.");
            }

            // 3. Loop update
            foreach ($reports as $report) {
                $success = $this->processApprovalStatus($report, $status, $remark, $username, $roles);
                if ($success) {
                    $count++;
                }
            }

            DB::commit();

            return back()->with('success', "Total {$count} tiket berhasil di-reject.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat bulk reject: ' . $e->getMessage());
        }
    }
}
