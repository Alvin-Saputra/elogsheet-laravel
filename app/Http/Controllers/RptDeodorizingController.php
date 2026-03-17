<?php

namespace App\Http\Controllers;

use App\Exports\LSDeodorizingExport;
use App\Models\LSDeodorizingFiltration;
use App\Models\MRolesShiftPrepared;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;

class RptDeodorizingController extends Controller
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

        $shiftStatuses = $this->getShiftStatuses($tanggal);
        $refineryMachines = LSDeodorizingFiltration::select('refinery_machine')->distinct()->get();
        $signatures = $this->getSignatures($tanggal, $refineryMachines);
        $approvalStatus = $this->getApprovalStatus($tanggal);

        return view('rpt_deodorizing.index', compact('reports', 'shiftStatuses', 'tanggal', 'refineryMachines', 'signatures') + $approvalStatus);
    }


    /**
     * SHOW detail report
     */
    public function show($id)
    {
        // $report = LSDeodorizingFiltration::findOrFail($id);
        $baseSelect = [
            't_deodorizing_filtration.*',
            't_deodorizing_filtration.oil_type AS oil_type_id',
            'm_product.raw_material AS oil_type',
            't_deodorizing_filtration.oil_type_fg AS oil_type_fg_id',
            'm_product.finish_good AS oil_type_fg',
            't_deodorizing_filtration.oil_type_bp AS oil_type_bp_id',
            'm_product.by_product AS oil_type_bp',
        ];
        $report = LSDeodorizingFiltration::join('m_product', 't_deodorizing_filtration.oil_type', '=', 'm_product.id')
            ->select($baseSelect)
            ->where('t_deodorizing_filtration.id', $id)
            ->firstOrFail();


        return view('rpt_deodorizing.show', compact('report'));
    }

    /**
     * EXPORT Excel
     */
    public function exportExcel(Request $request)
    {
        $tanggal = $request->input('filter_tanggal', Carbon::today()->format('Y-m-d'));
        $filename = 'logsheet_deodorizing_filtration_' . Carbon::parse($tanggal)->format('Y_m_d') . '.xlsx';

        return Excel::download(new LSDeodorizingExport($tanggal), $filename);
    }

    /**
     * EXPORT Layout Preview
     */
    public function exportLayoutPreview(Request $request)
    {
        return $this->renderPreview($request, 'rpt_deodorizing.preview');
    }

    /**
     * EXPORT PDF
     */
    public function exportPdf(Request $request)
    {
        return $this->renderPdf($request, 'exports.report_deodorizing_layout_pdf');
    }

    /**
     * APPROVE all by date
     */
    public function approveDate(Request $request)
    {
        $date = $request->posting_date;
        $user = Auth::user();
        $role = $user->roles;

        $reports = LSDeodorizingFiltration::whereDate('posting_date', $date)
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

            LSDeodorizingFiltration::whereDate('posting_date', $date)
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

            LSDeodorizingFiltration::whereDate('posting_date', $date)->update([
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

            LSDeodorizingFiltration::whereDate('posting_date', $date)
                ->whereIn('shift', $assignedShifts)
                ->update(['prepared_status' => 'Rejected', 'prepared_status_remarks' => $request->remark, 'prepared_date' => now(), 'prepared_by' => $user->username ?? $user->name]);

            $message = "All reports for your assigned shifts on {$date} have been rejected.";
        } elseif ($role === 'MGR_PROD' || $role === 'MGR') {
            LSDeodorizingFiltration::whereDate('posting_date', $date)
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
        $report = LSDeodorizingFiltration::findOrFail($id);
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
        $report = LSDeodorizingFiltration::findOrFail($id);
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

    private function buildBaseQuery(Request $request, string $tanggal)
    {
        $user = Auth::user();
        $userRole = $user->roles;
        // $query = LSDeodorizingFiltration::query()->whereDate('posting_date', $tanggal);

        $query = LSDeodorizingFiltration::join('m_product', 't_deodorizing_filtration.oil_type', '=', 'm_product.id')
            ->whereDate('t_deodorizing_filtration.posting_date', $tanggal);

        if ($request->filled('filter_jam')) {
            $query->where('t_deodorizing_filtration.time', $request->filter_jam);
        }
        if ($request->filled('filter_refinery_machine')) {
            $query->where('t_deodorizing_filtration.refinery_machine', $request->filter_refinery_machine);
        }

        $query->where('t_deodorizing_filtration.flag', 'T');

        // if ($userRole === "LEAD" || $userRole === "LEAD_PROD") {
        //     $query->join('m_roles_shift_prepared', 't_deodorizing_filtration.shift', '=', 'm_roles_shift_prepared.shift_code')
        //         ->where('m_roles_shift_prepared.username', $user->username)->where('m_roles_shift_prepared.isactive', 'T')
        //         ->select('t_deodorizing_filtration.*');
        // }
        $baseSelect = [
            't_deodorizing_filtration.*',
            't_deodorizing_filtration.oil_type AS oil_type_id',
            'm_product.raw_material AS oil_type',
            't_deodorizing_filtration.oil_type_fg AS oil_type_fg_id',
            'm_product.finish_good AS oil_type_fg',
            't_deodorizing_filtration.oil_type_bp AS oil_type_bp_id',
            'm_product.by_product AS oil_type_bp',
        ];

        return $query
            ->select($baseSelect)
            ->orderBy('t_deodorizing_filtration.shift', 'asc')->orderBy('time', 'asc');
    }

    private function getShiftStatuses(string $tanggal): array
    {
        return ['shift1' => $this->checkShiftStatus($tanggal, '08:00:00', '15:59:59'), 'shift2' => $this->checkShiftStatus($tanggal, '16:00:00', '23:59:59'), 'shift3' => $this->checkShiftStatus($tanggal, '00:00:00', '07:59:59')];
    }

    // private function renderPreview(Request $request, string $view)
    // {
    //     $tanggal = $request->input('filter_tanggal', now()->toDateString());
    //     $refineryMachine = $request->input('filter_refinery_machine');
    //     $data = $this->getMainData($tanggal, $refineryMachine);
    //     [$formInfoFirst, $formInfoLast] = $this->getFormInfo($tanggal, $refineryMachine);
    //     $groupedData = empty($refineryMachine) ? $data->groupBy('refinery_machine') : collect();
    //     $signatures = $this->getSignatures($tanggal, $refineryMachine);
    //     $sign = $data->first();
    //     return view($view, compact('data', 'groupedData', 'tanggal', 'refineryMachine', 'signatures', 'sign', 'formInfoFirst', 'formInfoLast'));
    // }

    private function renderPreview(Request $request, string $view)
    {
        $tanggal = $request->input('filter_tanggal', now()->toDateString());
        $refineryMachine = $request->input('filter_refinery_machine');

        // Ambil Data Mentah
        $dataRaw = $this->getMainData($tanggal, $refineryMachine);
        [$formInfoFirst, $formInfoLast] = $this->getFormInfo($tanggal, $refineryMachine);

        // Proses Filling Hours (Logika Baru)
        $groupedData = $this->fillMissingHours($dataRaw, $tanggal);

        $signatures = $this->getSignatures($tanggal, $refineryMachine);
        $sign = $dataRaw->first();

        // Pastikan view rpt_deodorizing.preview sudah menggunakan looping dari $groupedData
        return view($view, compact(
            'groupedData',
            'tanggal',
            'refineryMachine',
            'signatures',
            'sign',
            'formInfoFirst',
            'formInfoLast'
        ));
    }

    // private function renderPdf(Request $request, string $view)
    // {
    //     $tanggal = $request->input('filter_tanggal', now()->toDateString());
    //     $refineryMachine = $request->input('filter_refinery_machine');
    //     $data = $this->getMainData($tanggal, $refineryMachine);
    //     [$formInfoFirst, $formInfoLast] = $this->getFormInfo($tanggal, $refineryMachine);
    //     $groupedData = empty($refineryMachine) ? $data->groupBy('refinery_machine') : collect();
    //     $signatures = $this->getSignatures($tanggal, $refineryMachine);
    //     $pdf = Pdf::loadView($view, compact('data', 'groupedData', 'tanggal', 'refineryMachine', 'formInfoFirst', 'formInfoLast', 'signatures'))->setPaper('a3', 'landscape');
    //     return $pdf->stream("deodorizing_report_{$tanggal}.pdf");
    // }

    private function renderPdf(Request $request, string $view)
    {
        $tanggal = $request->input('filter_tanggal', now()->toDateString());
        $refineryMachine = $request->input('filter_refinery_machine');

        // Ambil Data Mentah
        $dataRaw = $this->getMainData($tanggal, $refineryMachine);
        [$formInfoFirst, $formInfoLast] = $this->getFormInfo($tanggal, $refineryMachine);

        // Proses Filling Hours
        $groupedData = $this->fillMissingHours($dataRaw, $tanggal);

        $signatures = $this->getSignatures($tanggal, $refineryMachine);

        $pdf = Pdf::loadView($view, compact(
            'groupedData',
            'tanggal',
            'refineryMachine',
            'formInfoFirst',
            'formInfoLast',
            'signatures'
        ))->setPaper('a3', 'landscape');

        return $pdf->stream("deodorizing_report_{$tanggal}.pdf");
    }

    private function getMainData(string $tanggal, ?string $refineryMachine)
    {
        $user = Auth::user();
        $userRole = $user->roles;
        // $query = LSDeodorizingFiltration::whereDate('posting_date', $tanggal);
        $query = LSDeodorizingFiltration::join('m_product', 't_deodorizing_filtration.oil_type', '=', 'm_product.id')
            ->whereDate('t_deodorizing_filtration.posting_date', $tanggal);


        if ($refineryMachine)
            $query->where('t_deodorizing_filtration.refinery_machine', $refineryMachine);

        $query->where('t_deodorizing_filtration.flag', 'T');
        // $orderLogic = "CASE WHEN time BETWEEN '08:00:00' AND '15:59:59' THEN 1 WHEN time BETWEEN '16:00:00' AND '23:59:59' THEN 2 WHEN time BETWEEN '00:00:00' AND '07:59:59' THEN 3 ELSE 4 END";

        // if ($userRole === "MGR" or $userRole === "MGR_PROD") {
        //     return $query->select('t_deodorizing_filtration.*')->orderByRaw($orderLogic)->orderBy('time')->get();
        // } elseif ($userRole === "LEAD" or $userRole === "LEAD_PROD") {
        //     return $query->join('m_roles_shift_prepared', 't_deodorizing_filtration.shift', '=', 'm_roles_shift_prepared.shift_code')
        //         ->where('m_roles_shift_prepared.username', $user->username)->where('m_roles_shift_prepared.isactive', 'T')
        //         ->select('t_deodorizing_filtration.*')->orderbyRaw($orderLogic)->orderby('time')->get();
        // }

        $baseSelect = [
            't_deodorizing_filtration.*',
            't_deodorizing_filtration.oil_type AS oil_type_id',
            'm_product.raw_material AS oil_type',
            't_deodorizing_filtration.oil_type_fg AS oil_type_fg_id',
            'm_product.finish_good AS oil_type_fg',
            't_deodorizing_filtration.oil_type_bp AS oil_type_bp_id',
            'm_product.by_product AS oil_type_bp',
        ];

        return $query->select($baseSelect)->orderBy('t_deodorizing_filtration.time')->get();
    }

    private function getSignatures(string $tanggal, ?string $refineryMachine): array
    {
        $get = function ($start, $end) use ($tanggal, $refineryMachine) {
            $q = LSDeodorizingFiltration::whereDate('posting_date', $tanggal)->whereBetween('time', [$start, $end])->where('prepared_status', 'Approved');
            if ($refineryMachine)
                $q->where('refinery_machine', $refineryMachine);
            $row = $q->orderByDesc('time')->first(['prepared_by as name', 'prepared_date as date']);
            return $row ? ['name' => $row->name, 'date' => $row->date] : null;
        };
        return ['shift1' => $get('08:00:00', '15:59:59'), 'shift2' => $get('16:00:00', '23:59:59'), 'shift3' => $get('00:00:00', '07:59:59')];
    }

    private function getFormInfo(string $tanggal, ?string $refineryMachine): array
    {
        $base = LSDeodorizingFiltration::whereDate('posting_date', $tanggal);
        if ($refineryMachine)
            $base->where('refinery_machine', $refineryMachine);
        $first = (clone $base)->orderBy('revision_date')->first(['form_no', 'date_issued', 'revision_no', 'revision_date']);
        $last = (clone $base)->orderByDesc('revision_date')->first(['form_no', 'date_issued', 'revision_no', 'revision_date']);
        return [$first, $last];
    }

    private function checkShiftStatus(string $tanggal, string $start, string $end): string
    {
        $q = LSDeodorizingFiltration::whereDate('posting_date', $tanggal)->whereBetween('time', [$start, $end]);
        if (!$q->exists())
            return 'Belum Ada Transaksi';
        $pending = (clone $q)->where(fn($sub) => $sub->whereNull('checked_status')->orWhere('checked_status', '!=', 'Approved'))->exists();
        return $pending ? 'Belum Selesai' : 'Approved Semua';
    }

    private function getApprovalStatus(string $tanggal): array
    {
        $reports = LSDeodorizingFiltration::whereDate('posting_date', $tanggal)->where('flag', 'T')->get();
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
                    $reportsForUserShifts = LSDeodorizingFiltration::whereDate('posting_date', $tanggal)->whereIn('shift', $assignedShifts)->get();
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

    private function getOperationalHours()
    {
        $hours = [];
        // Shift 1 & 2 (Hari H)
        for ($i = 8; $i <= 23; $i++) $hours[] = sprintf('%02d:00:00', $i);
        // Shift 3 (Hari H+1)
        for ($i = 0; $i <= 7; $i++)  $hours[] = sprintf('%02d:00:00', $i);

        return $hours;
    }

    private function fillMissingHours($data, $tanggal)
    {
        $hours = $this->getOperationalHours();

        // Kita grouping berdasarkan refinery_machine agar setiap mesin punya tabel jam lengkap
        return $data->groupBy('refinery_machine')->map(function ($rows, $rm) use ($hours, $tanggal) {
            // Indexing data berdasarkan string waktu (misal '08:00:00')
            $rowsByTime = $rows->keyBy(function ($item) {
                return \Carbon\Carbon::parse($item->time)->format('H:i:s');
            });

            $filled = collect();

            foreach ($hours as $hour) {
                if ($rowsByTime->has($hour)) {
                    // Jika data ada di database, pakai data itu
                    $filled->push($rowsByTime->get($hour));
                } else {
                    // Jika tidak ada, buat dummy object
                    $isNextDay = (intval(substr($hour, 0, 2)) < 8);
                    $dateObj = \Carbon\Carbon::parse($tanggal . ' ' . $hour);
                    if ($isNextDay) {
                        $dateObj->addDay();
                    }

                    // Buat Object Dummy
                    $dummy = new \stdClass();
                    $dummy->time = $dateObj; // Carbon Object
                    $dummy->refinery_machine = $rm;
                    $dummy->oil_type = null;
                    $dummy->remarks = null;

                    $filled->push($dummy);
                }
            }

            return $filled;
        });
    }
}
