<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

use App\Models\LSDailyStorageTankStatus;
use App\Models\LSDailyStorageTankAnalytical;

class RptDailyStorageTankAnalyticalController extends Controller
{
 
    public function index(Request $request)
    {
        $tanggal = $request->input('filter_tanggal', now()->toDateString());

        // 1. TANK VALID SAJA (T.01 – T.99)
        $tanks = LSDailyStorageTankStatus::where('tank_no', 'like', 'T.%')
            ->whereRaw("LENGTH(tank_no) <= 4")
            ->orderBy('tank_no')
            ->get();

        // 2. ANALYTICAL TERAKHIR <= TANGGAL (PER TANK)
        $analytical = LSDailyStorageTankAnalytical::whereDate('posting_date', '<=', $tanggal)
            ->orderBy('posting_date', 'desc')
            ->get()
            ->groupBy('tank_no');

        // 3. GABUNGKAN
        $data = $tanks->map(function ($tank) use ($analytical) {

            $last = $analytical->get($tank->tank_no)?->first();

            $tank->report_id        = $last->id ?? null;
            $tank->plant            = $last->plant ?? null;
            $tank->oil_type         = $last->oil_type ?? null;
            
            $tank->transaction_date = $last->posting_date ?? null;

            $tank->transaction_date = $last->posting_date ?? null;
            $tank->prepared_status = $last->prepared_status ?? null;
            $tank->approved_status = $last->approved_status ?? null;

            return $tank;
        });

        return view(
            'rpt_daily_storage_tank_analytical.index',
            compact('tanggal', 'data')
        );

    }

    public function approveReport($id)
    {
        $report = LSDailyStorageTankAnalytical::findOrFail($id);
        $role = Auth::user()->roles;

        if (in_array($role, ['LEAD', 'LEAD_QC'])) {
            $report->update([
                'prepared_status' => 'Approved',
                'prepared_status_remarks' => null,
                'prepared_date' => now(),
                'prepared_by' => auth()->user()->username ?? auth()->user()->name,
            ]);
        }

        if (in_array($role, ['MGR', 'MGR_PROD', 'ADM'])) {
            $report->update([
                'approved_status' => 'Approved',
                'approved_status_remarks' => null,
                'approved_date' => now(),
                'approved_by' => auth()->user()->username ?? auth()->user()->name,
            ]);
        }

        return back()->with('success', "Report {$report->id} berhasil di-approve.");
    }

  
    public function rejectReport(Request $request, $id)
    {
        $request->validate([
            'remark' => 'required|string|max:255'
        ]);

        $report = LSDailyStorageTankAnalytical::findOrFail($id);
        $role = Auth::user()->roles;

        if (in_array($role, ['LEAD', 'LEAD_QC'])) {
            $report->update([
                'prepared_status' => 'Rejected',
                'prepared_status_remarks' => $request->remark,
                'prepared_date' => now(),
                'prepared_by' => auth()->user()->username ?? auth()->user()->name,
            ]);
        }

        if (in_array($role, ['MGR', 'MGR_PROD', 'ADM'])) {
            $report->update([
                'approved_status' => 'Rejected',
                'approved_status_remarks' => $request->remark,
                'approved_date' => now(),
                'approved_by' => auth()->user()->username ?? auth()->user()->name,
            ]);
        }

        return back()->with('success', "Report {$report->id} berhasil di-reject.");
    }

    public function show($id)
    {
        $data = LSDailyStorageTankAnalytical::findOrFail($id);
        return view('rpt_daily_storage_tank_analytical.show', compact('data'));
    }

    
    public function exportLayoutPreview(Request $request)
    {
        return $this->renderPreview($request, 'rpt_daily_storage_tank_analytical.preview');
    }

    private function renderPreview(Request $request, string $view)
    {
        $tanggal = $request->input('filter_tanggal', now()->toDateString());

        // 1. Ambil tank VALID SAJA (BUANG T.401+)
        $tanks = LSDailyStorageTankStatus::where('tank_no', 'like', 'T.%')
            ->whereRaw("LENGTH(tank_no) <= 4") // T.01 – T.99
            ->orderBy('tank_no')
            ->get();

        // 2. Ambil ANALYTICAL TERAKHIR <= tanggal (per tank)
        $analytical = LSDailyStorageTankAnalytical::whereDate('posting_date', '<=', $tanggal)
            ->orderBy('posting_date', 'desc')
            ->get()
            ->groupBy('tank_no');

        // 3. Gabungkan (SAMA PERSIS DENGAN INDEX)
        $data = $tanks->map(function ($tank) use ($analytical) {

            $last = $analytical->get($tank->tank_no)?->first();

            $tank->oil_type         = $last->oil_type ?? null;
            $tank->analysis_date    = $last->posting_date ?? null;
            $tank->capacity         = $tank->capacity ?? null;

            // QC PARAMETER
            $tank->quantity         = $last->quantity ?? null;
            $tank->empty_space      = $last->empty_space ?? null;
            $tank->suhu             = $last->suhu ?? null;
            $tank->ffa              = $last->qp_ffa ?? null;
            $tank->moisture         = $last->qp_moisture ?? null;
            $tank->lov_r            = $last->qp_lovibond_color_r ?? null;
            $tank->lov_y            = $last->qp_lovibond_color_y ?? null;
            $tank->iv               = $last->qp_iv ?? null;
            $tank->pv               = $last->qp_pv ?? null;
            $tank->smp              = $last->qp_slip_melting_point ?? null;
            $tank->cloud            = $last->qp_cloud_point ?? null;
            $tank->anv              = $last->qp_anv ?? null;
            $tank->beta_carotene    = $last->qp_beta_carotene ?? null;
            $tank->p                = $last->qp_p ?? null;
            $tank->dobi             = $last->qp_dobi ?? null;
            $tank->totox            = $last->qp_totox ?? null;
            $tank->odor             = $last->qp_odor ?? null;

            return $tank;
        });

        // tanda tangan
        $sign = LSDailyStorageTankAnalytical::whereDate('posting_date', '<=', $tanggal)
            ->orderBy('posting_date', 'desc')
            ->first();

        return view($view, compact('data', 'tanggal', 'sign'));
    }


   
    public function exportPdf(Request $request)
    {
        $tanggal = $request->input('filter_tanggal', now()->toDateString());

        // 1. Ambil tank valid saja (T.01 – T.99)
        $tanks = LSDailyStorageTankStatus::where('tank_no', 'like', 'T.%')
            ->whereRaw("LENGTH(tank_no) <= 4")
            ->orderBy('tank_no')
            ->get();

        // 2. Ambil analytical <= tanggal, terbaru dulu
        $analytical = LSDailyStorageTankAnalytical::whereDate('posting_date', '<=', $tanggal)
            ->orderBy('posting_date', 'desc')
            ->get()
            ->groupBy('tank_no');

        // 3. Gabungkan (SAMA DENGAN INDEX)
        $rows = $tanks->map(function ($tank) use ($analytical) {
            $last = $analytical->get($tank->tank_no)?->first();

            return (object)[
                'tank_no' => $tank->tank_no,
                'oil_type' => $last->oil_type ?? null,
                'analysis_date' => $last->posting_date ?? null,
                'capacity' => $tank->capacity,
                'quantity' => $last->quantity ?? null,
                'empty_space' => $last->empty_space ?? null,
                'suhu' => $last->suhu ?? null,
                'ffa' => $last->qp_ffa ?? null,
                'moisture' => $last->qp_moisture ?? null,
                'r' => $last->qp_lovibond_color_r ?? null,
                'y' => $last->qp_lovibond_color_y ?? null,
                'iv' => $last->qp_iv ?? null,
                'pv' => $last->qp_pv ?? null,
                'smp' => $last->qp_slip_melting_point ?? null,
                'cloud' => $last->qp_cloud_point ?? null,
                'anv' => $last->qp_anv ?? null,
                'bcar' => $last->qp_beta_carotene ?? null,
                'p' => $last->qp_p ?? null,
                'dobi' => $last->qp_dobi ?? null,
                'totox' => $last->qp_totox ?? null,
                'odor' => $last->qp_odor ?? null,
            ];
        });

        $sign = LSDailyStorageTankAnalytical
            ::whereDate('posting_date', '<=', $tanggal)
            ->orderBy('posting_date', 'desc')
            ->first();

        $pdf = Pdf::loadView(
            'exports.report_daily_storage_tank_analytical_pdf',
            compact('tanggal', 'rows', 'sign')
        )->setPaper('a3', 'landscape');

        return $pdf->stream("daily_storage_tank_analytical_{$tanggal}.pdf");
    }

}
