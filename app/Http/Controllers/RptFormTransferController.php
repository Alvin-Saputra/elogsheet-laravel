<?php

namespace App\Http\Controllers;

use App\Models\LSFormTransferHeader;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

    /**
     * GET /export/view
     * View Layout for all Form Transfers for the selected date
     */
    public function exportView(Request $request)
    {
        $tanggal = $request->input('filter_tanggal', Carbon::today()->format('Y-m-d'));
        $plantCode = session('plant_code');

        $query = LSFormTransferHeader::with('details')->whereDate('transaction_date', $tanggal);

        if (!empty($plantCode)) {
            $query->where('plant', $plantCode);
        }

        $transfers = $query->orderBy('transaction_date', 'desc')->get();

        return view('rpt_form_transfer.preview_all', compact('transfers', 'tanggal'));
    }

    /**
     * GET /export/pdf
     * Download PDF for all Form Transfers for the selected date
     */
    public function exportPdf(Request $request)
    {
        $tanggal = $request->input('filter_tanggal', Carbon::today()->format('Y-m-d'));
        $plantCode = session('plant_code');

        $query = LSFormTransferHeader::with('details')->whereDate('transaction_date', $tanggal);

        if (!empty($plantCode)) {
            $query->where('plant', $plantCode);
        }

        $transfers = $query->orderBy('transaction_date', 'desc')->get();

        $pdf = Pdf::loadView('exports.form_transfer_all_pdf', compact('transfers', 'tanggal'))
            ->setPaper('a4', 'landscape');

        $fileName = 'form-transfer-' . $tanggal . '.pdf';
        return $pdf->stream($fileName);
    }

    /**
     * POST /{id}/approve
     * Approve or Reject Form Transfer record from web UI
     *
     * 2-Step Approval Workflow:
     * - prepared_status: Lead roles (LEAD, LEAD_QC)
     * - approved_status: Manager roles (MGR, MGR_QC, ADM)
     */
    public function approve(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $header = LSFormTransferHeader::findOrFail($id);
            $user = Auth::user();
            $status = Str::ucfirst(strtolower($request->query('status', 'Approved')));
            $remark = $request->input('remark');

            // Determine approval level based on user role
            $LEAD_ROLES = ['LEAD', 'LEAD_QC'];
            $MGR_ROLES = ['MGR', 'MGR_QC', 'ADM'];

            $userRole = $user->roles;
            $level = null;

            if (in_array($userRole, $MGR_ROLES, true)) {
                $level = 'approved';
            } elseif (in_array($userRole, $LEAD_ROLES, true)) {
                $level = 'prepared';
            }

            if (!$level) {
                return back()->with('error', 'You do not have permission to approve/reject this record.');
            }

            // Update the appropriate approval fields
            $header->update([
                "{$level}_status"         => $status,
                "{$level}_by"             => $user->username,
                "{$level}_role"           => json_encode($userRole),
                "{$level}_date"           => now(),
                "{$level}_status_remarks" => $remark,
            ]);

            DB::commit();

            $action = $status === 'Approved' ? 'approved' : 'rejected';
            return back()->with('success', "Form Transfer #{$id} has been {$action} successfully.");
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return back()->with('error', 'Record not found.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update approval status: ' . $e->getMessage());
        }
    }
}
