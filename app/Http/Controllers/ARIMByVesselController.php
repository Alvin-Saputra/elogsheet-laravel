<?php

namespace App\Http\Controllers;

use App\Models\ARIMByVesselDetail;
use App\Models\ARIMByVesselHeader;
use App\Models\MControlnumber;
use App\Models\MDataFormNo;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
                "arrival" => ["required", 'date_format:Y-m-d h:i:s'],
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
                "transaction_date" => $now->format('Y-m-d h:i:s'),
                "entry_by" => $user,
                "entry_date" => $now->format('Y-m-d h:i:s'),
                "form_no" => $data_form['f_code'],
                "date_issued" => $data_form['f_date_issued'],
                "revision_no" => $data_form['f_revision_no'],
                "revision_date" => $data_form['f_revision_date']
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
            return response()->json([
                'success' => false,
                'data' => $th->getMessage()
            ], 500);
        }
    }

    // public function get(Request $request)
    // {
    //     try {
    //         //check if id header exist, get by header id
    //         $id_header = $request->query('id');
    //         $result = [];
    //         if ($id_header) {
    //             $detail = ARIMByVesselHeader::find($id_header)->details()->get()->toArray();
    //             $header = ARIMByVesselHeader::where('id', $id_header)->first()->toArray();
    //             $result = [
    //                 [
    //                     ...$header,
    //                     "detail" => $detail
    //                 ]
    //             ];
    //         } else {
    //             $result = ARIMByVesselHeader::paginate(1);
    //         }
    //         return response()->json([
    //             'success' => true,
    //             "data" => $result
    //         ], 200);
    //     } catch (\Throwable $th) {
    //         //throw $th;
    //         return response()->json([
    //             'success' => false,
    //             'data' => $th->getMessage()
    //         ], 500);
    //     }
    // }
}
