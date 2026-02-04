<?php

namespace App\Http\Controllers;

use App\Exports\LSQualityExport;
use App\Models\LSQualityReport;
use App\Models\LSQualityReportQc;
use App\Models\MRolesShiftPrepared;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use DB;

class RptQualityController extends Controller
{
    /**
     * INDEX (untuk report normal)
     */
    public function index(Request $request)
    {
        $tanggal = $request->input('filter_tanggal', Carbon::today()->format('Y-m-d'));

        $reports = $this->buildBaseQuery($request, $tanggal)
            ->paginate(8)
            ->withQueryString();

        $shiftStatuses = $this->getShiftStatuses($tanggal);
        $workCenters = LSQualityReport::select('work_center')->distinct()->get();

        $signatures = $this->getSignatures($tanggal, $request->input('work_center'));

        // Panggil helper function
        $approvalStatus = $this->getApprovalStatus($tanggal);

        return view('rpt_quality.index', compact('reports', 'shiftStatuses', 'tanggal', 'workCenters', 'signatures') + $approvalStatus);
    }

    /**
     * INDEX (untuk QC)
     */
    public function indexQc(Request $request)
    {
        $tanggal = $request->input('filter_tanggal', Carbon::today()->format('Y-m-d'));

        $reports = $this->buildBaseQueryQc($request, $tanggal)
            ->paginate(8)
            ->withQueryString();

        $shiftStatuses = $this->getShiftStatuses($tanggal);
        $workCenters = LSQualityReportQc::select('work_center')->distinct()->get();

        $signaturesQc = $this->getSignaturesQc($tanggal, $request->input('work_center'));

        // Panggil helper function
        $approvalStatus = $this->getApprovalStatusQc($tanggal);

        return view('rpt_quality.QC.index', compact(
            'reports',
            'shiftStatuses',
            'tanggal',
            'workCenters',
            'signaturesQc',
        ) + $approvalStatus);
    }

    /**
     * SHOW detail report
     */
    public function show($id)
    {
        // $report = LSQualityReport::findOrFail($id);
        // return view('rpt_quality.show', compact('report'));

        $report = LSQualityReport::join('m_product', 't_quality_report_refinery.oil_type', '=', 'm_product.id')
            ->select('t_quality_report_refinery.*', 't_quality_report_refinery.oil_type AS oil_type_id', 'm_product.raw_material AS oil_type')
            ->where('t_quality_report_refinery.id', $id)
            ->firstOrFail();

        return view('rpt_quality.show', compact('report'));
    }

    public function showQc($id)
    {
        // $report = LSQualityReportQc::findOrFail($id);
        // return view('rpt_quality.QC.show', compact('report'));

        // $report = LSQualityReportQc::join('m_product', 't_quality_report_qc.oil_type', '=', 'm_product.id')
        //     ->select('t_quality_report_qc.*', 't_quality_report_qc.oil_type AS oil_type_id', 'm_product.raw_material AS oil_type')
        //     ->where('t_quality_report_qc.id', $id)
        //     ->firstOrFail();

        // return view('rpt_quality.QC.show', compact('report'));

        $report = LSQualityReportQc::join('m_product', 't_quality_report_qc.oil_type', '=', 'm_product.id')
            // Join untuk Prepared By
            ->leftJoin('m_user as u_prep', 't_quality_report_qc.prepared_by', '=', 'u_prep.username')
            // Join untuk Checked By
            ->leftJoin('m_user as u_check', 't_quality_report_qc.checked_by', '=', 'u_check.username')
            ->leftJoin('m_user as u_update', 't_quality_report_qc.updated_by', '=', 'u_update.username')
            ->leftJoin('m_user as u_entry', 't_quality_report_qc.entry_by', '=', 'u_entry.username')
            ->select(
                't_quality_report_qc.*',
                't_quality_report_qc.oil_type AS oil_type_id',
                'm_product.raw_material AS oil_type',
                // Ambil data user prepared
                'u_prep.fullname as prepared_fullname',
                'u_prep.roles as prepared_role',
                // Ambil data user checked
                'u_check.fullname as checked_fullname',
                'u_check.roles as checked_role',

                'u_update.fullname as updated_fullname',
                'u_update.roles as updated_role',

                'u_entry.fullname as entry_fullname',
                'u_entry.roles as entry_role'
            )
            ->where('t_quality_report_qc.id', $id)
            ->firstOrFail();

        return view('rpt_quality.QC.show', compact('report'));
    }

    /**
     * EXPORT Excel
     */
    public function exportExcel(Request $request)
    {
        $tanggal = $request->input('filter_tanggal', Carbon::today()->format('Y-m-d'));
        $filename = 'logsheet_quality_report_' . Carbon::parse($tanggal)->format('Y_m_d') . '.xlsx';

        return Excel::download(new LSQualityExport($tanggal), $filename);
    }

    /**
     * EXPORT Layout Preview (normal)
     */
    public function exportLayoutPreview(Request $request)
    {
        return $this->renderPreview($request, 'rpt_quality.preview');
    }

    /**
     * EXPORT Layout Preview (QC)
     */
    public function exportLayoutPreviewQc(Request $request)
    {
        return $this->renderPreview($request, 'rpt_quality.QC.preview');
    }

    /**
     * EXPORT PDF (normal)
     */
    public function exportPdf(Request $request)
    {
        return $this->renderPdf($request, 'exports.report_quality_layout_pdf');
    }

    /**
     * EXPORT PDF (QC)
     */
    public function exportPdfQc(Request $request)
    {
        return $this->renderPdf($request, 'exports.report_quality_layout_pdf_qc');
    }

    /**
     * APPROVE all by date
     */
    public function approveDate(Request $request)
    {
        $date = $request->posting_date;
        $user = Auth::user();
        $role = $user->roles;

        // Cek kondisi prepared_status
        $reports = LSQualityReport::whereDate('posting_date', $date)
            ->where('flag', 'T')
            ->get();

        if ($reports->isEmpty()) {
            return back()->with('error', "Tidak ada data pada tanggal $date.");
        }

        // Kalau ada yang di-reject oleh shift leader
        if ($reports->contains(fn($r) => $r->prepared_status === 'Rejected')) {
            return back()->with('error', 'Data sudah direject oleh shift leader.');
        }

        if ($role === "LEAD_PROD" or $role === "LEAD") {
            //  Ambil shift user
            $assignedShifts = MRolesShiftPrepared::where('username', $user->username)
                ->where('isactive', 'T')
                ->pluck('shift_code');
            $message = "Semua data tanggal $date berhasil di-prepare.";
            if ($assignedShifts->isEmpty()) {
                $statusMessage = "User tidak memiliki shift.";
            } else {
                // Ambil semua report berdasarkan shift user
                LSQualityReport::whereDate('posting_date', $date)
                    ->whereIn('shift', $assignedShifts)
                    ->update([
                        'prepared_status' => 'Approved',
                        'prepared_status_remarks' => null,
                        'prepared_date' => now(),
                        'prepared_by' => auth()->user()->username ?? auth()->user()->name,
                    ]);
            }
        } else if ($role === "MGR_PROD" or $role === "MGR") {
            // Kalau ada salah satu yang belum di-prepare
            if ($reports->contains(fn($r) => is_null($r->prepared_status))) {
                return back()->with('error', 'Belum dilakukan prepared oleh shift leader.');
            }

            LSQualityReport::whereDate('posting_date', $date)->update([
                'checked_status' => 'Approved',
                'checked_status_remarks' => null,
                'checked_date' => now(),
                'checked_by' => auth()->user()->username ?? auth()->user()->name,
            ]);
        }

        return back()->with('success', "Semua data tanggal $date berhasil di-approve.");
    }

    /**
     * REJECT all by date
     */
    public function rejectDate(Request $request)
    {
        $request->validate([
            'posting_date' => 'required|date',
            'remark' => 'nullable|string|max:255',
        ]);

        $date = $request->posting_date;
        $user = Auth::user();
        $role = $user->roles;
        $message = '';

        if ($role === 'LEAD_PROD' || $role === 'LEAD') {
            // Get the current user's assigned shifts
            $assignedShifts = MRolesShiftPrepared::where('username', $user->username)
                ->where('isactive', 'T')
                ->pluck('shift_code');

            if ($assignedShifts->isEmpty()) {
                return back()->with('error', 'You have no active shifts assigned.');
            }

            // Update only the reports for the user's assigned shifts
            LSQualityReport::whereDate('posting_date', $date)
                ->whereIn('shift', $assignedShifts)
                ->update([
                    'prepared_status' => 'Rejected',
                    'prepared_status_remarks' => $request->remark,
                    'prepared_date' => now(),
                    'prepared_by' => $user->username ?? $user->name,
                ]);

            $message = "All reports for your assigned shifts on {$date} have been rejected.";
        } elseif ($role === 'MGR_PROD' || $role === 'MGR') {
            // This logic for Managers remains the same
            LSQualityReport::whereDate('posting_date', $date)
                ->update([
                    'checked_status' => 'Rejected',
                    'checked_status_remarks' => $request->remark,
                    'checked_date' => now(),
                    'checked_by' => $user->username ?? $user->name,
                ]);
            $message = "All data on {$date} has been rejected (Checked).";
        } else {
            return back()->with('error', 'You do not have permission to perform this action.');
        }

        return back()->with('success', $message);
    }

    /**
     * APPROVE all by date QC
     */
    public function approveDateQc(Request $request)
    {
        $date = $request->posting_date;
        $user = Auth::user();
        $role = $user->roles;

        // Cek kondisi prepared_status
        $reports = LSQualityReportQc::whereDate('posting_date', $date)
            ->where('flag', 'T')
            ->get();

        if ($reports->isEmpty()) {
            return back()->with('error', "Tidak ada data pada tanggal $date.");
        }

        // Kalau ada yang di-reject oleh shift leader
        if ($reports->contains(fn($r) => $r->prepared_status === 'Rejected')) {
            return back()->with('error', 'Data sudah direject oleh shift leader.');
        }

        if ($role === 'LEAD_QC' or $role === "LEAD") {
            //  Ambil shift user
            $assignedShifts = MRolesShiftPrepared::where('username', $user->username)
                ->where('isactive', 'T')
                ->pluck('shift_code');

            if ($assignedShifts->isEmpty()) {
                $statusMessage = "User tidak memiliki shift.";
            } else {
                LSQualityReportQc::whereDate('posting_date', $date)
                    ->whereIn('shift', $assignedShifts)
                    ->where('flag', 'T')
                    ->update([
                        'prepared_status' => 'Approved',
                        'prepared_status_remarks' => null,
                        'prepared_date' => now(),
                        'prepared_by' => $user->username ?? $user->name,
                    ]);
                // $message = "Semua data QC tanggal $date berhasil di-prepare.";
                $message = "Semua data QC tanggal $date berhasil di-prepare.";
            }
        } elseif ($role === 'MGR_QC' or $role === 'MGR') {
            // Kalau ada salah satu yang belum di-prepare
            if ($reports->contains(fn($r) => is_null($r->prepared_status))) {
                return back()->with('error', 'Belum dilakukan prepared oleh shift leader.');
            }

            if ($reports->contains(fn($r) => is_null($r->prepared_status) || $r->prepared_status === 'Rejected')) {
                return back()->with('error', 'Data QC belum di-prepare atau sudah di-reject oleh shift leader.');
            }
            LSQualityReportQc::whereDate('posting_date', $date)
                ->where('flag', 'T')
                ->update([
                    'checked_status' => 'Approved',
                    'checked_status_remarks' => null,
                    'checked_date' => now(),
                    'checked_by' => $user->username ?? $user->name,
                ]);
            $message = "Semua data QC tanggal $date berhasil di-approve.";
        } else {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk melakukan tindakan ini.');
        }

        return back()->with('success', $message);
    }

    /**
     * REJECT all by date QC
     */
    public function rejectDateQc(Request $request)
    {
        $request->validate([
            'posting_date' => 'required|date',
            'remark' => 'nullable|string|max:255',
        ]);

        $date = $request->posting_date;
        $user = Auth::user();
        $role = $user->roles;
        $reports = LSQualityReportQc::whereDate('posting_date', $date)->get();

        if ($role === 'LEAD_QC' || $role === 'LEAD') {
            // Get the current user's assigned shifts
            $assignedShifts = MRolesShiftPrepared::where('username', $user->username)
                ->where('isactive', 'T')
                ->pluck('shift_code');

            if ($assignedShifts->isEmpty()) {
                return back()->with('error', 'You have no active shifts assigned.');
            }

            // Update only the reports for the user's assigned shifts
            LSQualityReportQc::whereDate('posting_date', $date)
                ->whereIn('shift', $assignedShifts)
                ->update([
                    'prepared_status' => 'Rejected',
                    'prepared_status_remarks' => $request->remark,
                    'prepared_date' => now(),
                    'prepared_by' => $user->username ?? $user->name,
                ]);

            $message = "All reports for your assigned shifts on {$date} have been rejected.";
        } elseif ($role === 'MGR_QC' || $role === 'MGR') {
            // This logic for Managers remains the same
            LSQualityReportQc::whereDate('posting_date', $date)
                ->update([
                    'checked_status' => 'Rejected',
                    'checked_status_remarks' => $request->remark,
                    'checked_date' => now(),
                    'checked_by' => $user->username ?? $user->name,
                ]);
            $message = "All data on {$date} has been rejected (Checked).";
        } else {
            return back()->with('error', 'You do not have permission to perform this action.');
        }

        return back()->with('success', $message);
    }


    /**
     * APPROVE by ticket id Produksi
     */
    public function approveTicket($id)
    {
        $report = LSQualityReport::findOrFail($id);
        $userRole = Auth::user()->roles;


        if ($userRole === "LEAD" or $userRole === "LEAD_PROD") {
            $report->update([
                'prepared_status' => 'Approved',
                'prepared_status_remarks' => null,
                'prepared_date' => now(),
                'prepared_by' => auth()->user()->username ?? auth()->user()->name,
            ]);

            return back()->with('success', "Tiket {$report->id} berhasil di-approve (QC).");
        } elseif ($userRole === "MGR" or $userRole === "MGR_PROD") {
            $report->update([
                'checked_status' => 'Approved',
                'checked_status_remarks' => null,
                'checked_date' => now(),
                'checked_by' => auth()->user()->username ?? auth()->user()->name,
            ]);

            return back()->with('success', "Tiket {$report->id} berhasil di-approve (QC).");
        }
    }

    /**
     * REJECT by ticket id Produksi
     */
    public function rejectTicket(Request $request, $id)
    {
        $request->validate([
            'remark' => 'nullable|string|max:255',
        ]);

        $report = LSQualityReport::findOrFail($id);
        $userRole = Auth::user()->roles;


        if ($userRole === "LEAD" or $userRole === "LEAD_PROD") {
            $report->update([
                'prepared_status' => 'Rejected',
                'prepared_status_remarks' => $request->remark,
                'prepared_date' => now(),
                'prepared_by' => auth()->user()->username ?? auth()->user()->name,

            ]);

            return back()->with('success', "Tiket {$report->id} berhasil di-reject (QC).");
        } elseif ($userRole === "MGR" or $userRole === "MGR_PROD") {
            $report->update([
                'checked_status' => 'Rejected',
                'checked_status_remarks' => $request->remark,
                'checked_date' => now(),
                'checked_by' => auth()->user()->username ?? auth()->user()->name,
            ]);

            return back()->with('success', "Tiket {$report->id} berhasil di-reject (QC).");
        }

        return back()->with('success', "Tiket {$report->id} berhasil direject (Produksi).");
    }

    /**
     * APPROVE by ticket id QC
     */
    public function approveTicketQc($id)
    {
        $report = LSQualityReportQc::findOrFail($id);
        $userRole = Auth::user()->roles;

        if ($userRole === "LEAD" or $userRole === "LEAD_QC") {
            $report->update([
                'prepared_status' => 'Approved',
                'prepared_status_remarks' => null,
                'prepared_date' => now(),
                'prepared_by' => auth()->user()->username ?? auth()->user()->name,
            ]);

            return back()->with('success', "Tiket {$report->id} berhasil di-approve (QC).");
        } elseif ($userRole === "MGR" or $userRole === "MGR_QC") {
            $report->update([
                'checked_status' => 'Approved',
                'checked_status_remarks' => null,
                'checked_date' => now(),
                'checked_by' => auth()->user()->username ?? auth()->user()->name,
            ]);

            return back()->with('success', "Tiket {$report->id} berhasil di-approve (QC).");
        }
    }

    /**
     * REJECT by ticket id QC
     */
    public function rejectTicketQc(Request $request, $id)
    {
        $request->validate([
            'remark' => 'nullable|string|max:255',
        ]);

        $report = LSQualityReportQc::findOrFail($id);
        $userRole = Auth::user()->roles;

        if ($userRole === "LEAD" or $userRole === "LEAD_QC") {
            $report->update([
                'prepared_status' => 'Rejected',
                'prepared_status_remarks' => $request->remark,
                'prepared_date' => now(),
                'prepared_by' => auth()->user()->username ?? auth()->user()->name,

            ]);

            return back()->with('success', "Tiket {$report->id} berhasil di-reject (QC).");
        } elseif ($userRole === "MGR" or $userRole === "MGR_QC") {
            $report->update([
                'checked_status' => 'Rejected',
                'checked_status_remarks' => $request->remark,
                'checked_date' => now(),
                'checked_by' => auth()->user()->username ?? auth()->user()->name,
            ]);

            return back()->with('success', "Tiket {$report->id} berhasil di-reject (QC).");
        }

        return back()->with('success', "Tiket {$report->id} berhasil direject (QC).");
    }

    /* ==========================================================================
     * PRIVATE HELPERS
     * ========================================================================== */

    private function buildBaseQuery(Request $request, string $tanggal)
    {
        $user = Auth::user();
        $userRole = $user->roles;
        // $query = LSQualityReport::query()->whereDate('posting_date', $tanggal);

        $query = LSQualityReport::join('m_product', 't_quality_report_refinery.oil_type', '=', 'm_product.id')
            ->whereDate('t_quality_report_refinery.posting_date', $tanggal);

        if ($request->filled('filter_jam')) {
            $query->where('t_quality_report_refinery.time', $request->filter_jam);
        }

        if ($request->filled('filter_work_center')) {
            $query->where('t_quality_report_refinery.work_center', $request->filter_work_center);
        }

        // Tambahkan baris ini untuk filter flag
        $query->where('t_quality_report_refinery.flag', 'T');

        // Define the select statement
        $baseSelect = [
            't_quality_report_refinery.*',
            't_quality_report_refinery.oil_type AS oil_type_id',
            'm_product.raw_material AS oil_type'
        ];

        // Tidak ada filter tambahan untuk manager.
        return $query->select($baseSelect)->reorder()
            ->orderByRaw("CASE WHEN t_quality_report_refinery.time >= '08:00' THEN 0 ELSE 1 END")
            ->orderBy('t_quality_report_refinery.shift', 'asc')
            ->orderBy('t_quality_report_refinery.time', 'asc');
    }

    private function buildBaseQueryQc(Request $request, string $tanggal)
    {

        // $query = LSQualityReportQc::query()->whereDate('posting_date', $tanggal);

        $query = LSQualityReportQc::with('dailyProduction')
            ->join('m_product', 't_quality_report_qc.oil_type', '=', 'm_product.id')
            ->whereDate('t_quality_report_qc.posting_date', $tanggal);

        if ($request->filled('filter_jam')) {
            $query->where('t_quality_report_qc.time', $request->filter_jam);
        }

        if ($request->filled('filter_work_center')) {
            $query->where('t_quality_report_qc.work_center', $request->filter_work_center);
        }

        $user = Auth::user();
        $userRole = $user->roles;

        // Tambahkan baris ini untuk filter flag
        $query->where('t_quality_report_qc.flag', 'T');

        $baseSelect = [
            't_quality_report_qc.*',
            't_quality_report_qc.oil_type AS oil_type_id',
            'm_product.raw_material AS oil_type'
        ];

        return $query->select($baseSelect)->reorder()
            ->orderByRaw("CASE WHEN time >= '08:00' THEN 0 ELSE 1 END")
            ->orderBy('shift', 'asc')
            ->orderBy('time', 'asc');
    }

    private function getShiftStatuses(string $tanggal): array
    {
        return [
            'shift1' => $this->checkShiftStatus($tanggal, '08:00:00', '15:59:59'),
            'shift2' => $this->checkShiftStatus($tanggal, '16:00:00', '23:59:59'),
            'shift3' => $this->checkShiftStatus($tanggal, '00:00:00', '07:59:59'),
        ];
    }

    private function renderPreview(Request $request, string $view)
    {
        $tanggal = $request->input('filter_tanggal', now()->toDateString());
        $workCenter = $request->input('filter_work_center');

        // Memanggil set fungsi yang sesuai berdasarkan view
        if (str_contains($view, 'QC')) {
            $data = $this->getMainDataQc($tanggal, $workCenter);
            [$formInfoFirst, $formInfoLast] = $this->getFormInfoQc($tanggal, $workCenter);
            $refinery = $this->getRefineryQc($tanggal, $workCenter);
            $oilType = $this->getOilTypeQc($tanggal, $workCenter);
        } else {
            $data = $this->getMainData($tanggal, $workCenter);
            [$formInfoFirst, $formInfoLast] = $this->getFormInfo($tanggal, $workCenter);
            $refinery = $this->getRefinery($tanggal, $workCenter);
            $oilType = $this->getOilType($tanggal, $workCenter);
        }

        $groupedData = empty($workCenter) ? $data->groupBy('work_center') : collect();
        $signatures = $this->getSignatures($tanggal, $workCenter);
        $signaturesQc = $this->getSignaturesQc($tanggal, $workCenter);
        $sign = $data->first();

        return view($view, compact('data', 'groupedData', 'tanggal', 'workCenter', 'signatures', 'signaturesQc', 'sign', 'formInfoFirst', 'formInfoLast', 'refinery', 'oilType'));
    }

    private function renderPdf(Request $request, string $view)
    {
        $tanggal = $request->input('filter_tanggal', now()->toDateString());
        $workCenter = $request->input('filter_work_center');

        // Memanggil set fungsi yang sesuai berdasarkan view
        if (str_contains($view, 'qc')) {
            $data = $this->getMainDataQc($tanggal, $workCenter);
            [$formInfoFirst, $formInfoLast] = $this->getFormInfoQc($tanggal, $workCenter);
            $refinery = $this->getRefineryQc($tanggal, $workCenter);
            $oilType = $this->getOilTypeQc($tanggal, $workCenter);
        } else {
            $data = $this->getMainData($tanggal, $workCenter);
            [$formInfoFirst, $formInfoLast] = $this->getFormInfo($tanggal, $workCenter);
            $refinery = $this->getRefinery($tanggal, $workCenter);
            $oilType = $this->getOilType($tanggal, $workCenter);
        }

        $groupedData = empty($workCenter) ? $data->groupBy('work_center') : collect();
        $signatures = $this->getSignatures($tanggal, $workCenter);
        $signaturesQc = $this->getSignaturesQc($tanggal, $workCenter);
        $lastShift = collect($signaturesQc)->filter(fn($s) => data_get($s, 'prepared') || data_get($s, 'acknowledge'))->last();

        $pdf = Pdf::loadView($view, compact('data', 'groupedData', 'tanggal', 'workCenter', 'formInfoFirst', 'formInfoLast', 'refinery', 'oilType', 'signatures', 'signaturesQc', 'lastShift'))->setPaper('a3', 'landscape');
        return $pdf->stream("quality_report_{$tanggal}.pdf");
    }


    private function getMainData(string $tanggal, ?string $workCenter)
    {
        $user = Auth::user();
        $userRole = $user->roles;

        // $query = LSQualityReport::whereDate('posting_date', $tanggal);
        $query = LSQualityReport::join('m_product', 't_quality_report_refinery.oil_type', '=', 'm_product.id')
            ->whereDate('t_quality_report_refinery.posting_date', $tanggal);

        if ($workCenter) {
            $query->where('work_center', $workCenter);
        }

        $query->where('t_quality_report_refinery.flag', 'T');

        $baseSelect = [
            't_quality_report_refinery.*',
            't_quality_report_refinery.oil_type AS oil_type_id',
            'm_product.raw_material AS oil_type'
        ];

        // if ($userRole === "MGR" or $userRole === "MGR_PROD") {
        //     return $query->join('m_mastervalue', 't_quality_report_refinery.work_center', '=', 'm_mastervalue.code')
        //         ->select('t_quality_report_refinery.*', 'm_mastervalue.name as refinery_name')
        //         ->orderByRaw("CASE
        //             WHEN time BETWEEN '08:00:00' AND '15:59:59' THEN 1
        //             WHEN time BETWEEN '16:00:00' AND '23:59:59' THEN 2
        //             WHEN time BETWEEN '00:00:00' AND '07:59:59' THEN 3
        //             ELSE 4 END")
        //         ->orderBy('time')
        //         ->get();
        // } elseif ($userRole === "LEAD" or $userRole === "LEAD_PROD") {
        //     return $query
        //         ->join('m_roles_shift_prepared', 't_quality_report_refinery.shift', '=', 'm_roles_shift_prepared.shift_code')
        //         ->where('m_roles_shift_prepared.username', $user->username)
        //         ->where('m_roles_shift_prepared.isactive', 'T')
        //         ->select('t_quality_report_refinery.*')
        //         ->orderbyRaw(
        //             "CASE
        //         WHEN time BETWEEN '08:00:00' AND '15:59:59' THEN 1
        //         WHEN time BETWEEN '16:00:00' AND '23:59:59' THEN 2
        //         WHEN time BETWEEN '00:00:00' AND '07:59:59' THEN 3
        //         ELSE 4 END"
        //         )
        //         ->orderby('time')
        //         ->get();
        // }
        return $query->select($baseSelect)
            ->orderBy('time')
            ->get();
    }

    private function getMainDataQc(string $tanggal, ?string $workCenter)
    {
        // $query = LSQualityReportQc::whereDate('posting_date', $tanggal);

        $query = LSQualityReportQc::join('m_product', 't_quality_report_qc.oil_type', '=', 'm_product.id')
            ->whereDate('t_quality_report_qc.posting_date', $tanggal);

        if ($workCenter) {
            $query->where('t_quality_report_qc.work_center', $workCenter);
        }
        $query->where('t_quality_report_qc.flag', 'T');
        return $query->join('m_mastervalue', 't_quality_report_qc.work_center', '=', 'm_mastervalue.code')
            ->select(
                't_quality_report_qc.*',
                't_quality_report_qc.oil_type AS oil_type_id',
                'm_product.raw_material AS oil_type',
                'm_product.finish_good AS oil_type_fg',
                'm_product.by_product AS oil_type_bp',
                'm_mastervalue.name as refinery_name'
            )
            ->orderByRaw("CASE WHEN time BETWEEN '08:00:00' AND '15:59:59' THEN 1 WHEN time BETWEEN '16:00:00' AND '23:59:59' THEN 2 WHEN time BETWEEN '00:00:00' AND '07:59:59' THEN 3 ELSE 4 END")
            ->orderBy('t_quality_report_qc.time')->get();
    }

    private function getSignatures(string $tanggal, ?string $workCenter): array
    {
        $get = function ($start, $end) use ($tanggal, $workCenter) {
            $q = LSQualityReport::whereDate('posting_date', $tanggal)
                ->whereBetween('time', [$start, $end])
                ->where('prepared_status', 'Approved');

            if ($workCenter) {
                $q->where('work_center', $workCenter);
            }

            $row = $q->orderByDesc('time')->first(['prepared_by as name', 'prepared_date as date']);
            return $row ? ['name' => $row->name, 'date' => $row->date] : null;
        };

        return [
            'shift1' => $get('08:00:00', '15:59:59'),
            'shift2' => $get('16:00:00', '23:59:59'),
            'shift3' => $get('00:00:00', '07:59:59'),
        ];
    }

    // private function getSignaturesQc(string $tanggal, ?string $workCenter): array
    // {
    //     $get = function ($start, $end) use ($tanggal, $workCenter) {
    //         $q = LSQualityReportQc::whereDate('posting_date', $tanggal)
    //             ->whereBetween('time', [$start, $end]);

    //         if ($workCenter) {
    //             $q->where('work_center', $workCenter);
    //         }

    //         $prepared = (clone $q)->orderByDesc('time')->first(['prepared_by', 'prepared_date']);
    //         $ack = (clone $q)->orderByDesc('time')->first(['checked_by', 'checked_date']);

    //         return [
    //             'prepared' => $prepared ? ['name' => $prepared->prepared_by, 'date' => $prepared->prepared_date] : null,
    //             'acknowledge' => $ack ? ['name' => $ack->checked_by, 'date' => $ack->checked_date] : null,
    //         ];
    //     };

    //     return [
    //         'shift1' => $get('08:00:00', '15:59:59'),
    //         'shift2' => $get('16:00:00', '23:59:59'),
    //         'shift3' => $get('00:00:00', '07:59:59'),
    //     ];
    // }


    private function getSignaturesQc(string $tanggal, ?string $workCenter): array
    {
        $get = function ($start, $end) use ($tanggal, $workCenter) {
            // Mulai Query dengan Join ke tabel User
            // Kita join 2 kali: satu untuk u_prep (Prepared), satu untuk u_check (Checked)
            $q = LSQualityReportQc::query()
                ->leftJoin('m_user as u_prep', 't_quality_report_qc.prepared_by', '=', 'u_prep.username')
                ->leftJoin('m_user as u_check', 't_quality_report_qc.checked_by', '=', 'u_check.username')
                ->whereDate('t_quality_report_qc.posting_date', $tanggal)
                ->whereBetween('t_quality_report_qc.time', [$start, $end]);

            if ($workCenter) {
                $q->where('t_quality_report_qc.work_center', $workCenter);
            }

            // Pilih kolom spesifik agar tidak tertimpa
            $selectCols = [
                't_quality_report_qc.prepared_by',
                't_quality_report_qc.prepared_date',
                't_quality_report_qc.checked_by',
                't_quality_report_qc.checked_date',
                // Ambil data dari tabel user alias
                'u_prep.fullname as prepared_fullname',
                'u_prep.roles as prepared_role',
                'u_check.fullname as checked_fullname',
                'u_check.roles as checked_role',
            ];

            // Ambil data terakhir di shift tersebut untuk tanda tangan
            $prepared = (clone $q)->orderByDesc('t_quality_report_qc.time')->first($selectCols);
            $ack = (clone $q)->orderByDesc('t_quality_report_qc.time')->first($selectCols);

            return [
                'prepared' => $prepared ? [
                    // Logic: Pakai fullname, kalau null pakai username, kalau null lagi strip
                    'name' => $prepared->prepared_fullname ?? ($prepared->prepared_by ?? '-'),
                    'role' => $prepared->prepared_role ?? 'Shift Leader',
                    'date' => $prepared->prepared_date
                ] : null,
                'acknowledge' => $ack ? [
                    'name' => $ack->checked_fullname ?? ($ack->checked_by ?? '-'),
                    'role' => $ack->checked_role ?? 'SPV / Manager',
                    'date' => $ack->checked_date
                ] : null,
            ];
        };

        return [
            'shift1' => $get('08:00:00', '15:59:59'),
            'shift2' => $get('16:00:00', '23:59:59'),
            'shift3' => $get('00:00:00', '07:59:59'),
        ];
    }



    private function getFormInfo(string $tanggal, ?string $workCenter): array
    {
        $base = LSQualityReport::whereDate('posting_date', $tanggal);
        $firstQuery = clone $base;
        $lastQuery = clone $base;

        if ($workCenter) {
            $firstQuery->where('work_center', $workCenter);
            $lastQuery->where('work_center', $workCenter);
        }

        $first = $firstQuery->orderBy('revision_date')->first(['form_no', 'date_issued', 'revision_no', 'revision_date']);
        $last = $lastQuery->orderByDesc('revision_date')->first(['form_no', 'date_issued', 'revision_no', 'revision_date']);

        return [$first, $last];
    }

    private function getFormInfoQc(string $tanggal, ?string $workCenter): array
    {
        $base = LSQualityReportQc::whereDate('posting_date', $tanggal);
        if ($workCenter) {
            $base->where('work_center', $workCenter);
        }
        $first = (clone $base)->orderBy('revision_date')->first(['form_no', 'date_issued', 'revision_no', 'revision_date']);
        $last = (clone $base)->orderByDesc('revision_date')->first(['form_no', 'date_issued', 'revision_no', 'revision_date']);
        return [$first, $last];
    }

    private function getRefinery(string $tanggal, ?string $workCenter)
    {
        $q = LSQualityReport::whereDate('posting_date', $tanggal)
            ->join('m_mastervalue', 't_quality_report_refinery.work_center', '=', 'm_mastervalue.code')
            ->select('t_quality_report_refinery.work_center', 'm_mastervalue.name');

        if ($workCenter) {
            $q->where('t_quality_report_refinery.work_center', $workCenter);
        }

        return $q->first();
    }

    private function getRefineryQc(string $tanggal, ?string $workCenter)
    {
        $q = LSQualityReportQc::whereDate('posting_date', $tanggal)->join('m_mastervalue', 't_quality_report_qc.work_center', '=', 'm_mastervalue.code')->select('t_quality_report_qc.work_center', 'm_mastervalue.name');
        if ($workCenter) {
            $q->where('t_quality_report_qc.work_center', $workCenter);
        }
        return $q->first();
    }

    private function getOilType(string $tanggal, ?string $workCenter)
    {
        $q = LSQualityReport::whereDate('posting_date', $tanggal)->select('oil_type');

        if ($workCenter) {
            $q->where('work_center', $workCenter);
        }

        return $q->first();
    }

    private function getOilTypeQc(string $tanggal, ?string $workCenter)
    {
        $q = LSQualityReportQc::whereDate('posting_date', $tanggal)->select('oil_type');
        if ($workCenter) {
            $q->where('work_center', $workCenter);
        }
        return $q->first();
    }

    private function checkShiftStatus(string $tanggal, string $start, string $end): string
    {
        $q = LSQualityReport::whereDate('posting_date', $tanggal)->whereBetween('time', [$start, $end]);

        if (!$q->exists()) {
            return 'Belum Ada Transaksi';
        }

        $pending = (clone $q)->where(function ($sub) {
            $sub->whereNull('checked_status')
                ->orWhere('checked_status', '!=', 'Approved');
        })->exists();

        return $pending ? 'Belum Selesai' : 'Approved Semua';
    }

    private function checkShiftStatusQc(string $tanggal, string $start, string $end): string
    {
        $q = LSQualityReportQc::whereDate('posting_date', $tanggal)->whereBetween('time', [$start, $end]);
        if (!$q->exists()) {
            return 'Belum Ada Transaksi';
        }
        $pending = (clone $q)->where(function ($sub) {
            $sub->whereNull('checked_status')->orWhere('checked_status', '!=', 'Approved');
        })->exists();
        return $pending ? 'Belum Selesai' : 'Approved Semua';
    }

    /**
     * Cek apakah tombol approve/reject bisa aktif untuk tanggal tertentu
     */
    private function getApprovalStatus(string $tanggal): array
    {
        $reports = LSQualityReport::whereDate('posting_date', $tanggal)
            ->where('flag', 'T')
            ->get();

        $statusMessage = null;
        $canApproveReject = false;
        $user = Auth::user();
        $userRole = $user->roles;

        if ($reports->isEmpty()) {
            $statusMessage = "Tidak ada data pada tanggal $tanggal.";
        } else {
            if ($userRole === "LEAD_PROD" or $userRole === "LEAD") {
                //  Ambil shift
                $assignedShifts = MRolesShiftPrepared::where('username', $user->username)
                    ->where('isactive', 'T')
                    ->pluck('shift_code');
                if ($assignedShifts->isEmpty()) {
                    $statusMessage = "User tidak memiliki shift.";
                } else {
                    // Ambil semua report berdasarkan shift user
                    $reportsForUserShifts = LSQualityReport::whereDate('posting_date', $tanggal)
                        ->whereIn('shift', $assignedShifts)
                        ->get();

                    $reportedShifts = $reportsForUserShifts->pluck('shift')->unique();

                    $allShiftsReported = $assignedShifts->diff($reportedShifts)->isEmpty();

                    if ($reportsForUserShifts->isEmpty()) {
                        $statusMessage = "No reports submitted for your assigned shifts yet.";
                        $canApproveReject = false;
                    } elseif (!$allShiftsReported) {
                        $missingShifts = $assignedShifts->diff($reportedShifts)->implode(', ');
                        $statusMessage = "Waiting for reports from shift(s): {$missingShifts}.";
                        $canApproveReject = false;
                    } elseif ($reportsForUserShifts->contains(fn($r) => !is_null($r->prepared_status))) {
                        $statusMessage = 'You have already prepared the reports for this date.';
                        $canApproveReject = false;
                    } else {
                        $canApproveReject = true; // All conditions met!
                    }
                }
            } elseif ($userRole === "MGR_PROD" or $userRole === "MGR") {
                if ($reports->contains(fn($r) => is_null($r->prepared_status))) {
                    $statusMessage = 'Belum dilakukan prepared oleh shift leader.';
                    $canApproveReject = false;
                } elseif ($reports->contains(fn($r) => $r->prepared_status === 'Rejected')) {
                    $statusMessage = 'Data sudah direject oleh shift leader.';
                    $canApproveReject = false;
                } elseif ($reports->every(fn($r) => !is_null($r->checked_status))) {
                    $statusMessage = 'Semua data sudah di-review oleh Anda.';
                    $canApproveReject = false;
                } elseif ($reports->every(fn($r) => $r->prepared_status === 'Approved')) {
                    $canApproveReject = true;
                } else {
                    $statusMessage = 'Terdapat data yang tidak valid untuk diproses.';
                    $canApproveReject = false;
                }
            }
        }

        return [
            'canApproveReject' => $canApproveReject,
            'statusMessage' => $statusMessage,
        ];
    }
    /**
     * (Qc) Cek apakah tombol approve/reject bisa aktif untuk tanggal tertentu
     */
    private function getApprovalStatusQc(string $tanggal): array
    {
        $reports = LSQualityReportQc::whereDate('posting_date', $tanggal)
            ->where('flag', 'T')
            ->get();

        $statusMessage = null;
        $canApproveReject = false;
        $user = Auth::user();
        $userRole = $user->roles;

        if ($reports->isEmpty()) {
            $statusMessage = "Tidak ada data pada tanggal $tanggal.";
        } else {
            if ($userRole === "LEAD_QC" or $userRole === "LEAD") {
                $assignedShifts = MRolesShiftPrepared::where('username', $user->username)
                    ->where('isactive', 'T')
                    ->pluck('shift_code');
                if ($assignedShifts->isEmpty()) {
                    $statusMessage = "User tidak memiliki shift.";
                } else {
                    // Ambil semua report berdasarkan shift user
                    $reportsForUserShifts = LSQualityReportQc::whereDate('posting_date', $tanggal)
                        ->whereIn('shift', $assignedShifts)
                        ->get();
                    $reportedShifts = $reportsForUserShifts->pluck('shift')->unique();

                    $allShiftsReported = $assignedShifts->diff($reportedShifts)->isEmpty();

                    if ($reportsForUserShifts->isEmpty()) {
                        $statusMessage = "No reports submitted for your assigned shifts yet.";
                        $canApproveReject = false;
                    } elseif (!$allShiftsReported) {
                        $missingShifts = $assignedShifts->diff($reportedShifts)->implode(', ');
                        $statusMessage = "Waiting for reports from shift(s): {$missingShifts}.";
                        $canApproveReject = false;
                    } elseif ($reportsForUserShifts->contains(fn($r) => !is_null($r->prepared_status))) {
                        $statusMessage = 'You have already prepared the reports for this date.';
                        $canApproveReject = false;
                    } else {
                        $canApproveReject = true; // All conditions met!
                    }

                    // if ($reports->contains(fn($r) => !is_null($r->prepared_status))) {
                    //     $statusMessage = 'Semua data sudah di-prepare Approve/Reject oleh Shift Leader.';
                    //     $canApproveReject = false;
                    // } elseif ($reports->every(fn($r) => $r->prepared_status === 'Rejected') or $reports->every(fn($r) => $r->prepared_status === 'Approved')) {
                    //     $canApproveReject = false;
                    // } else {
                    //     $canApproveReject = true;
                    // }
                }
            } elseif ($userRole === "MGR_QC" or $userRole === "MGR") {
                if ($reports->contains(fn($r) => is_null($r->prepared_status))) {
                    $statusMessage = 'Belum dilakukan prepared oleh shift leader.';
                    $canApproveReject = false;
                } elseif ($reports->contains(fn($r) => $r->prepared_status === 'Rejected')) {
                    $statusMessage = 'Data sudah direject oleh shift leader.';
                    $canApproveReject = false;
                } elseif ($reports->every(fn($r) => !is_null($r->checked_status))) {
                    // This is the NEW and most important check.
                    // If every report has a checked_status, the manager's work is done.
                    $statusMessage = 'Semua data sudah di-review oleh Anda.';
                    $canApproveReject = false;
                } elseif ($reports->every(fn($r) => $r->prepared_status === 'Approved')) {
                    $canApproveReject = true;
                } else {
                    $statusMessage = 'Terdapat data yang tidak valid untuk diproses.';
                    $canApproveReject = false;
                }
            }
        }

        return [
            'canApproveReject' => $canApproveReject,
            'statusMessage' => $statusMessage,
        ];
    }
}
