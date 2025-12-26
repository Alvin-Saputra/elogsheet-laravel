<?php

namespace App\Http\Controllers;

use App\Models\ROADetail;
use App\Models\ROAHeader;
use App\Models\MDataFormNo;
use Illuminate\Http\Request;
use App\Models\MControlnumber;
use App\Models\AROIPFuelDetail;
use App\Models\AROIPFuelHeader;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\CreateAroipFuelRequest;

class AROIPFuelController extends Controller
{
     public function create(CreateAroipFuelRequest $request)
    {
        try {
            DB::beginTransaction();
            
            // Asumsi: Method getDisplayNameAttribute() ada di model User
            $user = $request->user()->getDisplayNameAttribute(); 
            $data = $request->validated();

            // ---------------------------------------------------------
            // 1. Validasi Config Form
            // ---------------------------------------------------------
            $dataForm = MDataFormNo::find(18);
            if (!$dataForm) {
                throw new \Exception('Form configuration not found (f_id: 18)');
            }

            // ---------------------------------------------------------
            // 2. PROSES CREATE ROA (Selalu dijalankan)
            // ---------------------------------------------------------
            
            // Ambil Control Number untuk ROA
            $roaControl = MControlnumber::where('prefix', 'Q12A')
                ->where('plantid', 'PS21')
                ->lockForUpdate()
                ->first();

            if (!$roaControl) {
                throw new \Exception('Control number configuration not found for ROA');
            }

            // Generate ID ROA
            $roaNextNum = intval($roaControl->autonumber ?? 0) + 1;
            $paddedNum  = str_pad($roaNextNum, 6, '0', STR_PAD_LEFT);
            $year       = (string) $roaControl->accountingyear;
            $suffix     = 'ROA' . ($roaControl->plantid ?? '') . $year . $paddedNum;
            $roaId      = ($roaControl->prefix ?? 'Q12A') . $suffix;

            // Simpan Header ROA
            $roaHeader = ROAHeader::create([
                'id' => $roaId,
                'report_no' => $data['roa']['report_no'],
                'shipper' => $data['roa']['shipper'],
                'buyer' => $data['roa']['buyer'],
                'date_received' => $data['roa']['date_received'],
                'date_analyzed_start' => $data['roa']['date_analyzed_start'],
                'date_analyzed_end' => $data['roa']['date_analyzed_end'],
                'date_reported' => $data['roa']['date_reported'],
                'lab_sample_id' => $data['roa']['lab_sample_id'],
                'customer_sample_id' => $data['roa']['customer_sample_id'],
                'seal_no' => $data['roa']['seal_no'],
                'weight_of_received_sample' => $data['roa']['weight_of_received_sample'],
                'top_size_of_received_sample' => $data['roa']['top_size_of_received_sample'],
                'authorized_by' => $user,
                'authorized_date' => now(),
                'updated_by' => $user,
                'updated_date' => now(),
            ]);

            // Simpan Details ROA
            foreach ($data['roa']['details'] as $index => $detail) {
                $detailId = $roaId . 'D' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
                ROADetail::create([
                    'id' => $detailId,
                    'id_hdr' => $roaId,
                    'parameter' => $detail['parameter'],
                    'unit' => $detail['unit'] ?? null,
                    'basis' => $detail['basis'] ?? null,
                    'result' => $detail['result'] ?? null,
                ]);
            }

            // Update Auto Number ROA
            DB::table('m_controlnumber')
                ->where('prefix', $roaControl->prefix)
                ->where('plantid', $roaControl->plantid)
                ->update(['autonumber' => $roaNextNum]);


            // ---------------------------------------------------------
            // 3. PROSES CREATE AROIP FUEL
            // ---------------------------------------------------------
            
            // Ambil Control Number untuk AROIP
            // PERHATIKAN: Prefix-nya sama 'Q12A'? Jika beda, sesuaikan di sini.
            $control = MControlnumber::where('prefix', 'Q12A') 
                ->where('plantid', 'PS21')
                ->lockForUpdate()
                ->first();

            if (!$control) {
                throw new \Exception('Control number configuration not found for AROIP');
            }

            // Generate ID AROIP
            $nextNum = intval($control->autonumber) + 1;
            $paddedNum = str_pad($nextNum, 6, '0', STR_PAD_LEFT);
            // Format ID sesuaikan kebutuhan, ini contoh standar:
            $headerId = $control->prefix . $control->plantid . $control->accountingyear . $paddedNum;

            // Simpan Header AROIP Fuel
            $header = AROIPFuelHeader::create([
                'id' => $headerId,
                'id_roa' => $roaId, // Menggunakan ID dari ROA yang baru saja dibuat
                'date' => $data['analytical']['date'],
                'material' => $data['analytical']['material'],
                'quantity' => $data['analytical']['quantity'], // FIX: sesuaikan dengan nama field di validasi
                'supplier' => $data['analytical']['supplier'],
                'police_no' => $data['analytical']['police_no'],
                'analyst' => $data['analytical']['analyst'],
                'updated_by' => $user,
                'updated_date' => now(),
                'form_no' => $dataForm->f_code,
                'date_issued' => $dataForm->f_date_issued,
                'revision_no' => $dataForm->f_revision_no,
                'revision_date' => $dataForm->f_revision_date,
                'entry_by' => $user,
                'entry_date' => now(),
            ]);

            // Simpan Details AROIP Fuel
            foreach ($data['analytical']['details'] as $index => $detail) {
                $detailId = $headerId . 'D' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
                AROIPFuelDetail::create([
                    'id' => $detailId,
                    'id_hdr' => $headerId,
                    'parameter' => $detail['parameter'],
                    'result_min' => $detail['result_min'],
                    'result_max' => $detail['result_max'] ?? null,
                    'specification_min' => $detail['specification_min'],
                    'specification_max' => $detail['specification_max'],
                    'status_ok' => $detail['status_ok'],
                    'remark' => $detail['remark'] ?? null,
                ]);
            }

            // Update Auto Number AROIP
            DB::table('m_controlnumber')
                ->where('prefix', $control->prefix)
                ->where('plantid', $control->plantid)
                ->update(['autonumber' => $nextNum]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'AROIP Fuel created successfully',
                'data' => [
                    'aroip_header_id' => $header->id,
                    'roa_header_id' => $roaHeader->id,
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create AROIP Fuel',
                'error' => $e->getMessage(), // Matikan ini di production agar aman
            ], 500);
        }
    }
}
