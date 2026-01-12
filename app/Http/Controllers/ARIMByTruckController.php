<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\MDataFormNo;
use Illuminate\Http\Request;
use App\Models\MControlnumber;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ARIMByTruckDetail;
use App\Models\ARIMByTruckHeader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ARIMByTruckController extends Controller
{
    private function findHeaderWithId($id)
    {
        return ARIMByTruckHeader::with('details')->findOrFail($id);
    }

    private function processApprovalStatus($header, $status, $remark, $user_name, $user_roles)
    {
        $LEAD_QC = ['LEAD', 'LEAD_QC'];
        $QC_Control_MGR = ["MGR", "MGR_QC", "ADM"];

        $fieldPrefix = '';

        if (in_array($user_roles, $QC_Control_MGR, true)) {
            $fieldPrefix = 'approved';
        } elseif (in_array($user_roles, $LEAD_QC, true)) {
            $fieldPrefix = 'prepared';
        } else {
            return false;
        }

        $header->update([
            "{$fieldPrefix}_status" => $status,
            "{$fieldPrefix}_by" => $user_name,
            "{$fieldPrefix}_role" => json_encode($user_roles),
            "{$fieldPrefix}_date" => now()->utc()->toDateTimeString(),
            "{$fieldPrefix}_status_remarks" => $remark,
        ]);

        return true;
    }

    /**
     * Convert incoming datetime (assumed Asia/Jakarta) to UTC string for DB
     */
    private function toUtcString(mixed $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // If already Carbon instance, convert; else parse with Asia/Jakarta as input tz
        try {
            if ($value instanceof Carbon) {
                return $value->copy()->utc()->toDateTimeString();
            }

            return Carbon::parse($value, 'Asia/Jakarta')->utc()->toDateTimeString();
        } catch (\Throwable $e) {
            // fallback: try generic parse then convert
            return Carbon::parse($value)->utc()->toDateTimeString();
        }
    }

    // ----- API Request Function -------
    public function create(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = $request->user()->getDisplayNameAttribute();
            $data = $request->all();
            $detail = $data['detail'] ?? [];
            $id_det_arr = [];

            $validator = Validator::make($data, [
                "menu_id" => "required",
                "company" => "required",
                "plant" => "required",
                "material" => "required",
                "arrival_date" => ["required"],
                "supplier" => "required",
                "vessel_vehicle" => "required",
                "ss_ffa" => "numeric",
                "ss_mni" => "numeric",
                "detail" => "required"
            ]);

            $validator_det = null;
            foreach ($detail as $det) {
                $validator_det = Validator::make(
                    $det,
                    [
                        'no' => "required",
                        'sampling_date' => "required",
                        'police_no' => "required",
                        'p_ffa' => "numeric",
                        'p_moisture' => "numeric",
                        'p_iv' => "numeric",
                        'p_dobi' => "numeric",
                        'p_pv' => "numeric",
                        'p_color_r' => "numeric",
                        'p_color_y' => "numeric",
                    ]
                );
                if ($validator->fails()) {
                    break;
                }
            }

            if ($validator->fails() || ($validator_det && $validator_det->fails())) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'error' => "INVALID_PAYLOAD",
                    'data' => [
                        'header' => $validator->errors()->all(),
                        'detail' => $validator_det ? $validator_det->errors()->all() : []
                    ]
                ], 400);
            }

            // get id rule
            $data_form = MDataFormNo::where('is_menu', $data['menu_id'])->first();
            if (!$data_form) {
                return response()->json([
                    'success' => false,
                    'error' => "INVALID_DATA_FORM",
                ], 400);
            }

            // get m_control_number
            $control = MControlnumber::where('prefix', 'Q10')->where('plantid', $data['plant'])->first();
            $nextnum = intval($control['autonumber']) + 1;
            $padded_num = str_pad($nextnum, 6, '0', STR_PAD_LEFT);
            $hd_id = $control['prefix'];
            $suffix = $control['plantid'] . $control['accountingyear'] . $padded_num;
            $header_id = $hd_id . $suffix;

            // Prepare header payload: convert provided arrival_date from Asia/Jakarta -> UTC
            $payload = $data;
            $payload['id'] = $header_id;
            $payload['flag'] = 'T';
            $payload['transaction_date'] = now()->utc()->toDateTimeString();
            $payload['entry_by'] = $user;
            $payload['entry_date'] = now()->utc()->toDateTimeString();
            $payload['form_no'] = $data_form['f_code'];
            $payload['date_issued'] = $data_form['f_date_issued'];
            $payload['revision_no'] = $data_form['f_revision_no'];

            // arrival_date - convert incoming (assume Asia/Jakarta) to UTC DB value
            if (isset($data['arrival_date'])) {
                $payload['arrival_date'] = $this->toUtcString($data['arrival_date']);
            }

            // insert header
            $header = ARIMByTruckHeader::create($payload);

            // insert detail rows (convert sampling_date similarly)
            foreach ($detail as $key => $det) {
                $id_det = $hd_id . "D" . $suffix . $key;

                $payload_det = $det;
                $payload_det['id'] = $id_det;
                $payload_det['id_hdr'] = $header_id;

                if (isset($det['sampling_date'])) {
                    $payload_det['sampling_date'] = $this->toUtcString($det['sampling_date']);
                }

                ARIMByTruckDetail::create($payload_det);
                array_push($id_det_arr, $id_det);
            }

            // update running number
            MControlnumber::where('prefix', 'Q10')->where('plantid', $data['plant'])->update(['autonumber' => strval($nextnum)]);
            DB::commit();

            return response()->json([
                'success' => true,
                'id_header' => $header_id,
                'id_det' => $id_det_arr
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function get(Request $request)
    {
        try {
            $id_header = $request->query('id');
            $plant = $request->query('plant');
            $date = $request->query('date');

            // base query (urut berdasarkan arrival_date ascending)
            $query = ARIMByTruckHeader::with('details')->orderBy('arrival_date', 'asc');

            if ($plant) {
                $query->where('plant', $plant);
            }

            if ($id_header) {
                $query->where('id', $id_header);
            }

            // kalau filter by date (tanggal lokal yg user masukkan diasumsikan format 'Y-m-d')
            if ($date) {
                // karena DB menyimpan UTC, kita cari berdasarkan DATE(arrival_date) pada UTC yang sesuai;
                // lebih aman: convert date range Jakarta -> UTC range then whereBetween
                $startJakarta = \Carbon\Carbon::createFromFormat('Y-m-d', $date, 'Asia/Jakarta')->startOfDay();
                $endJakarta = \Carbon\Carbon::createFromFormat('Y-m-d', $date, 'Asia/Jakarta')->endOfDay();

                $startUtc = $startJakarta->copy()->utc()->toDateTimeString();
                $endUtc = $endJakarta->copy()->utc()->toDateTimeString();

                $query->whereBetween('arrival_date', [$startUtc, $endUtc]);
            }

            $headers = $query->get();

            // Map headers -> format semua datetime ke Asia/Jakarta 'Y-m-d H:i:s'
            $result = $headers->map(function ($h) {
                // helper to format nullable datetime
                $fmt = function ($val) {
                    if (empty($val))
                        return null;
                    return \Carbon\Carbon::parse($val)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s');
                };

                return [
                    'id' => $h->id,
                    'company' => $h->company,
                    'plant' => $h->plant,
                    'transaction_date' => $fmt($h->transaction_date),
                    'material' => $h->material,
                    'arrival_date' => $fmt($h->arrival_date),
                    'contract_do' => $h->contract_do,
                    'supplier' => $h->supplier,
                    'vessel_vehicle' => $h->vessel_vehicle,
                    'ss_ffa' => $h->ss_ffa,
                    'ss_mni' => $h->ss_mni,
                    'ss_others' => $h->ss_others,
                    'flag' => $h->flag,
                    'entry_by' => $h->entry_by,
                    'entry_date' => $fmt($h->entry_date),
                    'prepared_by' => $h->prepared_by,
                    'prepared_date' => $fmt($h->prepared_date),
                    'prepared_status' => $h->prepared_status,
                    'prepared_status_remarks' => $h->prepared_status_remarks,
                    'approved_by' => $h->approved_by,
                    'approved_date' => $fmt($h->approved_date),
                    'approved_status' => $h->approved_status,
                    'approved_status_remarks' => $h->approved_status_remarks,
                    'updated_by' => $h->updated_by,
                    'updated_date' => $fmt($h->updated_date),
                    'form_no' => $h->form_no,
                    'date_issued' => $fmt($h->date_issued),
                    'revision_no' => $h->revision_no,
                    'revision_date' => $fmt($h->revision_date),
                    'detail' => $h->details->map(function ($d) use ($fmt) {
                        return [
                            'id' => $d->id,
                            'id_hdr' => $d->id_hdr,
                            'no' => $d->no,
                            'sampling_date' => $fmt($d->sampling_date),
                            'police_no' => $d->police_no,
                            'p_ffa' => $d->p_ffa,
                            'p_moisture' => $d->p_moisture,
                            'p_iv' => $d->p_iv,
                            'p_dobi' => $d->p_dobi,
                            'p_pv' => $d->p_pv,
                            'p_color_r' => $d->p_color_r,
                            'p_color_y' => $d->p_color_y,
                            'analis' => $d->analis,
                            'remarks' => $d->remarks,
                        ];
                    })->toArray(),
                ];
            })->toArray();

            return response()->json(['success' => true, 'data' => $result], 200);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'data' => $th->getMessage()], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $header = ARIMByTruckHeader::find($id);
            if (!$header) {
                return response()->json(['success' => false, 'error' => 'NOT_FOUND'], 404);
            }

            $header->flag = 'F';
            $header->updated_by = $request->user() ? $request->user()->getDisplayNameAttribute() : $header->updated_by;
            $header->updated_date = now()->utc()->toDateTimeString();
            $header->save();

            return response()->json(['success' => true], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            DB::beginTransaction();
            $user = $request->user()->getDisplayNameAttribute();
            $data = $request->all();
            $id = $data['id'];
            $detail = $data['detail'] ?? [];

            $validator = Validator::make($data, [
                "id" => "required",
                "material" => "required",
                "supplier" => "required",
                "vessel_vehicle" => "required",
                "ss_ffa" => "numeric",
                "ss_mni" => "numeric",
                "detail" => "required"
            ]);

            $validator_det = null;
            foreach ($detail as $det) {
                $validator_det = Validator::make(
                    $det,
                    [
                        'no' => "required",
                        'police_no' => "required",
                        'p_ffa' => "numeric",
                        'p_moisture' => "numeric",
                        'p_iv' => "numeric",
                        'p_dobi' => "numeric",
                        'p_pv' => "numeric",
                        'p_color_r' => "numeric",
                        'p_color_y' => "numeric",
                    ]
                );
                if ($validator->fails()) {
                    break;
                }
            }

            if ($validator->fails() || ($validator_det && $validator_det->fails())) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'error' => "INVALID_PAYLOAD",
                    'data' => [
                        'header' => $validator->errors()->all(),
                        'detail' => $validator_det ? $validator_det->errors()->all() : []
                    ]
                ], 400);
            }

            $header = ARIMByTruckHeader::where('id', $id)->first();
            if (!$header) {
                DB::rollBack();
                return response()->json(['success' => false, 'error' => 'NOT_FOUND'], 404);
            }

            $payload = $data;
            $payload['updated_by'] = $user;
            $payload['updated_date'] = now()->utc()->toDateTimeString();

            // convert arrival_date if provided (assume Asia/Jakarta input)
            if (isset($data['arrival_date'])) {
                $payload['arrival_date'] = $this->toUtcString($data['arrival_date']);
            }

            unset($payload['id']);

            $header->update($payload);

            // Update details by id: update existing, insert new, delete removed
            $existingIds = ARIMByTruckDetail::where('id_hdr', $id)->pluck('id')->toArray();
            $processedIds = [];

            foreach ($detail as $key => $det) {
                $providedId = $det['id'] ?? null;

                if ($providedId && in_array($providedId, $existingIds, true)) {
                    $payload_det = $det;
                    unset($payload_det['id_hdr']);

                    if (isset($det['sampling_date'])) {
                        $payload_det['sampling_date'] = $this->toUtcString($det['sampling_date']);
                    }

                    ARIMByTruckDetail::where('id_hdr', $id)->where('id', $providedId)->update($payload_det);
                    $processedIds[] = $providedId;
                } else {
                    $id_det = $providedId ?? ($id . "D" . $key);
                    $payload_det = $det;
                    $payload_det['id'] = $id_det;
                    $payload_det['id_hdr'] = $id;

                    if (isset($det['sampling_date'])) {
                        $payload_det['sampling_date'] = $this->toUtcString($det['sampling_date']);
                    }

                    ARIMByTruckDetail::create($payload_det);
                    $processedIds[] = $id_det;
                }
            }

            $toDelete = array_diff($existingIds, $processedIds);
            if (!empty($toDelete)) {
                ARIMByTruckDetail::where('id_hdr', $id)->whereIn('id', $toDelete)->delete();
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'id_header' => $id,
                'id_det' => $processedIds
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function updateApprovalStatusApi(Request $request, $id = null)
    {
        try {
            $data = $request->validate([
                'id' => 'required|string',
                'approve_status' => 'required|in:Approved,Rejected',
                'remark' => 'nullable|string|max:255',
            ]);

            $header = ARIMByTruckHeader::find($data['id']);
            $role = auth()->user()->roles;
            $username = auth()->user()->username;
            $status = $data['approve_status'];
            $remark = $data['remark'];

            if (!$header) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'error' => 'DATA_NOT_FOUND'
                ], 404);
            }

            $isSuccess = $this->processApprovalStatus($header, $status, $remark, $username, $role);

            if ($isSuccess) {
                return response()->json([
                    'success' => true,
                    'message' => 'Approval updated successfully'
                ], 200);
            }

            return response()->json(['success' => false, 'error' => 'UNAUTHORIZED'], 403);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'data' => $th->getMessage()
            ], 500);
        }
    }

    // ---- Web Request Function ------

    public function index(Request $request)
    {
        $tanggal = $request->input('filter_tanggal') ?: now()->toDateString();
        $plantCode = session('plant_code');

        $headers = ARIMByTruckHeader::with('details')
            ->where('plant', $plantCode)
            ->whereDate('arrival_date', $tanggal)
            ->orderBy('arrival_date', 'asc')   // <<< tambahkan ini
            ->get();

        return view('rpt_analytical_result_incoming_material_by_truck.index', compact('headers', 'tanggal'));
    }
    public function getById(Request $request, $id)
    {
        $data = $this->findHeaderWithId($id);
        $intention = $request->query('intention');

        return match ($intention) {
            'show' => view('rpt_analytical_result_incoming_material_by_truck.show', [
                'header' => $data
            ]),
            'preview' => view('rpt_analytical_result_incoming_material_by_truck.preview_layout', [
                'header' => $data
            ]),
            'export' => (function () use ($data) {
                    $pdf = Pdf::loadView('exports.report_analytical_result_incoming_material_by_truck_pdf', [
                    'header' => $data,
                    ]);
                    $pdf->setPaper('a4', 'portrait');
                    $fileName = 'startup-produksi-checklist-' . $data->id . '.pdf';
                    return $pdf->stream($fileName);
                })(),
            default => abort(400, 'Invalid intention')
        };
    }

    public function updateApprovalStatusWeb(Request $request, $id)
    {
        $report = ARIMByTruckHeader::findOrFail($id);
        $status = $request->query('status');
        $remark = $request->remark;
        $username = auth()->user()->username;
        $role = auth()->user()->roles;

        if ($status === 'Rejected' && empty(trim($remark))) {
            return back()->with('error', 'Alasan penolakan (remark) wajib diisi.');
        }

        $isSuccess = $this->processApprovalStatus($report, $status, $remark, $username, $role);

        if ($isSuccess) {
            if ($status == "Approved") {
                return back()->with('success-approve', "Tiket {$report->id} berhasil di-$status");
            } else if ($status == "Rejected") {
                return back()->with('success-reject', "Tiket {$report->id} berhasil di-$status");
            }
        }

        return back()->with('error', 'Role tidak diperbolehkan melakukan aksi ini.');
    }
}
