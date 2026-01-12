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
        // Ambil input tanggal dari user, default = hari ini
        $tanggal = $request->input('filter_tanggal', now()->toDateString());

        // Query sederhana ke 1 tabel (tanpa join)
        $data = LSDailyStorageTankAnalytical::whereDate('posting_date', $tanggal)
            ->where('flag', 'T') // hanya data aktif
            ->get();

        // Hitung apakah user bisa Approve/Reject untuk tanggal ini
        $approvalStatus = $this->getApprovalStatus($tanggal);

        // Kirim hasil query ke view
        return view('rpt_daily_storage_tank_analytical.index', [
            'tanggal' => $tanggal,
            'data' => $data,
            'canApproveReject' => $approvalStatus['canApproveReject'],
            'statusMessage' => $approvalStatus['statusMessage'],
        ]);
    }

    /**
     * Approve semua report untuk sebuah tanggal (Approve Hari Ini)
     */
    public function approveDate(Request $request)
    {
        $request->validate([
            'posting_date' => 'required|date',
        ]);

        $date = $request->posting_date;
        $user = Auth::user();
        $role = $user->roles;

        $reports = LSDailyStorageTankAnalytical::whereDate('posting_date', $date)
            ->where('flag', 'T')
            ->get();

        if ($reports->isEmpty()) {
            return back()->with('error', "Tidak ada data pada tanggal $date.");
        }

        // Jika ada yang sudah direject oleh shift leader, batalkan
        if ($reports->contains(fn($r) => $r->prepared_status === 'Rejected')) {
            return back()->with('error', 'Ada data yang sudah direject oleh leader. Proses dibatalkan.');
        }

        // LEAD melakukan prepared (prepared_status) => hanya jika belum ada prepared_status sama sekali
        if ($role === 'LEAD' || $role === 'LEAD_QC') {
            if ($reports->contains(fn($r) => !is_null($r->prepared_status))) {
                return back()->with('error', 'Beberapa/semua laporan sudah diprepare sebelumnya.');
            }

            LSDailyStorageTankAnalytical::whereDate('posting_date', $date)
                ->where('flag', 'T')
                ->update([
                    'prepared_status' => 'Approved',
                    'prepared_status_remarks' => null,
                    'prepared_date' => now(),
                    'prepared_by' => $user->username ?? $user->name,
                ]);

            return back()->with('success', "Semua laporan tanggal {$date} berhasil di-approve (prepared).");
        }

        // MGR melakukan approve final (approved_status)
        if (in_array($role, ['MGR', 'MGR_QC', 'MGR_PROD', 'ADM'])) {
            // Pastikan semua sudah diprepare
            if ($reports->contains(fn($r) => is_null($r->prepared_status))) {
                return back()->with('error', 'Belum semua laporan diprepare oleh leader.');
            }

            // Jika ada yang direject, batalkan
            if ($reports->contains(fn($r) => $r->prepared_status === 'Rejected')) {
                return back()->with('error', 'Ada laporan yang direject oleh leader. Proses dibatalkan.');
            }

            // Jika sudah ada approved_status pada beberapa, kita tetap set untuk semua (idempotent)
            LSDailyStorageTankAnalytical::whereDate('posting_date', $date)
                ->where('flag', 'T')
                ->update([
                    'approved_status' => 'Approved',
                    'approved_status_remarks' => null,
                    'approved_date' => now(),
                    'approved_by' => $user->username ?? $user->name,
                ]);

            return back()->with('success', "Semua laporan tanggal {$date} berhasil di-approve (approved).");
        }

        return back()->with('error', 'Anda tidak memiliki hak akses untuk melakukan aksi ini.');
    }

    /**
     * Reject semua report untuk sebuah tanggal (Reject Hari Ini)
     */
    public function rejectDate(Request $request)
    {
        $request->validate([
            'posting_date' => 'required|date',
            'remark' => 'nullable|string|max:255',
        ]);

        $date = $request->posting_date;
        $user = Auth::user();
        $role = $user->roles;
        $remark = $request->remark;

        $reports = LSDailyStorageTankAnalytical::whereDate('posting_date', $date)
            ->where('flag', 'T')
            ->get();

        if ($reports->isEmpty()) {
            return back()->with('error', "Tidak ada data pada tanggal $date.");
        }

        // LEAD: menolak (prepared_status) jika belum diprepare atau untuk seluruh data
        if ($role === 'LEAD' || $role === 'LEAD_QC') {
            // Jika leader sudah pernah prepare some, kita izinkan reject (kamu bisa ubah ini jika ingin mencegah)
            LSDailyStorageTankAnalytical::whereDate('posting_date', $date)
                ->where('flag', 'T')
                ->update([
                    'prepared_status' => 'Rejected',
                    'prepared_status_remarks' => $remark,
                    'prepared_date' => now(),
                    'prepared_by' => $user->username ?? $user->name,
                ]);

            return back()->with('success', "Semua laporan tanggal {$date} berhasil di-reject (prepared).");
        }

        // MGR: menolak final (approved_status)
        if (in_array($role, ['MGR', 'MGR_QC', 'MGR_PROD', 'ADM'])) {
            LSDailyStorageTankAnalytical::whereDate('posting_date', $date)
                ->where('flag', 'T')
                ->update([
                    'approved_status' => 'Rejected',
                    'approved_status_remarks' => $remark,
                    'approved_date' => now(),
                    'approved_by' => $user->username ?? $user->name,
                ]);

            return back()->with('success', "Semua laporan tanggal {$date} berhasil di-reject (approved).");
        }

        return back()->with('error', 'Anda tidak memiliki hak akses untuk melakukan aksi ini.');
    }

    public function approveReport($id)
    {
        $report = LSDailyStorageTankAnalytical::findOrFail($id);
        $userRole = Auth::user()->roles;

        if ($userRole === "LEAD" or $userRole === "LEAD_QC") {
            $report->update([
                'prepared_status' => 'Approved',
                'prepared_status_remarks' => null,
                'prepared_date' => now(),
                'prepared_by' => auth()->user()->username ?? auth()->user()->name,
            ]);
        } elseif (in_array($userRole, ["MGR", "MGR_QC", "MGR_PROD", "ADM"])) {
            $report->update([
                'approved_status' => 'Approved',
                'approved_status_remarks' => null,
                'approved_date' => now(),
                'approved_by' => auth()->user()->username ?? auth()->user()->name,
            ]);
        }
        return back()->with('success', "Tiket {$report->id} berhasil di-approve.");
    }

    public function rejectReport(Request $request, $id)
    {
        $request->validate(['remark' => 'nullable|string|max:255']);
        $report = LSDailyStorageTankAnalytical::findOrFail($id);
        $userRole = Auth::user()->roles;

        if ($userRole === "LEAD" or $userRole === "LEAD_QC") {
            $report->update([
                'prepared_status' => 'Rejected',
                'prepared_status_remarks' => $request->remark,
                'prepared_date' => now(),
                'prepared_by' => auth()->user()->username ?? auth()->user()->name,
            ]);
        } elseif (in_array($userRole, ["MGR", "MGR_QC", "MGR_PROD", "ADM"])) {
            $report->update([
                'approved_status' => 'Rejected',
                'approved_status_remarks' => $request->remark,
                'approved_date' => now(),
                'approved_by' => auth()->user()->username ?? auth()->user()->name,
            ]);
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
        [$formInfoFirst, $formInfoLast] = $this->getFormInfo($tanggal);

        // 5 Ambil satu record pertama untuk contoh tanda tangan / info
        $sign = $data->first();

        // 6 Render view dengan semua data
        return view($view, compact('data', 'tanggal', 'sign', 'formInfoFirst', 'formInfoLast'));
    }

    public function exportPdf(Request $request)
    {
        $tanggal = $request->input('filter_tanggal', now()->toDateString());
        $data = LSDailyStorageTankAnalytical::whereDate('posting_date', $tanggal)->get();

        [$formInfoFirst, $formInfoLast] = $this->getFormInfo($tanggal);
        $sign = $data->first();

        $pdf = Pdf::loadView('exports.report_daily_storage_tank_analytical_pdf', compact('data', 'tanggal', 'sign', 'formInfoFirst', 'formInfoLast'))
            ->setPaper('a3', 'landscape');

        return $pdf->stream("dry_fractionation_report_{$tanggal}.pdf");
    }

    /**
     * Hitung apakah tombol approve/reject dapat aktif untuk tanggal tertentu
     */
    private function getApprovalStatus(string $tanggal): array
    {
        $reports = LSDailyStorageTankAnalytical::whereDate('posting_date', $tanggal)
            ->where('flag', 'T')
            ->get();

        $statusMessage = null;
        $canApproveReject = false;
        $user = Auth::user();
        $userRole = $user->roles;

        if ($reports->isEmpty()) {
            $statusMessage = "Tidak ada data pada tanggal $tanggal.";
        } else {
            if ($userRole === "LEAD" || $userRole === "LEAD_QC") {
                // Untuk role LEAD: hanya boleh prepare jika belum ada prepared_status pada seluruh data
                if ($reports->contains(fn($r) => !is_null($r->prepared_status))) {
                    $statusMessage = 'Beberapa atau semua laporan sudah diprepare sebelumnya.';
                    $canApproveReject = false;
                } else {
                    $canApproveReject = true;
                }
            } elseif (in_array($userRole, ["MGR", "MGR_QC", "MGR_PROD", "ADM"])) {
                // Untuk manager: pastikan semua sudah diprepare dan tidak ada yang direject
                if ($reports->contains(fn($r) => is_null($r->prepared_status))) {
                    $statusMessage = 'Belum semua laporan diprepare oleh leader.';
                    $canApproveReject = false;
                } elseif ($reports->contains(fn($r) => $r->prepared_status === 'Rejected')) {
                    $statusMessage = 'Ada laporan yang sudah direject oleh leader.';
                    $canApproveReject = false;
                } elseif ($reports->every(fn($r) => !is_null($r->approved_status))) {
                    $statusMessage = 'Semua data sudah di-review oleh Anda.';
                    $canApproveReject = false;
                } elseif ($reports->every(fn($r) => $r->prepared_status === 'Approved')) {
                    $canApproveReject = true;
                } else {
                    $statusMessage = 'Terdapat data yang tidak valid untuk diproses.';
                    $canApproveReject = false;
                }
            }
        }

        return [
            'canApproveReject' => $canApproveReject,
            'statusMessage' => $statusMessage,
        ];
    }
}
