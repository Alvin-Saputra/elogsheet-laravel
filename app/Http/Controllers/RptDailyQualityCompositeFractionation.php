<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\LSDailyQualityCompositeFractionation;
use Illuminate\Support\Facades\Auth;

class RptDailyQualityCompositeFractionation extends Controller
{

    private function getOperationalRange(string $tanggal)
    {
        $start = Carbon::parse($tanggal)->setTime(8, 0, 0);
        $end   = (clone $start)->addDay()->setTime(7, 59, 59);
        return [$start, $end];
    }


    private function getOperationalHours()
    {
        $hours = [];
        for ($i = 8; $i <= 23; $i++) $hours[] = sprintf('%02d:00:00', $i);
        for ($i = 0; $i <= 7; $i++)  $hours[] = sprintf('%02d:00:00', $i);
        return $hours;
    }


    private function fillMissingHours($data)
    {
        $hours = $this->getOperationalHours();

        return $data->groupBy('work_center')->map(function ($rows) use ($hours) {
            $rowsByTime = $rows->groupBy('time');
            $filled = collect();

            foreach ($hours as $hour) {
                if ($rowsByTime->has($hour)) {
                    foreach ($rowsByTime[$hour] as $row) {
                        $filled->push($row);
                    }
                } else {
                    // BARIS DUMMY (UNTUK TAMPILAN SAJA)
                    $filled->push((object)[
                        'time' => $hour,
                        'work_center' => $rows->first()->work_center ?? null,
                        'crystalizer' => null,

                        'rm_mni' => null,
                        'rm_iv' => null,
                        'rm_color_r' => null,
                        'rm_color_y' => null,
                        'rm_color_w' => null,
                        'rm_color_b' => null,

                        'fg_ffa' => null,
                        'fg_mni' => null,
                        'fg_iv' => null,
                        'fg_color_r' => null,
                        'fg_color_y' => null,
                        'fg_color_w' => null,
                        'fg_color_b' => null,
                        'fg_cp' => null,
                        'fg_clarity' => null,
                        'fg_to_tank' => null,

                        'bp_ffa' => null,
                        'bp_mni' => null,
                        'bp_iv' => null,
                        'bp_pv' => null,
                        'bp_color_r' => null,
                        'bp_color_y' => null,
                        'bp_color_w' => null,
                        'bp_color_b' => null,
                        'bp_to_tank' => null,

                        'remarks' => null,
                    ]);
                }
            }

            return $filled;
        });
    }

    public function index(Request $request)
    {
        $filterTanggal    = $request->input('filter_tanggal', now()->toDateString());
        $filterJam        = $request->input('filter_jam');
        $filterWorkCenter = $request->input('filter_work_center');

        [$start, $end] = $this->getOperationalRange($filterTanggal);

        $query = LSDailyQualityCompositeFractionation::whereBetween(
            'transaction_date',
            [$start, $end]
        );

        if ($filterJam) {
            $query->whereTime('time', $filterJam . ':00');
        }

        if ($filterWorkCenter) {
            $query->where('work_center', $filterWorkCenter);
        }

        $data = $query->orderByRaw("
            CASE
                WHEN time >= '08:00:00' THEN time
                ELSE ADDTIME(time,'24:00:00')
            END
        ")->get();

        $workCenters = LSDailyQualityCompositeFractionation::select('work_center')
            ->distinct()->get();

        return view('rpt_daily_quality_composite_fractionation.index', [
            'tanggal' => $filterTanggal,
            'data' => $data,
            'jam' => $filterJam,
            'workCenter' => $filterWorkCenter,
            'listWorkCenters' => $workCenters
        ]);
    }


    public function approveReport($id)
    {
        $report = LSDailyQualityCompositeFractionation::findOrFail($id);
        $role = auth()->user()->roles;

        if (in_array($role, ['LEAD', 'LEAD_QC'])) {
            $report->update([
                'prepared_status' => 'Approved',
                'prepared_by' => auth()->user()->username ?? auth()->user()->name,
                'prepared_date' => now(),
            ]);
        } elseif (in_array($role, ['MGR', 'MGR_PROD', 'ADM'])) {
            $report->update([
                'checked_status' => 'Approved',
                'checked_by' => auth()->user()->username ?? auth()->user()->name,
                'checked_date' => now(),
            ]);
        }

        return back()->with('success', 'Report approved.');
    }


    public function rejectReport(Request $request, $id)
    {
        $report = LSDailyQualityCompositeFractionation::findOrFail($id);
        $role = auth()->user()->roles;

        if (in_array($role, ['LEAD', 'LEAD_QC'])) {
            $report->update([
                'prepared_status' => 'Rejected',
                'prepared_by' => auth()->user()->username ?? auth()->user()->name,
                'prepared_date' => now(),
            ]);
        } elseif (in_array($role, ['MGR', 'MGR_PROD', 'ADM'])) {
            $report->update([
                'checked_status' => 'Rejected',
                'checked_by' => auth()->user()->username ?? auth()->user()->name,
                'checked_date' => now(),
            ]);
        }

        return back()->with('success', 'Report rejected.');
    }


    public function show($id)
    {
        $data = LSDailyQualityCompositeFractionation::findOrFail($id);
        return view('rpt_daily_quality_composite_fractionation.show', compact('data'));
    }


    public function exportLayoutPreview(Request $request)
    {
        $filterTanggal = $request->input('filter_tanggal', now()->toDateString());
        $filterWorkCenter = $request->input('filter_work_center');

        [$start, $end] = $this->getOperationalRange($filterTanggal);

        $data = LSDailyQualityCompositeFractionation::whereBetween(
            'transaction_date',
            [$start, $end]
        )
            ->when($filterWorkCenter, fn($q) => $q->where('work_center', $filterWorkCenter))
            ->get();

        $groupedData = $this->fillMissingHours($data);
        [$formInfoFirst, $formInfoLast] = $this->getFormInfo($filterTanggal);

        $sign = LSDailyQualityCompositeFractionation::whereBetween(
            'transaction_date',
            [$start, $end]
        )
            ->when($filterWorkCenter, fn($q) => $q->where('work_center', $filterWorkCenter))
            ->where(function ($q) {
                $q->whereNotNull('prepared_by')
                    ->orWhereNotNull('checked_by');
            })
            ->orderByDesc('transaction_date')
            ->first();

        return view(
            'rpt_daily_quality_composite_fractionation.preview',
            compact(
                'groupedData',
                'filterTanggal',
                'filterWorkCenter',
                'formInfoFirst',
                'formInfoLast',
                'sign'
            )
        );
    }


    public function exportPdf(Request $request)
    {
        $filterTanggal = $request->input('filter_tanggal', now()->toDateString());
        $filterWorkCenter = $request->input('filter_work_center');

        [$start, $end] = $this->getOperationalRange($filterTanggal);

        $data = LSDailyQualityCompositeFractionation::whereBetween(
            'transaction_date',
            [$start, $end]
        )
            ->when($filterWorkCenter, fn($q) => $q->where('work_center', $filterWorkCenter))
            ->get();

        $groupedData = $this->fillMissingHours($data);
        [$formInfoFirst, $formInfoLast] = $this->getFormInfo($filterTanggal);

        // 🔥 SIGNATURE DATA ASLI
        $sign = LSDailyQualityCompositeFractionation::whereBetween(
            'transaction_date',
            [$start, $end]
        )
            ->when($filterWorkCenter, fn($q) => $q->where('work_center', $filterWorkCenter))
            ->where(function ($q) {
                $q->whereNotNull('prepared_by')
                    ->orWhereNotNull('checked_by');
            })
            ->orderByDesc('transaction_date')
            ->first();

        $pdf = Pdf::loadView(
            'exports.report_daily_quality_composite_fractionation_pdf',
            compact(
                'groupedData',
                'filterTanggal',
                'filterWorkCenter',
                'formInfoFirst',
                'formInfoLast',
                'sign'
            )
        )->setPaper('a3', 'landscape');

        return $pdf->stream("daily_quality_composite_fractionation_{$filterTanggal}.pdf");
    }

    private function getFormInfo(string $tanggal)
    {
        [$start, $end] = $this->getOperationalRange($tanggal);

        $base = LSDailyQualityCompositeFractionation::whereBetween(
            'transaction_date',
            [$start, $end]
        );

        $first = (clone $base)->orderBy('transaction_date')->first([
            'form_no',
            'date_issued',
            'revision_no',
            'revision_date'
        ]);

        $last = (clone $base)->orderByDesc('revision_date')->first([
            'form_no',
            'date_issued',
            'revision_no',
            'revision_date'
        ]);

        return [$first, $last];
    }

    public function bulkApprove(Request $request)
    {
        $userRole = Auth::user()->roles;
        $count = 0;

        // 1. Ambil tanggal dasar (default hari ini)
        $tanggalInput = $request->input('tanggal') ?? now()->format('Y-m-d');

        // 2. Buat range: Tanggal Input s/d Tanggal Input + 1 Hari
        $startDate = Carbon::parse($tanggalInput)->startOfDay();
        $endDate   = Carbon::parse($tanggalInput)->addDay()->endOfDay();

        if ($userRole === "LEAD" || $userRole === "LEAD_QC") {
            // Gunakan whereBetween untuk mengambil range 2 hari tersebut
            $reports = LSDailyQualityCompositeFractionation::whereNull('prepared_status')
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->get();

            foreach ($reports as $report) {
                $report->update([
                    'prepared_status' => 'Approved',
                    'prepared_status_remarks' => null,
                    'prepared_date' => now(),
                    'prepared_by' => auth()->user()->username ?? auth()->user()->name
                ]);
                $count++;
            }
        } elseif ($userRole === "MGR" || $userRole === "MGR_QC" || $userRole === "ADM") {
            $reports = LSDailyQualityCompositeFractionation::where('prepared_status', 'Approved')
                ->whereNull('checked_status')
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->get();

            foreach ($reports as $report) {
                $report->update([
                    'checked_status' => 'Approved',
                    'checked_status_remarks' => null,
                    'checked_date' => now(),
                    'checked_by' => auth()->user()->username ?? auth()->user()->name
                ]);
                $count++;
            }
        }

        return back()->with('success', "Total {$count} tiket (Range: {$startDate->toDateString()} s/d {$endDate->toDateString()}) berhasil di-approve.");
    }

    public function bulkReject(Request $request)
    {
        $request->validate(['remark' => 'nullable|string|max:255']);
        $userRole = Auth::user()->roles;
        $count = 0;

        // 1. Tentukan tanggal dasar
        $tanggalInput = $request->input('tanggal') ?? now()->format('Y-m-d');

        // 2. Buat range: Hari ini 00:00:00 s/d Besok 23:59:59
        $startDate = Carbon::parse($tanggalInput)->startOfDay();
        $endDate   = Carbon::parse($tanggalInput)->addDay()->endOfDay();

        if ($userRole === "LEAD" || $userRole === "LEAD_QC") {
            // Menggunakan whereBetween untuk mencakup 2 hari
            $reports = LSDailyQualityCompositeFractionation::whereNull('prepared_status')
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->get();

            foreach ($reports as $report) {
                $report->update([
                    'prepared_status' => 'Rejected',
                    'prepared_status_remarks' => $request->remark,
                    'prepared_date' => now(),
                    'prepared_by' => auth()->user()->username ?? auth()->user()->name
                ]);
                $count++;
            }
        } elseif ($userRole === "MGR" || $userRole === "MGR_QC" || $userRole === "ADM") {
            $reports = LSDailyQualityCompositeFractionation::where('prepared_status', 'Approved')
                ->whereNull('checked_status')
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->get();

            foreach ($reports as $report) {
                $report->update([
                    'checked_status' => 'Rejected',
                    'checked_status_remarks' => $request->remark,
                    'checked_date' => now(),
                    'checked_by' => auth()->user()->username ?? auth()->user()->name
                ]);
                $count++;
            }
        }

        return back()->with('success', "Total {$count} tiket (Range: {$startDate->toDateString()} s/d {$endDate->toDateString()}) berhasil di-reject.");
    }
}
