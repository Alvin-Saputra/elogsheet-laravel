<?php

namespace App\Http\Controllers;

use DateTime;
use App\Models\MDataFormNo;
use Illuminate\Http\Request;
use App\Models\MControlnumber;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;
use App\Models\ARIMByVesselDetail;
use App\Models\ARIMByVesselHeader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ARIMByVesselController extends Controller
{
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
                "arrival" => ["required"],
                "material" => "required",
                "quantity" => "required",
                "supplier" => "required",
                "ship_name" => "required",
                // "hasil_analisa_ffa" => "decimal",
                // "hasil_analisa_iv" => "decimal",
                // "hasil_analisa_moisture" => "decimal",
                // "hasil_analisa_dobi" => "decimal",
                // "hasil_analisa_pv" => "decimal",
                // "hasil_analisa_anv" => "decimal"
            ]);
            $validator_det = null;
            foreach ($detail as $det) {
                $validator_det = Validator::make(
                    $det,
                    []
                    // [
                    //     'palka_s_no' => "decimal:3",
                    //     'palka_s_ffa' => "decimal:3",
                    //     'palka_s_iv' => "decimal:3",
                    //     'palka_s_dobi' => "decimal:3",
                    //     'palka_s_mni' => "decimal:3",
                    //     'palka_c_no' => "decimal:3",
                    //     'palka_c_ffa' => "decimal:3",
                    //     'palka_c_iv' => "decimal:3",
                    //     'palka_c_dobi' => "decimal:3",
                    //     'palka_c_mni' => "decimal:3",
                    //     'palka_p_no' => "decimal:3",
                    //     'palka_p_ffa' => "decimal:3",
                    //     'palka_p_iv' => "decimal:3",
                    //     'palka_p_dobi' => "decimal:3",
                    //     'palka_p_mni' => "decimal:3",
                    // ]
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
            $control = MControlnumber::where('prefix', 'Q09')->where('plantid', $data['plant'])->first();
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
            $header = ARIMByVesselHeader::create($payload);

            //inser detail 
            foreach ($detail as $key => $det) {
                $id_det = $hd_id . "D" . $suffix . $key;
                $payload_det = [
                    ...$det,
                    "id" => $id_det,
                    "id_hdr" => $header_id
                ];
                $det_res = ARIMByVesselDetail::create($payload_det);
                array_push($id_det_arr, $id_det);
            }

            //update running number
            MControlnumber::where('prefix', 'Q09')->where('plantid', $data['plant'])->update(['autonumber' => strval($nextnum)]);
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
        if ($request->expectsJson() || $request->wantsJson() || $request->isJson()) {
            try {
                //check if id header exist, get by header id
                $id_header = $request->query('id');
                $plant = $request->query('plant');
                $result = [];
                if ($id_header) {
                    $header = ARIMByVesselHeader::where('plant', $plant)->where('id', $id_header)->first()->toArray();
                    $detail = ARIMByVesselHeader::find($header['id'])->details()->get()->toArray();
                    $result = [
                        [
                            ...$header,
                            "detail" => $detail
                        ]
                    ];
                } else {
                    $header = ARIMByVesselHeader::where('plant', $plant)->get()->toArray();
                    foreach ($header as $hd) {
                        $detail = ARIMByVesselHeader::find($hd['id'])->details()->get()->toArray();
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
        } else {
            $tanggal = $request->input('filter_tanggal');

            if (!$tanggal) {
                $tanggal = now()->toDateString();
            }
            $plantCode = session('plant_code');
            $headers = ARIMByVesselHeader::with('details')
                ->where('plant', $plantCode)
                ->whereDate('arrival', $tanggal)
                ->get();

            return view('rpt_analytical_result_incoming_material_by_vessel.index', compact('headers', 'tanggal'));
        }
    }

    /**
     * Update header and detail for given header id.
     * This replaces all detail rows for the header with provided detail array.
     */
    public function update(Request $request)
    {
        try {
            DB::beginTransaction();
            $user = $request->user()->getDisplayNameAttribute();
            $data = $request->all();
            $id = $data['id'];
            $detail = $data['detail'] ?? [];

            $validator = Validator::make($data, [
                "material" => "required",
                "quantity" => "required",
                "supplier" => "required",
                "ship_name" => "required",
                "hasil_analisa_ffa" => "decimal:3",
                "hasil_analisa_iv" => "decimal:3",
                "hasil_analisa_moisture" => "decimal:3",
                "hasil_analisa_dobi" => "decimal:3",
                "hasil_analisa_pv" => "decimal:3",
                "hasil_analisa_anv" => "decimal:3"
            ]);

            $validator_det = null;
            foreach ($detail as $det) {
                $validator_det = Validator::make(
                    $det,
                    [
                        'palka_s_no' => "decimal:3",
                        'palka_s_ffa' => "decimal:3",
                        'palka_s_iv' => "decimal:3",
                        'palka_s_dobi' => "decimal:3",
                        'palka_s_mni' => "decimal:3",
                        'palka_c_no' => "decimal:3",
                        'palka_c_ffa' => "decimal:3",
                        'palka_c_iv' => "decimal:3",
                        'palka_c_dobi' => "decimal:3",
                        'palka_c_mni' => "decimal:3",
                        'palka_p_no' => "decimal:3",
                        'palka_p_ffa' => "decimal:3",
                        'palka_p_iv' => "decimal:3",
                        'palka_p_dobi' => "decimal:3",
                        'palka_p_mni' => "decimal:3",
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
            $header = ARIMByVesselHeader::where('id', $id)->first();
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
            $existingIds = ARIMByVesselDetail::where('id_hdr', $id)->pluck('id')->toArray();
            $processedIds = [];

            foreach ($detail as $key => $det) {
                $providedId = $det['id'] ?? null;

                if ($providedId && in_array($providedId, $existingIds, true)) {
                    // update existing detail
                    $payload_det = $det;
                    // ensure id_hdr not changed
                    unset($payload_det['id_hdr']);
                    ARIMByVesselDetail::where('id_hdr', $id)->where('id', $providedId)->update($payload_det);
                    $processedIds[] = $providedId;
                } else {
                    // create new detail row
                    $id_det = $providedId ?? ($id . "D" . $key);
                    $payload_det = [
                        ...$det,
                        'id' => $id_det,
                        'id_hdr' => $id
                    ];
                    ARIMByVesselDetail::create($payload_det);
                    $processedIds[] = $id_det;
                }
            }

            // delete any existing detail rows that were not included in the payload
            $toDelete = array_diff($existingIds, $processedIds);
            if (!empty($toDelete)) {
                ARIMByVesselDetail::where('id_hdr', $id)->whereIn('id', $toDelete)->delete();
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

    public function updateApprovalReject(Request $request, $id = null)
    {
        $LEAD_QC = ['LEAD', 'LEAD_QC'];
        $QC_Control_MGR = ["MGR", "MGR_QC", "ADM",];
        $role =  auth()->user()->roles;
        if ($request->expectsJson() || $request->wantsJson() || $request->isJson()) {
            try {
                DB::beginTransaction();

                $data = $request->validate([
                    'id' => 'required|string',
                    'username' => 'required|string',
                    'approve_status' => 'required|in:Approved,Rejected',
                    'remark' => 'nullable|string|max:255',
                ]);

                $header = ARIMByVesselHeader::find($data['id']);

                if (!$header) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'error' => 'DATA_NOT_FOUND'
                    ], 404);
                }

                if (in_array($role, $LEAD_QC, true)) {
                    $header->update([
                        'prepared_status' => $data['approve_status'],
                        'prepared_by'     => $data['username'],
                        'prepared_role'   => $role,
                        'prepared_date'   => now(),
                        'prepared_status_remarks' => $data['remark'],
                    ]);

                    DB::commit();
                } else if (in_array($role, $QC_Control_MGR, true)) {
                    $header->update([
                        'approved_status' => $data['approve_status'],
                        'approved_by'     => $data['username'],
                        'approved_role'   => $role,
                        'approved_date'   => now(),
                        'approved_status_remarks' => $data['remark'],
                    ]);
                    DB::commit();
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Approval updated successfully'
                ], 200);
            } catch (\Throwable $th) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'data' => $th->getMessage()
                ], 500);
            }
        } else {

            $report = ARIMByVesselHeader::findOrFail($id);
            $status = $request->query('status');
            $request->validate(['remark' => 'nullable|string|max:255']);
            if (in_array($role, $LEAD_QC, true)) {
                $report->update([
                    'prepared_status' => $status,
                    'prepared_by'     => auth()->user()->username,
                    'prepared_role'   => $role,
                    'prepared_date'   => now(),
                    'prepared_status_remarks' => $request->remark,
                ]);

                DB::commit();
            } else if (in_array($role, $QC_Control_MGR, true)) {
                $report->update([
                    'approved_status' => $status,
                    'approved_by'     => auth()->user()->username,
                    'approved_role'   => $role,
                    'approved_date'   => now(),
                    'approved_status_remarks' => $request->remark,
                ]);
                DB::commit();
            }
            if ($status == "Approved") {
                return back()->with('success-approve', "Tiket {$report->id} berhasil di-$status");
            } else if ($status == "Rejected") {
                return back()->with('success-reject', "Tiket {$report->id} berhasil di-$status");
            }
        }
    }


    /**
     * Delete header (details cascade) by id
     */
    public function destroy(Request $request, $id)
    {
        try {
            $header = ARIMByVesselHeader::find($id);
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

    public function show($id)
    {
        $data = ARIMByVesselHeader::with('details')->findOrFail($id);
        // kalau tidak ada, otomatis throw 404

        return view('rpt_analytical_result_incoming_material_by_vessel.show', [
            'header' => $data
        ]);
    }

    public function preview($id)
    {
        $data = ARIMByVesselHeader::with('details')->findOrFail($id);
        // kalau tidak ada, otomatis throw 404

        return view('rpt_analytical_result_incoming_material_by_vessel.preview_layout', [
            'header' => $data
        ]);
    }

    public function export($id)
    {
        $data = ARIMByVesselHeader::with('details')->findOrFail($id);

        $pdf = Pdf::loadView('exports.report_analytical_result_incoming_material_by_vessel_pdf', [
            'header' => $data,
        ]);

        $pdf->setPaper('a4', 'portrait');
        $fileName = 'startup-produksi-checklist-' . $data->id . '.pdf';
        return $pdf->stream($fileName);
    }
}
