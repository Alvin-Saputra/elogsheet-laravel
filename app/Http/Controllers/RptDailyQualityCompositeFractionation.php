<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\LSDailyQualityCompositeFractionation;


class RptDailyQualityCompositeFractionation extends Controller
{
    public function index(Request $request)
    {
        $filterTanggal = $request->input('filter_tanggal', now()->toDateString());
        $filterJam = $request->input('filter_jam');
        $filterWorkCenter = $request->input('filter_work_center');


        $query = LSDailyQualityCompositeFractionation::query();

        if ($filterTanggal) {
            $query->whereDate('transaction_date', $filterTanggal);
        }

        if ($filterJam) {
            $query->whereTime('time', $filterJam . ':00');
        }

        if ($filterWorkCenter) {
            $query->where('work_center', $filterWorkCenter);
        }

        $data = $query->get();
        $workCenters = LSDailyQualityCompositeFractionation::distinct()
            ->select('work_center')
            ->get()
            ->map(function ($wc) {
                $kapasitas = [
                    'FRAC-01' => '500 MT',
                    'FRAC-02' => '400 MT',
                ];

                $wc->label = $wc->work_center . ' ' . ($kapasitas[$wc->work_center] ?? '');
                return $wc;
            });
        return view('rpt_daily_quality_composite_fractionation.index', [
            'tanggal' => $filterTanggal,
            'data' => $data,
            'jam' => $filterJam,
            'workCenter' => $filterWorkCenter,
            'listWorkCenters' =>  $workCenters
        ]);
    }

    public function approveReport($id)
    {
        $report = LSDailyQualityCompositeFractionation::findOrFail($id);
        $userRole = Auth::user()->roles;

        if ($userRole === "LEAD" or $userRole === "LEAD_QC") {
            $report->update(['prepared_status' => 'Approved', 'prepared_status_remarks' => null, 'prepared_date' => now(), 'prepared_by' => auth()->user()->username ?? auth()->user()->name]);
        } elseif ($userRole === "MGR" or $userRole === "MGR_PROD" or $userRole === "ADM") {
            $report->update(['checked_status' => 'Approved', 'checked_status_remarks' => null, 'checked_date' => now(), 'checked_by' => auth()->user()->username ?? auth()->user()->name]);
        }
        return back()->with('success', "Tiket {$report->id} berhasil di-approve.");
    }

    public function rejectReport(Request $request, $id)
    {
        $request->validate(['remark' => 'nullable|string|max:255']);
        $report = LSDailyQualityCompositeFractionation::findOrFail($id);
        $userRole = Auth::user()->roles;

        if ($userRole === "LEAD" or $userRole === "LEAD_QC") {
            $report->update(['prepared_status' => 'Rejected', 'prepared_status_remarks' => $request->remark, 'prepared_date' => now(), 'prepared_by' => auth()->user()->username ?? auth()->user()->name]);
        } elseif ($userRole === "MGR" or $userRole === "MGR_PROD" or $userRole === "ADM") {
            $report->update(['checked_status' => 'Rejected', 'checked_status_remarks' => $request->remark, 'checked_date' => now(), 'checked_by' => auth()->user()->username ?? auth()->user()->name]);
        }
        return back()->with('success', "Tiket {$report->id} berhasil di-reject.");
    }

    public function show($id)
    {
        $data = LSDailyQualityCompositeFractionation::findOrFail($id);
        // kalau tidak ada, otomatis throw 404

        return view('rpt_daily_quality_composite_fractionation.show', [
            'data' => $data
        ]);
    }

    public function exportLayoutPreview(Request $request)
    {
        // return view('rpt_logsheetDryFra.preview');
        return $this->renderPreview($request, 'rpt_daily_quality_composite_fractionation.preview');
    }

    private function renderPreview(Request $request, string $view)
    {
        // 1 Ambil tanggal dari request, default hari ini
        $filterTanggal = $request->input('filter_tanggal', now()->toDateString());
        $filterJam = $request->input('filter_jam');
        $filterWorkCenter = $request->input('filter_work_center');

        $query = LSDailyQualityCompositeFractionation::query();

        if ($filterTanggal) {
            $query->whereDate('transaction_date', $filterTanggal);
        }

        if ($filterJam) {
            $query->whereTime('time', $filterJam . ':00');
        }

        if ($filterWorkCenter) {
            $query->where('work_center', $filterWorkCenter);
        }

        $data = $query->get();

        // 3 Ambil form info (first & last revision)
        [$formInfoFirst, $formInfoLast] = $this->getFormInfo($filterTanggal, null);

        // 4 Ambil signature jika perlu (opsional, bisa dilepas jika tidak ada fungsi getSignatures)
        // $signatures = $this->getSignatures($tanggal, null);

        // 5 Ambil satu record pertama untuk contoh tanda tangan / info
        $sign = $data->first();

        $groupedData = empty($filterWorkCenter) ? $data->groupBy('work_center') : collect();

        // 6 Render view dengan semua data
        return view($view, compact('data', 'filterTanggal', 'filterWorkCenter', 'groupedData', 'sign', 'formInfoFirst', 'formInfoLast'));
    }

    private function getFormInfo(string $tanggal)
    {
        $base = LSDailyQualityCompositeFractionation::whereDate('transaction_date', $tanggal);

        $first = (clone $base)->orderBy('revision_date')->first(['form_no', 'date_issued', 'revision_no', 'revision_date']);
        $last = (clone $base)->orderByDesc('revision_date')->first(['form_no', 'date_issued', 'revision_no', 'revision_date']);
        return [$first, $last];
    }

    public function exportPdf(Request $request)
    {
        // 1 Ambil tanggal dari request, default hari ini
        $filterTanggal = $request->input('filter_tanggal', now()->toDateString());
        $filterJam = $request->input('filter_jam');
        $filterWorkCenter = $request->input('filter_work_center');

        $query = LSDailyQualityCompositeFractionation::query();

        if ($filterTanggal) {
            $query->whereDate('transaction_date', $filterTanggal);
        }

        if ($filterJam) {
            $query->whereTime('time', $filterJam . ':00');
        }

        if ($filterWorkCenter) {
            $query->where('work_center', $filterWorkCenter);
        }

        $data = $query->get();


        [$formInfoFirst, $formInfoLast] = $this->getFormInfo($filterTanggal, null);


        $sign = $data->first();

        $groupedData = empty($filterWorkCenter) ? $data->groupBy('work_center') : collect();

        $pdf = Pdf::loadView('exports.report_daily_quality_composite_fractionation_pdf', compact('data', 'filterTanggal', 'filterWorkCenter', 'groupedData', 'sign', 'formInfoFirst', 'formInfoLast'))
            ->setPaper('a3', 'landscape');

        return $pdf->stream("daily_quality_composite_fractionation_report_{$filterTanggal}.pdf");
    }
}
