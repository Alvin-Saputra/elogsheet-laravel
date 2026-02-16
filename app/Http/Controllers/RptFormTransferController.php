<?php

namespace App\Http\Controllers;

use App\Models\LSFormTransferHeader;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
     * Helper: Determine prefix based on user roles (same as F/QCO-013)
     */
    private function decidePrefixFromRoles($userRoles)
    {
        $LEAD_QC = ['LEAD', 'LEAD_QC'];
        $QC_Control_MGR = ['MGR', 'MGR_QC', 'ADM'];

        if (is_array($userRoles)) {
            $roles = array_map(fn($r) => strtoupper((string) $r), $userRoles);
        } else {
            $roles = array_filter(array_map('trim', preg_split('/[,\|;]+/', (string) $userRoles)));
            $roles = array_map(fn($r) => strtoupper((string) $r), $roles ?: []);
        }

        if (count(array_intersect($roles, $QC_Control_MGR)) > 0) {
            return ['prefix' => 'approved', 'roles' => $roles];
        }

        if (count(array_intersect($roles, $LEAD_QC)) > 0) {
            return ['prefix' => 'prepared', 'roles' => $roles];
        }

        return false;
    }

    /**
     * Helper: Process approval status consistently (same as F/QCO-013)
     */
    private function processApprovalStatus($header, $status, $remark, $user_name, $user_roles)
    {
        $decision = $this->decidePrefixFromRoles($user_roles);
        if ($decision === false) {
            return false;
        }

        $fieldPrefix = $decision['prefix'];
        $rolesArr = $decision['roles'];

        $status = is_string($status) ? Str::ucfirst(strtolower(trim($status))) : $status;

        $updates = [
            "{$fieldPrefix}_status" => $status,
            "{$fieldPrefix}_by" => $user_name,
            "{$fieldPrefix}_date" => now(),
            "{$fieldPrefix}_status_remarks" => $remark,
            'updated_by' => $user_name,
            'updated_date' => now(),
        ];

        if (Schema::hasColumn($header->getTable(), "{$fieldPrefix}_role")) {
            $updates["{$fieldPrefix}_role"] = json_encode($rolesArr);
        }

        if ($fieldPrefix === 'approved' && Schema::hasColumn($header->getTable(), 'status')) {
            $updates['status'] = $status;
        }

        return $header->update($updates);
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

            $success = $this->processApprovalStatus($header, $status, $remark, $user->username, $user->roles);

            if (!$success) {
                DB::rollBack();
                return back()->with('error', 'You do not have permission to approve/reject this record.');
            }

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

    /**
     * POST /bulk/approve
     * Bulk approve all Form Transfer records for selected date based on role
     *
     * LEAD/LEAD_QC: approve records where prepared_status is NULL
     * MGR/MGR_QC/ADM: approve records where prepared_status = 'Approved' AND approved_status is NULL
     */
    public function bulkApprove(Request $request)
    {
        try {
            DB::beginTransaction();

            $status = 'Approved';
            $remark = null;
            $user = Auth::user();
            $username = $user->username ?? ($user->name ?? $user->email);
            $roles = $user->roles;
            $tanggal = $request->input('tanggal') ?? now()->format('Y-m-d');
            $plantCode = session('plant_code');

            // Determine user action level
            $decision = $this->decidePrefixFromRoles($roles);

            if (!$decision) {
                return back()->with('error', 'You do not have permission to bulk approve records.');
            }

            $prefix = $decision['prefix']; // 'prepared' or 'approved'

            // Build query based on role
            $query = LSFormTransferHeader::whereDate('transaction_date', $tanggal);

            // Apply plant filter
            if (!empty($plantCode)) {
                $query->where('plant', $plantCode);
            }

            if ($prefix === 'prepared') {
                // LEAD: Find records with null prepared_status
                $query->whereNull('prepared_status');
            } elseif ($prefix === 'approved') {
                // MGR: Find records with prepared_status='Approved' but null approved_status
                $query->where('prepared_status', 'Approved')
                    ->whereNull('approved_status');
            }

            $records = $query->get();

            if ($records->isEmpty()) {
                return back()->with('error', "No records found to approve for date {$tanggal}.");
            }

            $count = 0;
            foreach ($records as $record) {
                $success = $this->processApprovalStatus($record, $status, $remark, $username, $roles);
                if ($success) {
                    $count++;
                }
            }

            DB::commit();

            return back()->with('success', "Total {$count} Form Transfer records approved successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to bulk approve records: ' . $e->getMessage());
        }
    }

    /**
     * POST /bulk/reject
     * Bulk reject all Form Transfer records for selected date based on role
     *
     * LEAD/LEAD_QC: reject records where prepared_status is NULL
     * MGR/MGR_QC/ADM: reject records where prepared_status = 'Approved' AND approved_status is NULL
     */
    public function bulkReject(Request $request)
    {
        try {
            $request->validate([
                'remark' => 'required|string|max:255'
            ]);

            DB::beginTransaction();

            $status = 'Rejected';
            $remark = $request->remark;
            $user = Auth::user();
            $username = $user->username ?? ($user->name ?? $user->email);
            $roles = $user->roles;
            $tanggal = $request->input('tanggal') ?? now()->format('Y-m-d');
            $plantCode = session('plant_code');

            // Determine user action level
            $decision = $this->decidePrefixFromRoles($roles);

            if (!$decision) {
                return back()->with('error', 'You do not have permission to bulk reject records.');
            }

            $prefix = $decision['prefix']; // 'prepared' or 'approved'

            // Build query based on role
            $query = LSFormTransferHeader::whereDate('transaction_date', $tanggal);

            // Apply plant filter
            if (!empty($plantCode)) {
                $query->where('plant', $plantCode);
            }

            if ($prefix === 'prepared') {
                // LEAD: Find records with null prepared_status
                $query->whereNull('prepared_status');
            } elseif ($prefix === 'approved') {
                // MGR: Find records with prepared_status='Approved' but null approved_status
                $query->where('prepared_status', 'Approved')
                    ->whereNull('approved_status');
            }

            $records = $query->get();

            if ($records->isEmpty()) {
                return back()->with('error', "No records found to reject for date {$tanggal}.");
            }

            $count = 0;
            foreach ($records as $record) {
                $success = $this->processApprovalStatus($record, $status, $remark, $username, $roles);
                if ($success) {
                    $count++;
                }
            }

            DB::commit();

            return back()->with('success', "Total {$count} Form Transfer records rejected successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to bulk reject records: ' . $e->getMessage());
        }
    }
}
