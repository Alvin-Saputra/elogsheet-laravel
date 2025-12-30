<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateArosProductByTruck;
use App\Models\AROSProductByTruckDetail;
use App\Models\AROSProductByTruckHeader;
use App\Models\MControlnumber;
use App\Models\MDataFormNo;
use DB;
use Illuminate\Http\Request;

class AROSProductByTruckController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function create(CreateArosProductByTruck $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->validated();
            $user = $request->user()->getDisplayNameAttribute();

            $dataForm = MDataFormNo::find(19);
            if (!$dataForm) {
                return response()->json([
                    'sucess' => false,
                    'message' => 'Form configuration not found (f_id: 19)'
                ], 400);
            }


            // Get control number for AROSProductByTruck (using prefix 'Q13' and plantid 'PS21')
            $control = MControlnumber::where('prefix', 'Q13')
                ->where('plantid', 'PS21')
                ->lockForUpdate()
                ->first();

            if (!$control) {
                return response()->json([
                    'success' => false,
                    'message' => 'Control number configuration not found',
                ], 400);
            }


            // Generate new AROSProductByTruck document number
            $nextNum = intval($control->autonumber) + 1;
            $paddedNum = str_pad($nextNum, 6, '0', STR_PAD_LEFT);
            $headerId = $control->prefix . $control->plantid . $control->accountingyear . $paddedNum;


            $header = AROSProductByTruckHeader::create([
                ...$data,
                'id' => $headerId,
                'entry_by' => $user,
                'entry_date' => now(),
                'updated_by' => $user,
                'updated_date' => now(),
                'form_no' => $dataForm->f_code,
                'date_issued' => $dataForm->f_date_issued,
                'revision_no' => $dataForm->f_revision_no,
                'revision_date' => $dataForm->f_revision_date,
            ]);
            foreach ($data['details'] as $index => $row) {
                AROSProductByTruckDetail::create([
                    ...$row,
                    'id' => $header->id . 'D' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'id_hdr' => $header->id,
                ]);
            }

            DB::table('m_controlnumber')
                ->where('prefix', $control->prefix)
                ->where('plantid', $control->plantid)
                ->update(['autonumber' => $nextNum]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Analytical Result of Outgoing Shipment Product By Truck created successfully',
                'data' => [
                    'aroip_header_id' => $header->id,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create AROS Plant',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
