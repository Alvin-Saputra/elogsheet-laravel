<?php

namespace App\Http\Controllers;

use App\Models\LSFormTransferHeader;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RptFormTransferController extends Controller
{
    /**
     * INDEX - List view with date filters
     */
    public function index(Request $request)
    {
        $tanggal = $request->input('filter_tanggal', Carbon::today()->format('Y-m-d'));
        $plantCode = session('plant_code');

        $query = LSFormTransferHeader::query()->whereDate('transaction_date', $tanggal);

        if (!empty($plantCode)) {
            $query->where('plant', $plantCode);
        }

        $transfers = $query->orderBy('transaction_date', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('rpt_form_transfer.index', compact('transfers', 'tanggal'));
    }

    /**
     * SHOW / PREVIEW / EXPORT - Single record
     */
    public function getById(Request $request, $id)
    {
        $transfer = LSFormTransferHeader::with('details')->findOrFail($id);
        $intention = $request->query('intention');

        return match ($intention) {
            'show' => view('rpt_form_transfer.show', compact('transfer')),
            'preview' => view('rpt_form_transfer.preview_layout', compact('transfer')),
            'export' => (function () use ($transfer) {
                $pdf = Pdf::loadView('exports.form_transfer_pdf', compact('transfer'))
                    ->setPaper('a4', 'landscape');
                $fileName = 'form-transfer-' . $transfer->id . '.pdf';
                return $pdf->stream($fileName);
            })(),
            default => abort(400, 'Invalid intention')
        };
    }

    // NOTE: Bulk export / Excel export are intentionally omitted for now (report-only scope).
}
