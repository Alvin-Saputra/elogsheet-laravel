<?php

namespace App\Http\Controllers;

use DateTime;

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

        if (in_array($user_roles, $QC_Control_MGR, true)) { // Gunakan intersect utk keamanan array
            $fieldPrefix = 'approved';
        } elseif (in_array($user_roles, $LEAD_QC, true)) {

            $fieldPrefix = 'prepared';
        } else {
            // Jika role tidak cocok, return false atau throw error
            return false;
        }

        $header->update([
            "{$fieldPrefix}_status"         => $status,
            "{$fieldPrefix}_by"             => $user_name,
            "{$fieldPrefix}_role"           => json_encode($user_roles), // Simpan sebagai string/json jika perlu
            "{$fieldPrefix}_date"           => now(),
            "{$fieldPrefix}_status_remarks" => $remark,
        ]);

        return true;
    }

    // ----- API Request Function -------
    public function create(Request $request)
    {
        try {
            DB::beginTransaction();
            $user = $request->user()->getDisplayNameAttribute();
            $data = $request->all();
            $detail = $data['detail'];
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
            if ($validator->fails() || $validator_det->fails()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'error' => "INVALID_PAYLOAD",
                    'data' => [
                        'header' => $validator->errors()->all(),
                        'detail' => $validator_det->errors()->all()
                    ]
                ], 400);
            }
            // get id rule
            // HHHHJJJJYYXXXXXX
            // H => Form code (see m_control_number)
            // J => plant code
            // Y => year
            // X => running number (see autonumber in m_control_number, add +1)
            // if data is detail, the format will be :
            // HHHHDJJJJYYXXXXXX
            // D is character "D" sign as "Detail"

            //get data form no
            $data_form = MDataFormNo::where('is_menu', $data['menu_id'])->first();
            if (!$data_form) {
                return response()->json([
                    'success' => false,
                    'error' => "INVALID_DATA_FORM",
                ], 400);
            }

            //get m_control_number
            $control = MControlnumber::where('prefix', 'Q10')->where('plantid', $data['plant'])->first();
            $nextnum = intval($control['autonumber']) + 1;
            $padded_num = str_pad($nextnum, 6, '0', STR_PAD_LEFT);
            $hd_id = $control['prefix'];
            $suffix = $control['plantid'] . $control['accountingyear'] . $padded_num;
            $header_id = $hd_id . $suffix;
            $now = new DateTime();

            $payload = [
                ...$data,
                "id" => $header_id,
                'flag' => 'T',
                "transaction_date" => $now->format('Y-m-d h:i:s'),
                "entry_by" => $user,
                "entry_date" => $now->format('Y-m-d h:i:s'),
                "form_no" => $data_form['f_code'],
                "date_issued" => $data_form['f_date_issued'],
                "revision_no" => $data_form['f_revision_no'],
                // "revision_date" => $data_form['f_revision_date']->format('Y-m-d h:i:s')
            ];

            //insert header 
            $header = ARIMByTruckHeader::create($payload);

            //inser detail 
            foreach ($detail as $key => $det) {
                $id_det = $hd_id . "D" . $suffix . $key;
                $payload_det = [
                    ...$det,
                    "id" => $id_det,
                    "id_hdr" => $header_id
                ];
                $det_res = ARIMByTruckDetail::create($payload_det);
                array_push($id_det_arr, $id_det);
            }

            //update running number
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
            //check if id header exist, get by header id
            $id_header = $request->query('id');
            $plant = $request->query('plant');
            $date = $request->query('date');
            $result = [];
            if ($id_header) {
                $header = ARIMByTruckHeader::where('plant', $plant)->where('id', $id_header)->first()->toArray();
                $detail = ARIMByTruckHeader::find($header['id'])->details()->get()->toArray();
                $result = [
                    [
                        ...$header,
                        "detail" => $detail
                    ]
                ];
            } else if ($plant && $date) {
                $header = ARIMByTruckHeader::where('plant', $plant)->whereDate('arrival_date', $date)->get()->toArray();

                foreach ($header as $hd) {
                    $detail = ARIMByTruckHeader::find($hd['id'])->details()->get()->toArray();
                    array_push($result, [...$hd, 'detail' => $detail]);
                }
            } else if (!$plant && $date) {
                $header = ARIMByTruckHeader::whereDate('arrival', $date)->get()->toArray();

                foreach ($header as $hd) {
                    $detail = ARIMByTruckHeader::find($hd['id'])->details()->get()->toArray();
                    array_push($result, [...$hd, 'detail' => $detail]);
                }
            } else {
                $header = ARIMByTruckHeader::where('plant', $plant)->get()->toArray();
                foreach ($header as $hd) {
                    $detail = ARIMByTruckHeader::find($hd['id'])->details()->get()->toArray();
                    array_push($result, [...$hd, 'detail' => $detail]);
                }
            }
            return response()->json([
                'success' => true,
                "data" => $result
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'success' => false,
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $header = ARIMByTruckHeader::find($id);
            if (!$header) {
                return response()->json(['success' => false, 'error' => 'NOT_FOUND'], 404);
            }

            // Soft-delete by marking flag = 'F' and updating audit fields
            $now = new DateTime();
            $header->flag = 'F';
            $header->updated_by = $request->user() ? $request->user()->getDisplayNameAttribute() : $header->updated_by;
            $header->updated_date = $now->format('Y-m-d h:i:s');
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
                // "arrival_date" => ["required"],
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
                        // 'sampling_date' => "required",
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

            // find header
            $header = ARIMByTruckHeader::where('id', $id)->first();
            if (!$header) {
                DB::rollBack();
                return response()->json(['success' => false, 'error' => 'NOT_FOUND'], 404);
            }

            // prepare update payload
            $now = new DateTime();
            $payload = [
                ...$data,
                'updated_by' => $user,
                'updated_date' => $now->format('Y-m-d h:i:s')
            ];

            // avoid changing id
            unset($payload['id']);

            $header->update($payload);

            // Update details by id: update existing, insert new, delete removed
            $existingIds = ARIMByTruckDetail::where('id_hdr', $id)->pluck('id')->toArray();
            $processedIds = [];

            foreach ($detail as $key => $det) {
                $providedId = $det['id'] ?? null;

                if ($providedId && in_array($providedId, $existingIds, true)) {
                    // update existing detail
                    $payload_det = $det;
                    // ensure id_hdr not changed
                    unset($payload_det['id_hdr']);
                    ARIMByTruckDetail::where('id_hdr', $id)->where('id', $providedId)->update($payload_det);
                    $processedIds[] = $providedId;
                } else {
                    // create new detail row
                    $id_det = $providedId ?? ($id . "D" . $key);
                    $payload_det = [
                        ...$det,
                        'id' => $id_det,
                        'id_hdr' => $id
                    ];
                    ARIMByTruckDetail::create($payload_det);
                    $processedIds[] = $id_det;
                }
            }

            // delete any existing detail rows that were not included in the payload
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
            $role =  auth()->user()->roles;
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
        $tanggal = $request->input('filter_tanggal');

        if (!$tanggal) {
            $tanggal = now()->toDateString();
        }
        $plantCode = session('plant_code');
        $headers = ARIMByTruckHeader::with('details')
            ->where('plant', $plantCode)
            ->whereDate('arrival_date', $tanggal)
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
        $role =  auth()->user()->roles;

        $isSuccess = $this->processApprovalStatus($report, $status, $remark, $username, $role);

        if ($isSuccess) {
            if ($status == "Approved") {
                return back()->with('success-approve', "Tiket {$report->id} berhasil di-$status");
            } else if ($status == "Rejected") {

                return back()->with('success-reject', "Tiket {$report->id} berhasil di-$status");
            }
        }
    }
}
