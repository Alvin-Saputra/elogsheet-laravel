<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\MDataFormNo;
use Illuminate\Http\Request;
use App\Models\MControlnumber;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\DryFractionationDetail;
use App\Models\DryFractionationHeader;
use App\Http\Requests\CreateDryFraRequest;
use App\Http\Requests\UpdateDryFraRequest;

class DryFraController extends Controller
{

    private function processApprovalStatus($header, $status, $remark, $user_name, $user_roles)
    {
        $LEAD_QC = ['LEAD', 'LEAD_PROD'];
        $QC_Control_MGR = ['MGR', 'MGR_PROD', 'ADM'];

        $fieldPrefix = '';

        if (in_array($user_roles, $QC_Control_MGR, true)) { // Gunakan intersect utk keamanan array
            $fieldPrefix = 'checked';
        } elseif (in_array($user_roles, $LEAD_QC, true)) {
            $fieldPrefix = 'prepared';
        } else {
            // Jika role tidak cocok, return false atau throw error
            return false;
        }

        $header->update([
            "{$fieldPrefix}_status" => $status,
            "{$fieldPrefix}_by" => $user_name,
            // Simpan sebagai string/json jika perlu
            "{$fieldPrefix}_date" => now(),
            "{$fieldPrefix}_status_remarks" => $remark,
        ]);

        return true;
    }

    private function calculateDailyStatus($items)
    {
        $user = Auth::user();
        $role = $user->roles;

        $canApproveReject = false;
        $statusMessage = null;

        // LOGIKA UNTUK MANAGER
        if (in_array($role, ['MGR', 'MGR_PROD'])) {
            // 1. Cek apakah ada yang sudah diproses Manager (Approved/Rejected)
            $alreadyChecked = $items->contains(fn($i) => !is_null($i->checked_status));

            // 2. Cek apakah ada yang BELUM diapprove Leader (Pending)
            $leaderPending = $items->contains(fn($i) => is_null($i->prepared_status));

            // 3. Cek apakah ada yang DIREJECT Leader
            $leaderRejected = $items->contains(fn($i) => $i->prepared_status === 'Rejected');

            // 4. Cek apakah SEMUA sudah Approved Leader
            $allLeaderApproved = $items->every(fn($i) => $i->prepared_status === 'Approved');

            if ($alreadyChecked) {
                $statusMessage = 'Sebagian data sudah diproses Manager.';
            } elseif ($leaderPending) {
                $statusMessage = 'Menunggu Leader memproses semua data.';
            } elseif ($leaderRejected) {
                $statusMessage = 'Terdapat data yang di-reject oleh Leader.';
            } elseif ($allLeaderApproved) {
                // HORE! Bisa Approve All
                $canApproveReject = true;
                $statusMessage = 'Semua data valid untuk diproses.';
            }
        }

        // LOGIKA UNTUK LEADER (Opsional: Jika Leader mau bulk approve yang masih null)
        elseif (in_array($role, ['LEAD', 'LEAD_PROD'])) {
            // Bisa approve all jika belum ada satupun yang diproses
            if ($items->every(fn($i) => is_null($i->prepared_status))) {
                $canApproveReject = true;
            } else {
                $statusMessage = 'Sebagian data sudah diproses.';
            }
        }

        return [
            'canApproveReject' => $canApproveReject,
            'statusMessage'    => $statusMessage
        ];
    }

    public function create(CreateDryFraRequest $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->validated();
            $user = $request->user()->getDisplayNameAttribute();

            // ------------------------------------------------------------------
            // 1. CEK DUPLIKASI DATA
            // ------------------------------------------------------------------
            // Kita cek apakah ada data aktif (flag != 'D') dengan kombinasi yang sama.
            $isDuplicate = DryFractionationHeader::query()
                ->whereDate('date', $data['date']) // <--- GANTI JADI whereDate
                ->where('plant', $data['plant'])
                ->where('crystallizer', $data['crystallizer'])
                ->where('flag', '!=', 'D')
                ->exists();

            if ($isDuplicate) {
                // Batalkan proses jika data sudah ada
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Data Logsheet untuk Tanggal, Plant, dan Crystallizer tersebut sudah ada.'
                ], 422); // 422 Unprocessable Entity atau 409 Conflict
            }
            // ------------------------------------------------------------------

            $dataForm = MDataFormNo::find(19);
            if (!$dataForm) {
                return response()->json([
                    'success' => false,
                    'message' => 'Form configuration not found (f_id: 19)'
                ], 400);
            }

            // Get control number for AROSProductByTruck (using prefix 'Q13' and plantid 'PS21')
            $control = MControlnumber::where('prefix', 'DFM')
                ->where('plantid', $data['plant'])
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

            $header = DryFractionationHeader::create([
                ...$data,
                'id' => $headerId,
                'flag' => 'T',
                'entry_by' => $user,
                'entry_date' => now(),
                'updated_by' => $user,
                'updated_date' => now(),
                'form_no' => $dataForm->f_code,
                'date_issued' => $dataForm->f_date_issued,
                'revision_no' => $dataForm->f_revision_no,
                'revision_date' => $dataForm->f_revision_date,
                // Pastikan flag di-set ke 'T' (atau null, tergantung default DB) saat create
                // agar terhitung sebagai data aktif.

            ]);

            $prefix = $control->prefix; // "DFM"
            $plant  = $control->plantid; // "PS21"
            $year   = $control->accountingyear; // "25"
            $seq    = $paddedNum; // "000023"

            foreach ($data['details'] as $index => $row) {
                // Format Target: DFMD + PS21 + 25 + 000023 + 1
                // Hasil: DFMDPS21250000231
                $detailId = $prefix . 'D' . $plant . $year . $seq . ($index + 1);

                DryFractionationDetail::create([
                    ...$row,
                    'id'     => $detailId,
                    'id_hdr' => $header->id,
                    'filtration_date' => now()
                ]);
            }
            // update control number
            DB::table('m_controlnumber')
                ->where('prefix', $control->prefix)
                ->where('plantid', $control->plantid)
                ->update(['autonumber' => $nextNum]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Logsheet Dry Fractionation created successfully',
                'data' => [
                    'header_id' => $header->id,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create Ticket',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // public function get(Request $request)
    // {
    //     // 1. Inisialisasi query awal
    //     $query = DryFractionationHeader::with(['details'])->where('flag', 'T');

    //     // 2. Filter berdasarkan Date (jika ada)
    //     if ($request->filled('date')) {
    //         $query->whereDate('date', $request->date);
    //     }

    //     // 3. Filter berdasarkan Plant ID (jika ada) - TAMBAHAN BARU
    //     if ($request->filled('plant')) {
    //         // Asumsi nama kolom di database adalah 'plant'
    //         $query->where('plant', $request->plant);
    //     }

    //     // 4. Urutkan data
    //     $query->orderBy('date', 'desc');

    //     // 5. Eksekusi Query
    //     $result = $query->get();

    //     // 6. Cek jika user melakukan filter (date ATAU plant) tapi hasilnya kosong
    //     if ($request->anyFilled(['date', 'plant']) && $result->isEmpty()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'No data found for the given filters.',
    //         ], 404);
    //     }

    //     // 7. Return sukses
    //     return response()->json([
    //         'success' => true,
    //         'data' => $result,
    //     ], 200);
    // }


    public function get(Request $request)
    {
        // 1. Inisialisasi query awal
        $query = DryFractionationHeader::with(['details'])->where('flag', 'T');

        // 2. Filter Range Tanggal
        // Kita cek apakah ada start_date atau end_date
        if ($request->filled('start_date') || $request->filled('end_date')) {

            // Jika ada start_date, ambil data DARI tanggal tersebut ke depan
            if ($request->filled('start_date')) {
                $query->whereDate('date', '>=', $request->start_date);
            }

            // Jika ada end_date, ambil data SAMPAI tanggal tersebut
            if ($request->filled('end_date')) {
                $query->whereDate('date', '<=', $request->end_date);
            }
        }
        // Fallback: Jika tidak ada range, cek filter single date (untuk backward compatibility)
        elseif ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        // 3. Filter berdasarkan Plant ID
        if ($request->filled('plant')) {
            $query->where('plant', $request->plant);
        }

        // 4. Urutkan data
        $query->orderBy('date', 'desc');

        // 5. Eksekusi Query
        $result = $query->get();

        // 6. Cek hasil kosong
        // PENTING: Tambahkan 'start_date' dan 'end_date' ke dalam pengecekan anyFilled
        if ($request->anyFilled(['date', 'plant', 'start_date', 'end_date']) && $result->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No data found for the given filters.',
            ], 404);
        }

        // 7. Return sukses
        return response()->json([
            'success' => true,
            'data' => $result,
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        try {
            $header = DryFractionationHeader::find($id);
            if (! $header) {
                return response()->json(['success' => false, 'error' => 'NOT_FOUND'], 404);
            }

            // Soft-delete by marking flag = 'F' and updating audit fields

            $header->flag = 'D';
            $header->updated_by = $request->user() ? $request->user()->getDisplayNameAttribute() : $header->updated_by;
            $header->updated_date = now();
            $header->save();

            return response()->json(['success' => true], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'data' => $th->getMessage(),
            ], 500);
        }
    }


    public function update(UpdateDryFraRequest $request, $id)
    {
        try {
            DB::beginTransaction();

            // 1. Cari Header
            $header = DryFractionationHeader::find($id);

            if (!$header) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data not found'
                ], 404);
            }

            $data = $request->validated();
            $user = $request->user()->getDisplayNameAttribute();

            // 2. Update Header
            // Kita filter field mana saja yang BOLEH diupdate (Rule 1: Kecuali ID & Info Form)
            // Field audit (form_no, dll) tidak kita masukkan ke fill.
            $header->update([
                'date' => $data['date'],
                'posting_date' => $data['posting_date'],
                'company' => $data['company'],
                'plant' => $data['plant'],
                'crystallizer' => $data['crystallizer'],
                'feed_oil_iv' => $data['feed_oil_iv'],
                'initial_oil_level' => $data['initial_oil_level'],
                'filling_start_time' => $data['filling_start_time'],
                'filling_end_time' => $data['filling_end_time'],
                'cooling_start_temp' => $data['cooling_start_temp'],
                'cooling_start_time' => $data['cooling_start_time'],
                'agitator_speed' => $data['agitator_speed'],
                'water_pump_pres' => $data['water_pump_pres'],
                'remarks' => $data['remarks'],
                'is_completed' => $data['is_completed'] ?? $header->is_completed,

                // Update Audit Trail
                'updated_by' => $user,
                'updated_date' => now(),
            ]);

            // 3. Proses Details
            if (isset($data['details']) && is_array($data['details'])) {

                // Ambil daftar ID detail yang ada di database saat ini untuk header ini
                $existingDetails = DryFractionationDetail::where('id_hdr', $header->id)->get();

                // Cari angka suffix terakhir untuk generate ID baru (jika ada insert)
                // ID Format: DFMDPS21250000231 (Kita butuh angka '1' di ujung)
                $maxSuffix = 0;
                foreach ($existingDetails as $exDetail) {
                    // Ambil angka terakhir dari string ID
                    if (preg_match('/(\d+)$/', $exDetail->id, $matches)) {
                        $suffix = intval($matches[1]);
                        if ($suffix > $maxSuffix) {
                            $maxSuffix = $suffix;
                        }
                    }
                }

                foreach ($data['details'] as $row) {
                    // Cek apakah item ini punya ID (Berarti Update)
                    if (isset($row['id']) && !empty($row['id'])) {
                        // --- LOGIC UPDATE EXISTING DETAIL ---
                        $detailModel = DryFractionationDetail::where('id', $row['id'])
                            ->where('id_hdr', $header->id) // Safety check
                            ->first();

                        if ($detailModel) {
                            $detailModel->update([
                                'filtration_cycle_number' => $row['filtration_cycle_number'],
                                // 'filtration_date' => $row['filtration_date'],
                                'filtration_temp' => $row['filtration_temp'],
                                'time_start_filtration' => $row['time_start_filtration'],
                                'time_end_filtration' => $row['time_end_filtration'],
                                'load' => $row['load'],
                                'olein_iv' => $row['olein_iv'],
                                'olein_cp' => $row['olein_cp'],
                                'olein_ffa' => $row['olein_ffa'],
                                'olein_color_red' => $row['olein_color_red'],
                                'stearin_iv' => $row['stearin_iv'],
                                'stearin_ffa' => $row['stearin_ffa'],
                                'stearin_color_red' => $row['stearin_color_red'],
                                'stearin_pv' => $row['stearin_pv'],
                            ]);
                        }
                    } else {
                        // --- LOGIC INSERT NEW DETAIL (Rule 2) ---
                        $maxSuffix++; // Increment suffix

                        // Kita harus merekonstruksi Base ID untuk detail
                        // Header ID: DFMPS2125000023
                        // Detail ID Target: DFMDPS2125000023 + Suffix

                        // Cara paling aman: Ambil prefix 3 huruf dari header (DFM), sisipkan 'D', lalu sisa header
                        $prefix = substr($header->id, 0, 3);
                        $restOfHeader = substr($header->id, 3);

                        $newDetailId = $prefix . 'D' . $restOfHeader . $maxSuffix;

                        DryFractionationDetail::create([
                            ...$row, // Spread semua field dari request
                            'id' => $newDetailId,
                            'id_hdr' => $header->id,
                            'filtration_date' => $row['filtration_date'] ?? now(),
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Logsheet updated successfully',
                'data' => [
                    'header_id' => $header->id
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Logsheet',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateApprovalStatusApi(Request $request)
    {
        try {
            $data = $request->validate([
                // 'id' => 'required|string',
                'approve_status' => 'required|in:Approved,Rejected',
                'remark' => 'nullable|string|max:255',
                'plant' => 'required|string',
                'date' => 'required|date',
                'crystallizer' => 'required|string'
            ]);

            $header = DryFractionationHeader::query()
                ->whereDate('date', $data['date'])
                ->where('plant', $data['plant'])
                ->where('crystallizer', $data['crystallizer']);

            $role = auth()->user()->roles;
            $username = auth()->user()->username;
            $status = $data['approve_status'];
            $remark = $data['remark'];

            if (! $header) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'error' => 'DATA_NOT_FOUND',
                ], 404);
            }

            $isSuccess = $this->processApprovalStatus($header, $status, $remark, $username, $role);

            if ($isSuccess) {
                return response()->json([
                    'success' => true,
                    'message' => 'Approval updated successfully',
                ], 200);
            }
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'data' => $th->getMessage(),
            ], 500);
        }
    }

    public function updateApprovalStatusPerDateApi(Request $request)
    {
        try {
            $data = $request->validate([
                // 'id' => 'required|string',
                'approve_status' => 'required|in:Approved,Rejected',
                'remark' => 'nullable|string|max:255',
                'plant' => 'required|string',
                'date' => 'required|date',
                // 'crystallizer' => 'required|string'
            ]);

            $header = DryFractionationHeader::query()
                ->whereDate('date', $data['date'])
                ->where('plant', $data['plant']);

            $role = auth()->user()->roles;
            $username = auth()->user()->username;
            $status = $data['approve_status'];
            $remark = $data['remark'];

            if (! $header) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'error' => 'DATA_NOT_FOUND',
                ], 404);
            }

            $isSuccess = $this->processApprovalStatus($header, $status, $remark, $username, $role);

            if ($isSuccess) {
                return response()->json([
                    'success' => true,
                    'message' => 'Approval PerDate updated successfully',
                ], 200);
            }
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'data' => $th->getMessage(),
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->format('Y-m-d'));
        $endDate   = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $query = DryFractionationHeader::with(['details'])
            ->where('flag', 'T')
            ->orderBy('date', 'desc');

        if ($startDate) {
            $query->whereDate('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('date', '<=', $endDate);
        }

        $rawData = $query->get();
        $groupedData = $rawData->groupBy('date');

        // --- TAMBAHAN BARU: Hitung Status Approval per Tanggal ---
        // Kita simpan statusnya dalam array dengan key = tanggal
        $approvalStatusPerDate = [];

        foreach ($groupedData as $dateKey => $items) {
            $approvalStatusPerDate[$dateKey] = $this->calculateDailyStatus($items);
        }

        return view('rpt_logsheetDryFractionation.index', compact(
            'groupedData',
            'startDate',
            'endDate',
            'approvalStatusPerDate' // Kirim variable ini ke View
        ));
    }


    public function approvePerCrystallizer(Request $request)
    {
        $request->validate([
            'id'             => 'required',
            'approve_status' => 'required|in:Approved,Rejected',
            'remark'         => 'nullable|string',
        ]);

        $user = Auth::user();
        $role = $user->roles; // Pastikan ini string, jika array gunakan logic in_array yang sesuai

        // 1. Ambil Data Header dulu menggunakan first() atau find()
        // Agar kita bisa cek datanya ada atau tidak, dan mengambil info crystallizer
        $header = DryFractionationHeader::find($request->id);

        if (!$header) {
            return back()->with('error', 'Data Logsheet tidak ditemukan.');
        }

        // --- LOGIC LEADER ---
        if (in_array($role, ['LEAD', 'LEAD_PROD'])) {
            $header->update([
                'prepared_status'         => $request->approve_status,
                'prepared_status_remarks' => $request->remark,
                'prepared_date'           => now(),
                'prepared_by'             => $user->username ?? $user->name,
                'checked_status'          => null, // Reset status manager jika leader update ulang
            ]);

            $action = $request->approve_status == 'Approved' ? 'di-approve' : 'di-reject';
            // Gunakan $header->crystallizer karena $request->shift tidak dikirim dari form
            return back()->with('success', "Logsheet {$header->crystallizer} berhasil {$action} oleh Leader.");
        }

        // --- LOGIC MANAGER ---
        if (in_array($role, ['MGR', 'MGR_PROD'])) {

            // Cek apakah leader sudah approve
            if ($header->prepared_status != 'Approved') {
                return back()->with('error', 'Gagal: Logsheet ini belum di-approve oleh Leader.');
            }

            $header->update([
                'checked_status'         => $request->approve_status,
                'checked_status_remarks' => $request->remark,
                'checked_date'           => now(),
                'checked_by'             => $user->username ?? $user->name,
            ]);

            $action = $request->approve_status == 'Approved' ? 'di-approve' : 'di-reject';
            return back()->with('success', "Logsheet {$header->crystallizer} berhasil {$action} oleh Manager.");
        }

        return back()->with('error', 'Unauthorized Action.');
    }


    public function approvePerDate(Request $request)
    {
        $request->validate([
            'transaction_date' => 'required|date',
            'approve_status'   => 'required|in:Approved,Rejected',
            'remark'           => 'nullable|string',
        ]);

        $user = Auth::user();
        $role = $user->roles;
        $date = $request->transaction_date;

        // Ambil semua data pada tanggal tersebut
        $headers = DryFractionationHeader::whereDate('date', $date)
            ->where('flag', 'T')
            ->get();

        if ($headers->isEmpty()) {
            return back()->with('error', 'Tidak ada data pada tanggal tersebut.');
        }

        DB::beginTransaction();
        try {
            // --- LOGIC MANAGER ---
            if (in_array($role, ['MGR', 'MGR_PROD'])) {

                // Validasi ulang: Pastikan semua sudah diapprove Leader
                $invalidData = $headers->contains(fn($h) => $h->prepared_status != 'Approved');
                if ($invalidData) {
                    return back()->with('error', 'Gagal: Ada data yang belum disetujui Leader atau Rejected.');
                }

                // Update Semua
                DryFractionationHeader::whereDate('date', $date)
                    ->where('flag', 'T')
                    ->update([
                        'checked_status'         => $request->approve_status,
                        'checked_status_remarks' => $request->remark ?? 'Bulk Action per Date',
                        'checked_date'           => now(),
                        'checked_by'             => $user->username ?? $user->name,
                    ]);
            }
            // --- LOGIC LEADER ---
            elseif (in_array($role, ['LEAD', 'LEAD_PROD'])) {
                DryFractionationHeader::whereDate('date', $date)
                    ->where('flag', 'T')
                    ->whereNull('prepared_status') // Hanya yang belum diproses
                    ->update([
                        'prepared_status'         => $request->approve_status,
                        'prepared_status_remarks' => $request->remark ?? 'Bulk Action per Date',
                        'prepared_date'           => now(),
                        'prepared_by'             => $user->username ?? $user->name,
                    ]);
            } else {
                return back()->with('error', 'Unauthorized.');
            }

            DB::commit();
            return back()->with('success', "Semua data tanggal $date berhasil di-{$request->approve_status}.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }


    public function preview(Request $request)
    {

        $startDate = $request->input('start_date') ?? $request->input('filter_tanggal') ?? Carbon::now()->format('Y-m-d');
        $endDate   = $request->input('end_date') ?? $startDate; // Default end date is start date if not provided


        $query = DryFractionationHeader::with(['details'])
            ->where('flag', 'T')
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->orderBy('date', 'asc')
            ->orderBy('filling_start_time', 'asc');

        $rawData = $query->get();
        $groupedData = $rawData->groupBy('date');

        $formInfoFirst = null;
        $formInfoLast = null;



        return view('rpt_logsheetDryFractionation.preview', compact(
            'groupedData',
            'startDate',
            'endDate',
            'formInfoFirst',
            'formInfoLast',

        ));
    }


    public function export(Request $request)
    {

        $startDate = $request->input('start_date') ?? $request->input('filter_tanggal') ?? Carbon::now()->format('Y-m-d');
        $endDate   = $request->input('end_date') ?? $startDate; // Default end date is start date if not provided


        $query = DryFractionationHeader::with(['details'])
            ->where('flag', 'T')
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->orderBy('date', 'asc')
            ->orderBy('filling_start_time', 'asc');

        $rawData = $query->get();
        $groupedData = $rawData->groupBy('date');

        $formInfoFirst = null;
        $formInfoLast = null;

        $view = "exports.report_logsheetDryFractionation_layout_pdf";



        $pdf = Pdf::loadView($view, compact(
            'groupedData',
            'startDate',
            'endDate',
            'formInfoFirst',
            'formInfoLast',
        ))->setPaper('a3', 'landscape');
        return $pdf->stream("daily_production_fractionation_report_{$startDate}-{$endDate}.pdf");
    }

    public function show($id)
    {
        // Kita gunakan logika join yang sama dengan getMainData agar nama produk muncul
        $header = DryFractionationHeader::with(['details'])
            ->where('flag', 'T')
            ->where('id', $id)
            ->firstOrFail();

        return view('rpt_logsheetDryFractionation.show', compact('header'));
    }
}
