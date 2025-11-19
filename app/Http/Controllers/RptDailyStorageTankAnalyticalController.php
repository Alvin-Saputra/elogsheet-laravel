<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\LSDailyStorageTankAnalytical;

class RptDailyStorageTankAnalyticalController extends Controller
{
    public function index(Request $request)
    {
        // 1️⃣ Ambil input tanggal dari user, default = hari ini
        $tanggal = $request->input('filter_tanggal', now()->toDateString());

        // 2️⃣ Query sederhana ke 1 tabel (tanpa join)
        $data = LSDailyStorageTankAnalytical::whereDate('posting_date', $tanggal)
            ->get();

        // 3️⃣ Kirim hasil query ke view
        return view('rpt_daily_storage_tank_analytical.index', [
            'tanggal' => $tanggal,
            'data' => $data
        ]);
    }

    public function approveReport($id)
    {
        $report = LSDailyStorageTankAnalytical::findOrFail($id);
        $userRole = Auth::user()->roles;

        if ($userRole === "LEAD" or $userRole === "LEAD_QC") {
            $report->update(['prepared_status' => 'Approved', 'prepared_status_remarks' => null, 'prepared_date' => now(), 'prepared_by' => auth()->user()->username ?? auth()->user()->name]);
        } elseif ($userRole === "MGR" or $userRole === "MGR_PROD" or $userRole === "ADM") {
            $report->update(['approved_status' => 'Approved', 'approved_status_remarks' => null, 'approved_date' => now(), 'approved_by' => auth()->user()->username ?? auth()->user()->name]);
        }
        return back()->with('success', "Tiket {$report->id} berhasil di-approve.");
    }


    public function rejectReport(Request $request, $id)
    {
        $request->validate(['remark' => 'nullable|string|max:255']);
        $report = LSDailyStorageTankAnalytical::findOrFail($id);
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
        $data = LSDailyStorageTankAnalytical::findOrFail($id);
        // kalau tidak ada, otomatis throw 404

        return view('rpt_daily_storage_tank_analytical.show', [
            'data' => $data
        ]);
    }

    public function exportLayoutPreview(Request $request)
    {
        // return view('rpt_logsheetDryFra.preview');
        return $this->renderPreview($request, 'rpt_daily_storage_tank_analytical.preview');
    }

    private function getFormInfo(string $tanggal)
    {
        $base = LSDailyStorageTankAnalytical::whereDate('posting_date', $tanggal);

        $first = (clone $base)->orderBy('revision_date')->first(['form_no', 'date_issued', 'revision_no', 'revision_date']);
        $last = (clone $base)->orderByDesc('revision_date')->first(['form_no', 'date_issued', 'revision_no', 'revision_date']);
        return [$first, $last];
    }

    private function getSignatures(string $tanggal): array
    {
        $baseQuery = LSDailyStorageTankAnalytical::whereDate('posting_date', $tanggal)->where('flag', 'T');

        $prepared = (clone $baseQuery)->where('prepared_status', 'Approved')->orderByDesc('prepared_date')->first();

        $approved = (clone $baseQuery)->where('approved_status', 'Approved')->orderByDesc('approved_date')->first();

        return [
            'leader_shift' => $prepared ? ['name' => $prepared->prepared_by, 'date' => $prepared->prepared_date] : null,
            'supervisor' => $approved ? ['name' => $approved->approved_by, 'date' => $approved->approved_date] : null,
        ];
    }

    private function renderPreview(Request $request, string $view)
    {
        // 1 Ambil tanggal dari request, default hari ini
        $tanggal = $request->input('filter_tanggal', now()->toDateString());

        // 2 Ambil data berdasarkan tanggal
        $data = LSDailyStorageTankAnalytical::whereDate('posting_date', $tanggal)->get();

        // 3 Ambil form info (first & last revision)
        [$formInfoFirst, $formInfoLast] = $this->getFormInfo($tanggal, null);

        // 4 Ambil signature jika perlu (opsional, bisa dilepas jika tidak ada fungsi getSignatures)
        // $signatures = $this->getSignatures($tanggal, null);

        // 5 Ambil satu record pertama untuk contoh tanda tangan / info
        $sign = $data->first();

        // 6 Render view dengan semua data
        return view($view, compact('data', 'tanggal', 'sign', 'formInfoFirst', 'formInfoLast'));
    }

    public function exportPdf(Request $request)
    {
        $tanggal = $request->input('filter_tanggal', now()->toDateString());
        $data = LSDailyStorageTankAnalytical::whereDate('posting_date', $tanggal)->get();

        [$formInfoFirst, $formInfoLast] = $this->getFormInfo($tanggal, null);
        // $signatures = $this->getSignatures($tanggal, null);

        $sign = $data->first();

        $pdf = Pdf::loadView('exports.report_daily_storage_tank_analytical_pdf', compact('data', 'tanggal', 'sign', 'formInfoFirst', 'formInfoLast'))
            ->setPaper('a3', 'landscape');

        return $pdf->stream("dry_fractionation_report_{$tanggal}.pdf");
    }
}
