<?php

namespace App\Http\Controllers;

use App\Models\AROIPChemicalHeader;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Schema;

class AROIPChemicalController extends Controller
{
    private function findHeaderWithId($id)
    {
        return AROIPChemicalHeader::with(['details', 'coa.details'])->findOrFail($id);
    }
    /**
     * Normalize roles value to an array and decide which prefix to use.
     * Returns ['prefix' => 'prepared'|'approved', 'roles' => array] or false when not allowed.
     */
    private function decidePrefixFromRoles($userRoles)
    {
        $LEAD_QC = ['LEAD', 'LEAD_QC'];
        $QC_Control_MGR = ['MGR', 'MGR_QC', 'ADM'];

        // Normalize to array of upper-case strings
        if (is_array($userRoles)) {
            $roles = array_map(fn($r) => strtoupper((string) $r), $userRoles);
        } else {
            $roles = array_filter(array_map('trim', preg_split('/[,\|;]+/', (string) $userRoles)));
            $roles = array_map(fn($r) => strtoupper((string) $r), $roles ?: []);
        }

        // intersection check
        if (count(array_intersect($roles, $QC_Control_MGR)) > 0) {
            return ['prefix' => 'approved', 'roles' => $roles];
        }

        if (count(array_intersect($roles, $LEAD_QC)) > 0) {
            return ['prefix' => 'prepared', 'roles' => $roles];
        }

        return false;
    }

    private function processApprovalStatus($header, $status, $remark, $user_name, $user_roles)
    {
        $decision = $this->decidePrefixFromRoles($user_roles);

        if ($decision === false) {
            return false;
        }

        $fieldPrefix = $decision['prefix'];
        $rolesArr = $decision['roles'];

        // normalize status string
        $status = is_string($status) ? ucfirst(strtolower(trim($status))) : $status;

        $updates = [
            "{$fieldPrefix}_status" => $status,
            "{$fieldPrefix}_by" => $user_name,
            "{$fieldPrefix}_date" => now(),
            "{$fieldPrefix}_status_remarks" => $remark,
            'updated_by' => $user_name,
            'updated_date' => now(),
        ];

        // write role column if present
        if (Schema::hasColumn($header->getTable(), "{$fieldPrefix}_role")) {
            $updates["{$fieldPrefix}_role"] = json_encode($rolesArr);
        }

        // if manager (approved) also update overall status column when present
        if ($fieldPrefix === 'approved' && Schema::hasColumn($header->getTable(), 'status')) {
            $updates['status'] = $status;
        }

        return $header->update($updates);
    }

    // --- web actions ---

    public function index(Request $request)
    {
        $tanggal = $request->input('filter_tanggal', now()->toDateString());

        $headers = AROIPChemicalHeader::query()
            ->whereDate('entry_date', $tanggal)
            ->orderBy('entry_date', 'desc')
            ->get([
                'id',
                'material',
                'quantity',
                'prepared_status',
                'approved_status',
                'entry_date',
            ]);

        return view('rpt_analytical_result_of_incoming_plant_chemical.index', compact('headers', 'tanggal'));
    }

    public function show($id)
    {
        $data = $this->findHeaderWithId($id);
        return view('rpt_analytical_result_of_incoming_plant_chemical.show', ['header' => $data]);
    }

    public function preview($id)
    {
        $data = $this->findHeaderWithId($id);
        return view('rpt_analytical_result_of_incoming_plant_chemical.preview_layout', ['header' => $data]);
    }

    public function export($id)
    {
        $data = $this->findHeaderWithId($id);

        $pdf = Pdf::loadView('exports.report_rpt_analytical_result_of_incoming_plant_chemical_pdf', [
            'header' => $data,
        ]);

        $pdf->setPaper('a4', 'landscape');
        $fileName = 'aroip-chemical-' . $data->id . '.pdf';

        return $pdf->stream($fileName);
    }

    public function getById(Request $request, $id)
    {
        $data = $this->findHeaderWithId($id);
        $intention = $request->query('intention');

        return match ($intention) {
            'show' => view('rpt_analytical_result_of_incoming_plant_chemical.show', ['header' => $data]),
            'preview' => view('rpt_analytical_result_of_incoming_plant_chemical.preview_layout', ['header' => $data]),
            'export' => (function () use ($data) {
                    $pdf = Pdf::loadView('exports.report_rpt_analytical_result_of_incoming_plant_chemical_pdf', [
                    'header' => $data,
                    ]);
                    $pdf->setPaper('a4', 'landscape');
                    $fileName = 'aroip-chemical-' . $data->id . '.pdf';
                    return $pdf->stream($fileName);
                })(),
            default => abort(400, 'Invalid intention'),
        };
    }

    /**
     * Handle approval via web (POST route).
     * Accepts ?status=Approved|Rejected and optional remark in request body.
     */
    public function updateApprovalStatusWeb(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $report = AROIPChemicalHeader::findOrFail($id);

            $status = $request->query('status') ?? $request->input('status');
            $remark = $request->input('remark');
            $username = auth()->user()?->username ?? auth()->user()?->getDisplayNameAttribute();
            $role = auth()->user()?->roles ?? null;

            // process
            $isSuccess = $this->processApprovalStatus($report, $status, $remark, $username, $role);

            if (!$isSuccess) {
                DB::rollBack();
                return back()->with('error', 'You do not have permission to update approval status');
            }

            DB::commit();

            if ($status === 'Approved') {
                return back()->with('success-approve', "Tiket {$report->id} berhasil di-{$status}");
            } elseif ($status === 'Rejected') {
                return back()->with('success-reject', "Tiket {$report->id} berhasil di-{$status}");
            }

            return back()->with('success', 'Approval status updated');
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', $th->getMessage());
        }
    }
}
