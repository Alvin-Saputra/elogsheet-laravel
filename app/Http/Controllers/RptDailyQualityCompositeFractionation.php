<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        $workCenters = LSDailyQualityCompositeFractionation::select('work_center')->distinct()->get();

        return view('rpt_daily_quality_composite_fractionation.index', [
            'tanggal' => $filterTanggal,
            'data' => $data,
            'workCenters' =>  $workCenters
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
            $report->update(['approved_status' => 'Rejected', 'approved_status_remarks' => $request->remark, 'approved_date' => now(), 'approved_by' => auth()->user()->username ?? auth()->user()->name]);
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
}
