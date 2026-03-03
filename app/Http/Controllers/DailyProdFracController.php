<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\LSDailyProdFrac;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Models\MRolesShiftPrepared;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LSDailyProdFracExport;

class DailyProdFracController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil Input Filter
        $tanggal = $request->input('filter_tanggal', Carbon::today()->format('Y-m-d'));
        $filterWorkCenter = $request->input('filter_work_center');
        $filterApprovalStatus = $request->input('filter_approval_status', '');

        // Get user role for filtering
        $userRole = Auth::user()->roles ?? null;

        // 2. Ambil List Work Center untuk Dropdown Filter
        // Kita ambil unique work_center dari tabel agar dropdown dinamis
        $refineryMachines = LSDailyProdFrac::select('work_center')
            ->distinct()
            ->orderBy('work_center')
            ->get();

        // 3. Query Data Utama
        $query = LSDailyProdFrac::query()->where('flag', 'T');

        // Filter by Date (Gunakan transaction_date agar sesuai mobile app grouping)
        $query->whereDate('transaction_date', $tanggal);

        // Filter by Work Center (Jika user memilih work center)
        if ($request->filled('filter_work_center')) {
            $query->where('work_center', $filterWorkCenter);
        }

        // Ambil data (Flat Data)
        // Kita urutkan dulu biar grouping rapi: Work Center A -> Shift 1, 2, 3
        $allReports = $query->orderBy('work_center', 'asc')
            ->orderBy('shift', 'asc')
            ->orderBy('no', 'asc') // Opsional: urutkan nomor urut
            ->get();

        // Apply approval status filter based on user role
        if ($filterApprovalStatus && in_array($userRole, ['LEAD_PROD', 'LEAD', 'MGR_PROD', 'MGR'])) {
            $isLead = in_array($userRole, ['LEAD_PROD', 'LEAD']);
            
            $allReports = $allReports->filter(function ($report) use ($filterApprovalStatus, $isLead) {
                if ($isLead) {
                    // Leader filters by prepared_status
                    $status = $report->prepared_status;
                } else {
                    // Manager filters by checked_status
                    $status = $report->checked_status;
                }
                
                if ($filterApprovalStatus === 'approved') {
                    return $status === 'Approved';
                } elseif ($filterApprovalStatus === 'non_approved') {
                    return $status === null || $status === 'Pending' || $status === 'Rejected';
                }
                return true;
            });
        }

        // 4. Grouping Data: Work Center -> Shift
        // Hasil: [ 'FRA-01' => [ '1' => [items...], '2' => [items...] ] ]
        $groupedReports = $allReports->groupBy(['work_center', 'shift']);

        // 5. Cek Status Approval Global (Helper function)
        $approvalStatus = $this->getApprovalStatus($tanggal);
        
        $hasIncomplete = LSDailyProdFrac::whereDate('posting_date', $tanggal)
            ->where('flag', 'T')
            ->where(function($q) {
                $q->where('is_completed', 0)->orWhereNull('is_completed');
            })
            ->exists();

        return view('rpt_daily_production.fractionation.index', compact(
            'groupedReports',
            'refineryMachines', // Variabel ini penting untuk dropdown
            'tanggal',
            'approvalStatus',
            'hasIncomplete',
            'filterApprovalStatus'
        ));
    }

    // RptDailyPFraController.php

    public function approveShiftWorkCenter(Request $request)
    {
        $request->validate([
            'posting_date'   => 'required|date',
            'shift'          => 'required',
            'approve_status' => 'required|in:Approved,Rejected', // Pastikan validasi status
            'work_center'    => 'required',
            'remark'         => 'nullable|string',
        ]);

        $user = Auth::user();
        $role = $user->roles;

        // Query Dasar
        $query = LSDailyProdFrac::whereDate('transaction_date', $request->posting_date)
            ->where('shift', $request->shift)
            ->where('work_center', $request->work_center)
            ->where('flag', 'T');

        // --- LOGIC LEADER ---
        if (in_array($role, ['LEAD', 'LEAD_PROD'])) {
            $query->update([
                'prepared_status'         => $request->approve_status, // Gunakan input request
                'prepared_status_remarks' => $request->remark,         // Gunakan input request
                'prepared_date'           => now(),
                'prepared_by'             => $user->username ?? $user->name,
                // Jika Leader reject, reset status manager (opsional, tergantung flow)
                'checked_status'          => null,
            ]);

            $action = $request->approve_status == 'Approved' ? 'di-approve' : 'di-reject';
            return back()->with('success', "Shift {$request->shift} berhasil {$action} oleh Leader.");
        }

        // --- LOGIC MANAGER ---
        if (in_array($role, ['MGR', 'MGR_PROD'])) {
            // Cek: Manager hanya bisa proses jika Leader SUDAH Approve
            // PENTING: Jika Leader Reject, Manager biasanya tidak perlu aksi, atau alur berhenti.
            $leaderNotApproved = (clone $query)->where('prepared_status', '!=', 'Approved')->exists();

            if ($leaderNotApproved) {
                return back()->with('error', 'Gagal: Shift ini belum di-approve oleh Leader.');
            }

            // PERBAIKAN DI SINI: Gunakan Request, jangan Hardcode 'Approved'
            $query->update([
                'checked_status'         => $request->approve_status, // Ambil dari input (Approved/Rejected)
                'checked_status_remarks' => $request->remark,         // Ambil remark reject
                'checked_date'           => now(),
                'checked_by'             => $user->username ?? $user->name,
                'verified_status'         => $request->approve_status, // Ambil dari input (Approved/Rejected)
                'verified_status_remarks' => $request->remark,         // Ambil remark reject
                'verified_date'           => now(),
                'verified_by'             => $user->username ?? $user->name,
            ]);

            $action = $request->approve_status == 'Approved' ? 'di-approve' : 'di-reject';
            return back()->with('success', "Shift {$request->shift} berhasil {$action} oleh Manager.");
        }

        return back()->with('error', 'Unauthorized.');
    }

    /**
     * Helper: Cek status global hari itu (untuk tombol Approve All di atas)
     */

    // private function getApprovalStatus(string $tanggal): array
    // {
    //     $reports = LSDailyProdFrac::whereDate('posting_date', $tanggal)->where('flag', 'T')->get();
    //     $statusMessage = null;
    //     $canApproveReject = false;
    //     $user = Auth::user();
    //     $userRole = $user->roles;

    //     if ($reports->isEmpty()) {
    //         $statusMessage = "Tidak ada data pada tanggal $tanggal.";
    //     } else {
    //         if ($userRole === "MGR_PROD" or $userRole === "MGR") {
    //             if ($reports->contains(fn($r) => is_null($r->prepared_status)))
    //                 $statusMessage = 'Belum dilakukan prepared oleh shift leader.';
    //             elseif ($reports->contains(fn($r) => $r->prepared_status === 'Rejected'))
    //                 $statusMessage = 'Data sudah direject oleh shift leader.';
    //             elseif ($reports->every(fn($r) => !is_null($r->checked_status)))
    //                 $statusMessage = 'Semua data sudah di-review oleh Anda.';
    //             elseif ($reports->every(fn($r) => $r->prepared_status === 'Approved'))
    //                 $canApproveReject = true;
    //             else
    //                 $statusMessage = 'Terdapat data yang tidak valid untuk diproses.';
    //         }
    //     }
    //     return ['canApproveReject' => $canApproveReject, 'statusMessage' => $statusMessage];
    // }


    private function getApprovalStatus(string $tanggal): array
    {
        $reports = LSDailyProdFrac::whereDate('posting_date', $tanggal)
            ->where('flag', 'T')
            ->get();

        $statusMessage = null;
        $canApproveReject = false;

        $user = Auth::user();
        $userRole = $user->roles;

        if ($reports->isEmpty()) {
            return [
                'canApproveReject' => false,
                'statusMessage' => "Tidak ada data pada tanggal $tanggal."
            ];
        }

        // COMMENTED OUT: Shift constraint check - 2024-03-02
        // Previously only MGR roles were processed, now added LEAD roles support

        // Process LEAD roles
        if ($userRole === "LEAD_PROD" or $userRole === "LEAD") {
            // $assignedShifts = MRolesShiftPrepared::where('username', $user->username)->where('isactive', 'T')->pluck('shift_code');
            // if ($assignedShifts->isEmpty()) {
            //     $statusMessage = "User tidak memiliki shift.";
            // } else {
            //     $reportsForUserShifts = LSDailyProdFrac::whereDate('posting_date', $tanggal)->whereIn('shift', $assignedShifts)->get();
            //     ... (original shift logic)
            // }

            // NEW: Simplified logic - role-based only, no shift constraint
            $allReports = LSDailyProdFrac::whereDate('posting_date', $tanggal)->where('flag', 'T')->get();
            
            if ($allReports->contains(fn($r) => !is_null($r->prepared_status))) {
                $statusMessage = 'You have already prepared the reports.';
            } else {
                $canApproveReject = true;
            }
        }
        // Process MANAGER roles
        elseif ($userRole === "MGR_PROD" or $userRole === "MGR") {
            if ($reports->contains(fn($r) => !is_null($r->checked_status))) {
                return [
                    'canApproveReject' => false,
                    'statusMessage' => 'Sebagian data sudah di-review. Approve/Reject global tidak diperbolehkan.'
                ];
            }

            // Belum di-prepared leader
            if ($reports->contains(fn($r) => is_null($r->prepared_status))) {
                $statusMessage = 'Belum dilakukan prepared oleh shift leader.';
            }
            // Ada reject dari leader
            elseif ($reports->contains(fn($r) => $r->prepared_status === 'Rejected')) {
                $statusMessage = 'Data sudah direject oleh shift leader.';
            }
            // Semua leader approve → manager boleh approve all
            elseif ($reports->every(fn($r) => $r->prepared_status === 'Approved')) {
                $canApproveReject = true;
            } else {
                $statusMessage = 'Terdapat data yang tidak valid untuk diproses.';
            }
        } else {
            // Other roles not allowed
            return [
                'canApproveReject' => false,
                'statusMessage' => null
            ];
        }

        return [
            'canApproveReject' => $canApproveReject,
            'statusMessage' => $statusMessage
        ];
    }



    public function approvalDate(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'transaction_date' => 'required|date',
            'approve_status' => 'required',
            'remark' => 'nullable|string',
        ]);
        // PENTING: Hanya Manager yang boleh akses fungsi ini
        if (!in_array($user->roles, ['MGR', 'MGR_PROD'])) {
            return back()->with('error', 'Akses ditolak. Leader harus approve per shift.');
        }

        $date = $request->transaction_date;

        // Cek Safety: Apakah ada yang belum diapprove Leader?
        $pendingReports = LSDailyProdFrac::whereDate('transaction_date', $date)
            ->where('flag', 'T')
            ->whereNull('prepared_status') // Belum diapprove leader
            ->exists();

        if ($pendingReports) {
            return back()->with('error', 'Gagal: Masih ada laporan yang belum diapprove oleh Leader.');
        }

        // Eksekusi Approve
        LSDailyProdFrac::whereDate('transaction_date', $date)
            ->where('flag', 'T')
            ->update([
                'checked_status' => $request->approve_status,
                'checked_status_remarks' => $request->remark,
                'checked_date' => now(),
                'checked_by' => $user->username ?? $user->name,
                'verified_status' => $request->approve_status,
                'verified_status_remarks' => $request->remark,
                'verified_date' => now(),
                'verified_by' => $user->username ?? $user->name,
            ]);

        return back()->with('success', "Seluruh laporan tanggal $date berhasil di-approve.");
    }




    private function renderPreview(Request $request, string $view)
    {
        $tanggal = $request->input('filter_tanggal', now()->toDateString());
        $workCenter = $request->input('filter_work_center');
        $data = $this->getMainData($tanggal, $workCenter);
        [$formInfoFirst, $formInfoLast] = $this->getFormInfo($tanggal, $workCenter);

        $groupedData = empty($workCenter) ? $data->groupBy('work_center') : collect();
        $signatures = $this->getSignatures($tanggal, $workCenter);
        $sign = $data->first();

        return view($view, compact('data', 'groupedData', 'tanggal', 'workCenter', 'signatures', 'sign', 'formInfoFirst', 'formInfoLast'));
    }

    /**
     * Renders the PDF for export.
     */
    private function renderPdf(Request $request, string $view)
    {
        $tanggal = $request->input('filter_tanggal', now()->toDateString());
        $workCenter = $request->input('filter_work_center');
        $data = $this->getMainData($tanggal, $workCenter);
        [$formInfoFirst, $formInfoLast] = $this->getFormInfo($tanggal, $workCenter);
        $groupedData = empty($workCenter) ? $data->groupBy('work_center') : collect();
        $signatures = $this->getSignatures($tanggal, $workCenter);

        $pdf = Pdf::loadView($view, compact('data', 'groupedData', 'tanggal', 'workCenter', 'formInfoFirst', 'formInfoLast', 'signatures'))->setPaper('a3', 'landscape');
        return $pdf->stream("daily_production_fractionation_report_{$tanggal}.pdf");
    }


    public function exportExcel(Request $request)
    {
        $tanggal = $request->input('filter_tanggal', Carbon::today()->format('Y-m-d'));
        $filename = 'logsheet_daily_production_fractionation_' . Carbon::parse($tanggal)->format('Y_m_d') . '.xlsx';

        // Assuming an export class exists: App\Exports\LSProductionFractionationExport
        return Excel::download(new LSDailyProdFracExport($tanggal), $filename);
    }


    public function exportLayoutPreview(Request $request)
    {
        return $this->renderPreview($request, 'rpt_daily_production.fractionation.preview');
    }

    /**
     * EXPORT PDF
     */
    public function exportPdf(Request $request)
    {
        return $this->renderPdf($request, 'exports.report_dailyPFra_layout_pdf');
    }

    //  public function show(Request $request)
    // {
    //     $tanggal = $request->input('filter_tanggal', now()->toDateString());
    //     $workCenter = $request->input('filter_work_center');
    //     $shift = $request->input('filter_shift');

    //     $allReports = $this->getMainDataForShow($tanggal, $workCenter, $shift)
    //         ->orderBy('shift', 'asc')
    //         ->orderBy('no', 'asc') // Opsional: urutkan nomor urut
    //         ->get();

    //     // 4. Grouping Data: Work Center -> Shift
    //     // Hasil: [ 'FRA-01' => [ '1' => [items...], '2' => [items...] ] ]
    //     $groupedReports = $allReports->groupBy(['work_center', 'shift']);

    //     // 5. Cek Status Approval Global (Helper function)
    //     $approvalStatus = $this->getApprovalStatus($tanggal);

    //     return view('rpt_daily_production.fractionation.show', compact(
    //         'groupedReports',
    //     ));
    // }


    public function show(Request $request, $id)
    {
        $selectedShift = $request->input('shift');

        $rows = LSDailyProdFrac::query()
            ->select([
                't_daily_production_fractionation.*',
                'p_rm.raw_material AS oil_type_rm_name',
                'p_fgs.finish_good AS oil_type_fgs_name',
                'p_fgh.finish_good AS oil_type_fgh_name',
            ])
            ->leftJoin('m_product as p_rm', 't_daily_production_fractionation.oil_type_rm', '=', 'p_rm.id')
            ->leftJoin('m_product as p_fgs', 't_daily_production_fractionation.oil_type_fgs', '=', 'p_fgs.id')
            ->leftJoin('m_product as p_fgh', 't_daily_production_fractionation.oil_type_fgh', '=', 'p_fgh.id')
            ->where('t_daily_production_fractionation.id', $id)
            ->where('t_daily_production_fractionation.flag', 'T')
            ->when($selectedShift, fn($query) => $query->where('t_daily_production_fractionation.shift', $selectedShift))
            ->orderBy('t_daily_production_fractionation.shift')
            ->orderBy('t_daily_production_fractionation.no')
            ->get();

        if ($rows->isEmpty()) {
            abort(404);
        }

        $rowsByShift = $rows->groupBy('shift');
        $firstReport = $rows->first();
        $ticketId = $id;

        return view('rpt_daily_production.fractionation.show', compact(
            'ticketId',
            'selectedShift',
            'rows',
            'rowsByShift',
            'firstReport'
        ));
    }


    private function getMainData(string $tanggal, ?string $workCenter)
    {
        $user = Auth::user();
        
        // Mulai Query dari tabel transaksi
        $query = LSDailyProdFrac::query()
            // 1. JOIN untuk Raw Material (Alias: p_rm)
            ->leftJoin('m_product as p_rm', 't_daily_production_fractionation.oil_type_rm', '=', 'p_rm.id')
            
            // 2. JOIN untuk Finish Goods (Alias: p_fgs)
            ->leftJoin('m_product as p_fgs', 't_daily_production_fractionation.oil_type_fgs', '=', 'p_fgs.id')
            
            // 3. JOIN untuk By Product (Alias: p_fgh) - INI YANG ANDA BUTUHKAN
            ->leftJoin('m_product as p_fgh', 't_daily_production_fractionation.oil_type_fgh', '=', 'p_fgh.id')
            
            ->whereDate('t_daily_production_fractionation.posting_date', $tanggal)
            ->where('t_daily_production_fractionation.flag', 'T');

        if ($workCenter) {
            $query->where('t_daily_production_fractionation.work_center', $workCenter);
        }

        $baseSelect = [
            't_daily_production_fractionation.*',
            
            // Ambil Nama RM dari alias p_rm
            // Asumsi kolom nama di db adalah 'raw_material' atau 'product_name' (sesuaikan dengan DB Anda)
            'p_rm.raw_material AS oil_type_rm', 
            't_daily_production_fractionation.oil_type_rm AS oil_type_rm_id', // ID asli

            // Ambil Nama FGS dari alias p_fgs
            'p_fgs.finish_good AS oil_type_fgs', 
            't_daily_production_fractionation.oil_type_fgs AS oil_type_fg_id', // ID asli

            // Ambil Nama FGH dari alias p_fgh
            'p_fgh.finish_good AS oil_type_fgh',
            't_daily_production_fractionation.oil_type_fgh AS oil_type_fgh_id', // ID asli
        ];

        return $query->select($baseSelect)->get();
    }


    private function getMainDataForShow(string $tanggal, ?string $workCenter, ?string $shift)
    {
        // Mulai Query dari tabel transaksi
        $query = LSDailyProdFrac::query()
            // 1. JOIN untuk Raw Material (Alias: p_rm)
            ->leftJoin('m_product as p_rm', 't_daily_production_fractionation.oil_type_rm', '=', 'p_rm.id')
            
            // 2. JOIN untuk Finish Goods (Alias: p_fgs)
            ->leftJoin('m_product as p_fgs', 't_daily_production_fractionation.oil_type_fgs', '=', 'p_fgs.id')
            
            // 3. JOIN untuk By Product (Alias: p_fgh) - INI YANG ANDA BUTUHKAN
            ->leftJoin('m_product as p_fgh', 't_daily_production_fractionation.oil_type_fgh', '=', 'p_fgh.id')
            
            ->whereDate('t_daily_production_fractionation.posting_date', $tanggal)
            ->where('t_daily_production_fractionation.flag', 'T');

        if ($workCenter) {
            $query->where('t_daily_production_fractionation.work_center', $workCenter);
        }

        if ($shift) {
            $query->where('t_daily_production_fractionation.shift', $shift);
        }

        $baseSelect = [
            't_daily_production_fractionation.*',
            
            // Ambil Nama RM dari alias p_rm
            // Asumsi kolom nama di db adalah 'raw_material' atau 'product_name' (sesuaikan dengan DB Anda)
            'p_rm.raw_material AS oil_type_rm', 
            't_daily_production_fractionation.oil_type_rm AS oil_type_rm_id', // ID asli

            // Ambil Nama FGS dari alias p_fgs
            'p_fgs.finish_good AS oil_type_fgs', 
            't_daily_production_fractionation.oil_type_fgs AS oil_type_fg_id', // ID asli

            // Ambil Nama FGH dari alias p_fgh
            'p_fgh.finish_good AS oil_type_fgh',
            't_daily_production_fractionation.oil_type_fgh AS oil_type_fgh_id', // ID asli
        ];

        return $query->select($baseSelect)->get();
    }

    /**
     * Get the signatures (prepared_by) for the report.
     */
    private function getSignatures(string $tanggal, ?string $workCenter): array
    {
        $get = function ($shift) use ($tanggal, $workCenter) {
            $q = LSDailyProdFrac::whereDate('posting_date', $tanggal)
                ->where('shift', $shift)
                ->where('prepared_status', 'Approved');
            if ($workCenter)
                $q->where('work_center', $workCenter);
            $row = $q->orderByDesc('prepared_date')->first(['prepared_by as name', 'prepared_date as date']);
            return $row ? ['name' => $row->name, 'date' => $row->date] : null;
        };

        return [
            '1' => $get('1'),
            '2' => $get('2'),
            '3' => $get('3')
        ];
    }

    /**
     * Get form information (form_no, revision_no, etc.)
     */
    private function getFormInfo(string $tanggal, ?string $workCenter): array
    {
        $base = LSDailyProdFrac::whereDate('posting_date', $tanggal);
        if ($workCenter)
            $base->where('work_center', $workCenter);
        $first = (clone $base)->orderBy('revision_date')->first(['form_no', 'date_issued', 'revision_no', 'revision_date']);
        $last = (clone $base)->orderByDesc('revision_date')->first(['form_no', 'date_issued', 'revision_no', 'revision_date']);
        return [$first, $last];
    }
}
