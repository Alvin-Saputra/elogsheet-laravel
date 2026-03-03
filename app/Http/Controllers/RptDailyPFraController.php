<?php

namespace App\Http\Controllers;

use App\Exports\LSDailyProductionFractionationExport;
use App\Models\LSDailyProductionFractionation;
use App\Models\MRolesShiftPrepared;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class RptDailyPFraController extends Controller
{
    /**
     * INDEX
     */
    public function index(Request $request)
    {
        $tanggal = $request->input('filter_tanggal', Carbon::today()->format('Y-m-d'));

        $reports = $this->buildBaseQuery($request, $tanggal)
            ->paginate(8)
            ->withQueryString();

        // Note: Daily Production typically doesn't use the Deodorizing shift status check
        // $shiftStatuses = $this->getShiftStatuses($tanggal); 

        $refineryMachines = LSDailyProductionFractionation::select('work_center')->distinct()->get();
        $signatures = $this->getSignatures($tanggal, $request->input('filter_work_center'));
        $approvalStatus = $this->getApprovalStatus($tanggal);
        
        $hasIncomplete = LSDailyProductionFractionation::whereDate('posting_date', $tanggal)
            ->where('flag', 'T')
            ->where(function($q) {
                $q->where('is_completed', 0)->orWhereNull('is_completed');
            })
            ->exists();

        // We use an empty array for shiftStatuses if not implemented
        return view('rpt_daily_production.fractionation.index', compact('reports', 'tanggal', 'refineryMachines', 'signatures', 'hasIncomplete') + $approvalStatus);
    }

    /**
     * SHOW detail report
     */
    public function show($id)
    {
        $baseSelect = [
            't_daily_production_fractionation.*',
            't_daily_production_fractionation.oil_type_rm AS oil_type_rm_id',
            'm_product.raw_material AS oil_type_rm',
            't_daily_production_fractionation.oil_type_fgs AS oil_type_fg_id',
            'm_product.finish_good AS oil_type_fgs',
            't_daily_production_fractionation.oil_type_fgh AS oil_type_fgh_id',
            'm_product.by_product AS oil_type_fgh',
        ];
        // $report = LSDailyProductionFractionation::findOrFail($id);
        $report = LSDailyProductionFractionation::join('m_product', 't_daily_production_fractionation.oil_type_rm', '=', 'm_product.id')
            ->select($baseSelect)
            ->where('t_daily_production_fractionation.id', $id)
            ->firstOrFail();
        return view('rpt_daily_production.fractionation.show', compact('report'));
    }

    /**
     * EXPORT Excel
     */
    public function exportExcel(Request $request)
    {
        $tanggal = $request->input('filter_tanggal', Carbon::today()->format('Y-m-d'));
        $filename = 'logsheet_daily_production_fractionation_' . Carbon::parse($tanggal)->format('Y_m_d') . '.xlsx';

        // Assuming an export class exists: App\Exports\LSProductionFractionationExport
        return Excel::download(new LSDailyProductionFractionationExport($tanggal), $filename);
    }

    /**
     * EXPORT Layout Preview
     */
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

    /**
     * APPROVE all by date
     */
    public function approveDate(Request $request)
    {
        $date = $request->posting_date;
        $user = Auth::user();
        $role = $user->roles;

        $reports = LSDailyProductionFractionation::whereDate('posting_date', $date)
            ->where('flag', 'T')
            ->get();

        if ($reports->isEmpty()) {
            return back()->with('error', "Tidak ada data pada tanggal $date.");
        }

        if ($reports->contains(fn($r) => $r->prepared_status === 'Rejected')) {
            return back()->with('error', 'Data sudah direject oleh shift leader.');
        }

        if ($role === "LEAD_PROD" or $role === "LEAD") {
            $assignedShifts = MRolesShiftPrepared::where('username', $user->username)
                ->where('isactive', 'T')
                ->pluck('shift_code');

            if ($assignedShifts->isEmpty()) {
                return back()->with('error', "User tidak memiliki shift.");
            }

            LSDailyProductionFractionation::whereDate('posting_date', $date)
                ->whereIn('shift', $assignedShifts)
                ->update([
                    'prepared_status' => 'Approved',
                    'prepared_status_remarks' => null,
                    'prepared_date' => now(),
                    'prepared_by' => auth()->user()->username ?? auth()->user()->name,
                ]);

        } else if ($role === "MGR_PROD" or $role === "MGR") {
            if ($reports->contains(fn($r) => is_null($r->prepared_status))) {
                return back()->with('error', 'Belum dilakukan prepared oleh shift leader.');
            }

            LSDailyProductionFractionation::whereDate('posting_date', $date)->update([
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
        $request->validate(['posting_date' => 'required|date', 'remark' => 'nullable|string|max:255']);

        $date = $request->posting_date;
        $user = Auth::user();
        $role = $user->roles;
        $message = '';

        if ($role === 'LEAD_PROD' || $role === 'LEAD') {
            $assignedShifts = MRolesShiftPrepared::where('username', $user->username)->where('isactive', 'T')->pluck('shift_code');

            if ($assignedShifts->isEmpty()) {
                return back()->with('error', 'You have no active shifts assigned.');
            }

            LSDailyProductionFractionation::whereDate('posting_date', $date)
                ->whereIn('shift', $assignedShifts)
                ->update(['prepared_status' => 'Rejected', 'prepared_status_remarks' => $request->remark, 'prepared_date' => now(), 'prepared_by' => $user->username ?? $user->name]);

            $message = "All reports for your assigned shifts on {$date} have been rejected.";

        } elseif ($role === 'MGR_PROD' || $role === 'MGR') {
            LSDailyProductionFractionation::whereDate('posting_date', $date)
                ->update(['checked_status' => 'Rejected', 'checked_status_remarks' => $request->remark, 'checked_date' => now(), 'checked_by' => $user->username ?? $user->name]);
            $message = "All data on {$date} has been rejected (Checked).";
        } else {
            return back()->with('error', 'You do not have permission to perform this action.');
        }

        return back()->with('success', $message);
    }

    /**
     * APPROVE by ticket id
     */
    public function approveTicket($id)
    {
        $report = LSDailyProductionFractionation::findOrFail($id);
        $userRole = Auth::user()->roles;

        if ($userRole === "LEAD" or $userRole === "LEAD_PROD") {
            $report->update(['prepared_status' => 'Approved', 'prepared_status_remarks' => null, 'prepared_date' => now(), 'prepared_by' => auth()->user()->username ?? auth()->user()->name]);
        } elseif ($userRole === "MGR" or $userRole === "MGR_PROD") {
            $report->update(['checked_status' => 'Approved', 'checked_status_remarks' => null, 'checked_date' => now(), 'checked_by' => auth()->user()->username ?? auth()->user()->name]);
        }
        return back()->with('success', "Tiket {$report->id} berhasil di-approve.");
    }

    /**
     * REJECT by ticket id
     */
    public function rejectTicket(Request $request, $id)
    {
        $request->validate(['remark' => 'nullable|string|max:255']);
        $report = LSDailyProductionFractionation::findOrFail($id);
        $userRole = Auth::user()->roles;

        if ($userRole === "LEAD" or $userRole === "LEAD_PROD") {
            $report->update(['prepared_status' => 'Rejected', 'prepared_status_remarks' => $request->remark, 'prepared_date' => now(), 'prepared_by' => auth()->user()->username ?? auth()->user()->name]);
        } elseif ($userRole === "MGR" or $userRole === "MGR_PROD") {
            $report->update(['checked_status' => 'Rejected', 'checked_status_remarks' => $request->remark, 'checked_date' => now(), 'checked_by' => auth()->user()->username ?? auth()->user()->name]);
        }
        return back()->with('success', "Tiket {$report->id} berhasil di-reject.");
    }

    /* ==========================================================================
     * PRIVATE HELPERS
     * ========================================================================== */

    /**
     * Build the base query for the index report listing.
     */
    private function buildBaseQuery(Request $request, string $tanggal): Builder
    {
        $user = Auth::user();
        $userRole = $user->roles;
        // $query = LSDailyProductionFractionation::query()->whereDate('posting_date', $tanggal);

        $query = LSDailyProductionFractionation::join('m_product', 't_daily_production_fractionation.oil_type_rm', '=', 'm_product.id')
            ->whereDate('t_daily_production_fractionation.posting_date', $tanggal);

        if ($request->filled('filter_work_center')) {
            $query->where('t_daily_production_fractionation.work_center', $request->filter_work_center);
        }

        $query->where('t_daily_production_fractionation.flag', 'T');

        // if ($userRole === "LEAD" || $userRole === "LEAD_PROD") {
        //     $query->join('m_roles_shift_prepared', 't_daily_production_fractionation.shift', '=', 'm_roles_shift_prepared.shift_code')
        //         ->where('m_roles_shift_prepared.username', $user->username)->where('m_roles_shift_prepared.isactive', 'T')
        //         ->select('t_daily_production_fractionation.*');
        // }

        $baseSelect = [
            't_daily_production_fractionation.*',
            't_daily_production_fractionation.oil_type_rm AS oil_type_rm_id',
            'm_product.raw_material AS oil_type_rm',
            't_daily_production_fractionation.oil_type_fgs AS oil_type_fg_id',
            'm_product.finish_good AS oil_type_fgs',
            't_daily_production_fractionation.oil_type_fgh AS oil_type_fgh_id',
            'm_product.by_product AS oil_type_fgh',
        ];

        return $query->select($baseSelect)->reorder()->orderBy('shift', 'asc');
    }

    /**
     * Get the main data for exports.
     */
    private function getMainData(string $tanggal, ?string $workCenter)
    {
        $user = Auth::user();
        $userRole = $user->roles;
        $query = LSDailyProductionFractionation::join('m_product', 't_daily_production_fractionation.oil_type_rm', '=', 'm_product.id')
            ->whereDate('t_daily_production_fractionation.posting_date', $tanggal);

        if ($workCenter)
            $query->where('t_daily_production_fractionation.work_center', $workCenter);

        $query->where('t_daily_production_fractionation.flag', 'T');

        // if ($userRole === "MGR" or $userRole === "MGR_PROD") {
        //     return $query->select('t_daily_production_fractionation.*')->orderBy('shift')->get();
        // } elseif ($userRole === "LEAD" or $userRole === "LEAD_PROD") {
        //     return $query->join('m_roles_shift_prepared', 't_daily_production_fractionation.shift', '=', 'm_roles_shift_prepared.shift_code')
        //         ->where('m_roles_shift_prepared.username', $user->username)->where('m_roles_shift_prepared.isactive', 'T')
        //         ->select('t_daily_production_fractionation.*')->orderBy('shift')->get();
        // }

        $baseSelect = [
            't_daily_production_fractionation.*',
            't_daily_production_fractionation.oil_type_rm AS oil_type_rm_id',
            'm_product.raw_material AS oil_type_prm',
            't_daily_production_fractionation.oil_type_fgs AS oil_type_fg_id',
            'm_product.finish_good AS oil_type_fgs',
            't_daily_production_fractionation.oil_type_fgh AS oil_type_fgh_id',
            'm_product.by_product AS oil_type_fgh',
        ];
        return $query->select($baseSelect)->get();
    }

    /**
     * Get the signatures (prepared_by) for the report.
     */
    private function getSignatures(string $tanggal, ?string $workCenter): array
    {
        $get = function ($shift) use ($tanggal, $workCenter) {
            $q = LSDailyProductionFractionation::whereDate('posting_date', $tanggal)
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
        $base = LSDailyProductionFractionation::whereDate('posting_date', $tanggal);
        if ($workCenter)
            $base->where('work_center', $workCenter);
        $first = (clone $base)->orderBy('revision_date')->first(['form_no', 'date_issued', 'revision_no', 'revision_date']);
        $last = (clone $base)->orderByDesc('revision_date')->first(['form_no', 'date_issued', 'revision_no', 'revision_date']);
        return [$first, $last];
    }

    /**
     * Get the overall approval status for the date.
     */
    private function getApprovalStatus(string $tanggal): array
    {
        $reports = LSDailyProductionFractionation::whereDate('posting_date', $tanggal)->where('flag', 'T')->get();
        $statusMessage = null;
        $canApproveReject = false;
        $user = Auth::user();
        $userRole = $user->roles;

        if ($reports->isEmpty()) {
            $statusMessage = "Tidak ada data pada tanggal $tanggal.";
        } else {
            if ($userRole === "LEAD_PROD" or $userRole === "LEAD") {
                $assignedShifts = MRolesShiftPrepared::where('username', $user->username)->where('isactive', 'T')->pluck('shift_code');
                if ($assignedShifts->isEmpty()) {
                    $statusMessage = "User tidak memiliki shift.";
                } else {
                    $reportsForUserShifts = LSDailyProductionFractionation::whereDate('posting_date', $tanggal)->whereIn('shift', $assignedShifts)->get();
                    $reportedShifts = $reportsForUserShifts->pluck('shift')->unique();
                    $allShiftsReported = $assignedShifts->diff($reportedShifts)->isEmpty();

                    if ($reportsForUserShifts->isEmpty()) {
                        $statusMessage = "No reports for your shifts yet.";
                    } elseif (!$allShiftsReported) {
                        $missingShifts = $assignedShifts->diff($reportedShifts)->implode(', ');
                        $statusMessage = "Waiting for reports from shift(s): {$missingShifts}.";
                    } elseif ($reportsForUserShifts->contains(fn($r) => !is_null($r->prepared_status))) {
                        $statusMessage = 'You have already prepared the reports.';
                    } else {
                        $canApproveReject = true;
                    }
                }
            } elseif ($userRole === "MGR_PROD" or $userRole === "MGR") {
                if ($reports->contains(fn($r) => is_null($r->prepared_status)))
                    $statusMessage = 'Belum dilakukan prepared oleh shift leader.';
                elseif ($reports->contains(fn($r) => $r->prepared_status === 'Rejected'))
                    $statusMessage = 'Data sudah direject oleh shift leader.';
                elseif ($reports->every(fn($r) => !is_null($r->checked_status)))
                    $statusMessage = 'Semua data sudah di-review oleh Anda.';
                elseif ($reports->every(fn($r) => $r->prepared_status === 'Approved'))
                    $canApproveReject = true;
                else
                    $statusMessage = 'Terdapat data yang tidak valid untuk diproses.';
            }
        }
        return ['canApproveReject' => $canApproveReject, 'statusMessage' => $statusMessage];
    }

    /**
     * Renders the layout preview for export.
     */
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

    private function getShiftSummaries(\Illuminate\Support\Collection $data)
    {
        // 1. Group by shift
        $groupedByShift = $data->groupBy('shift');


        // 2. Aggregate the data for each shift
        $shiftSummaries = $groupedByShift->map(function ($shiftReports, $shift) {
            $lastReport = $shiftReports->last();
            $item = $shiftReports->uu_item;
            $budgetRefQty = optional($lastReport)->uu_budget_ref_qty;
            $yieldPercent = $shiftReports->sum(fn($report) => (float) $report->uu_yield_percent);
            $uuFlowmeterAfter = $shiftReports->sum(fn($report) => (float) $report->uu_flowmeter_after);
            $uuFlowmeterBefore = $shiftReports->sum(fn($report) => (float) $report->uu_flowmeter_before);
            $uuFlowmeterTotal = $shiftReports->sum(fn($report) => (float) $report->uu_flowmeter_total);
            $uuListrik = $shiftReports->sum(fn($report) => (float) $report->uu_listrik);
            $uuAir = $shiftReports->sum(fn($report) => (float) $report->uu_air);

            return (object) [
                'shift' => $shift,
                'uu_item' => $item,
                'uu_budget_ref_qty' => $budgetRefQty,
                'uu_yield_percent' => $yieldPercent,
                'uu_flowmeter_before' => $uuFlowmeterBefore,
                'uu_flowmeter_after' => $uuFlowmeterAfter,
                'uu_flowmeter_total' => $uuFlowmeterTotal,
                // Remarks Summary (Concatenate all remarks for the shift)
                'remarks' => $shiftReports->pluck('remarks')->filter()->implode(PHP_EOL . '---' . PHP_EOL),
            ];
        })->sortBy('shift')->values(); // Sort by shift and reset keys

        return $shiftSummaries;
    }
}