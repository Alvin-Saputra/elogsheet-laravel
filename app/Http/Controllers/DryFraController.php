<?php

namespace App\Http\Controllers;

use App\Models\MDataFormNo;
use Illuminate\Http\Request;
use App\Models\MControlnumber;
use Illuminate\Support\Facades\DB;
use App\Models\DryFractionationDetail;
use App\Models\DryFractionationHeader;
use App\Http\Requests\CreateDryFraRequest;
use App\Http\Requests\UpdateDryFraRequest;

class DryFraController extends Controller
{
    public function create(CreateDryFraRequest $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->validated();
            $user = $request->user()->getDisplayNameAttribute();

            // ------------------------------------------------------------------
            // 1. CEK DUPLIKASI DATA
            // ------------------------------------------------------------------
            // Kita cek apakah ada data aktif (flag != 'D') dengan kombinasi yang sama.
           $isDuplicate = DryFractionationHeader::query()
                ->whereDate('date', $data['date']) // <--- GANTI JADI whereDate
                ->where('plant', $data['plant'])
                ->where('crystallizer', $data['crystallizer'])
                ->where('flag', '!=', 'D')
                ->exists();

            if ($isDuplicate) {
                // Batalkan proses jika data sudah ada
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Data Logsheet untuk Tanggal, Plant, dan Crystallizer tersebut sudah ada.'
                ], 422); // 422 Unprocessable Entity atau 409 Conflict
            }
            // ------------------------------------------------------------------

            $dataForm = MDataFormNo::find(19);
            if (!$dataForm) {
                return response()->json([
                    'success' => false,
                    'message' => 'Form configuration not found (f_id: 19)'
                ], 400);
            }

            // Get control number for AROSProductByTruck (using prefix 'Q13' and plantid 'PS21')
            $control = MControlnumber::where('prefix', 'DFM')
                ->where('plantid', $data['plant'])
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

            $header = DryFractionationHeader::create([
                ...$data,
                'id' => $headerId,
                'flag' => 'T', 
                'entry_by' => $user,
                'entry_date' => now(),
                'updated_by' => $user,
                'updated_date' => now(),
                'form_no' => $dataForm->f_code,
                'date_issued' => $dataForm->f_date_issued,
                'revision_no' => $dataForm->f_revision_no,
                'revision_date' => $dataForm->f_revision_date,
                // Pastikan flag di-set ke 'T' (atau null, tergantung default DB) saat create
                // agar terhitung sebagai data aktif.
                
            ]);
            
            $prefix = $control->prefix; // "DFM"
            $plant  = $control->plantid; // "PS21"
            $year   = $control->accountingyear; // "25"
            $seq    = $paddedNum; // "000023"

            foreach ($data['details'] as $index => $row) {
                // Format Target: DFMD + PS21 + 25 + 000023 + 1
                // Hasil: DFMDPS21250000231
                $detailId = $prefix . 'D' . $plant . $year . $seq . ($index + 1);

                DryFractionationDetail::create([
                    ...$row,
                    'id'     => $detailId,
                    'id_hdr' => $header->id,
                    'filtration_date' => now()
                ]);
            }
            // update control number
            DB::table('m_controlnumber')
                ->where('prefix', $control->prefix)
                ->where('plantid', $control->plantid)
                ->update(['autonumber' => $nextNum]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Logsheet Dry Fractionation created successfully',
                'data' => [
                    'header_id' => $header->id,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create Ticket',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function get(Request $request)
    {
        // 1. Inisialisasi query awal
        $query = DryFractionationHeader::with(['details'])->where('flag', 'T');

        // 2. Filter berdasarkan Date (jika ada)
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        // 3. Filter berdasarkan Plant ID (jika ada) - TAMBAHAN BARU
        if ($request->filled('plant')) {
            // Asumsi nama kolom di database adalah 'plant'
            $query->where('plant', $request->plant);
        }

        // 4. Urutkan data
        $query->orderBy('date', 'desc');

        // 5. Eksekusi Query
        $result = $query->get();

        // 6. Cek jika user melakukan filter (date ATAU plant) tapi hasilnya kosong
        if ($request->anyFilled(['date', 'plant']) && $result->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No data found for the given filters.',
            ], 404);
        }

        // 7. Return sukses
        return response()->json([
            'success' => true,
            'data' => $result,
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        try {
            $header = DryFractionationHeader::find($id);
            if (! $header) {
                return response()->json(['success' => false, 'error' => 'NOT_FOUND'], 404);
            }

            // Soft-delete by marking flag = 'F' and updating audit fields

            $header->flag = 'D';
            $header->updated_by = $request->user() ? $request->user()->getDisplayNameAttribute() : $header->updated_by;
            $header->updated_date = now();
            $header->save();

            return response()->json(['success' => true], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'data' => $th->getMessage(),
            ], 500);
        }
    }


    public function update(UpdateDryFraRequest $request, $id)
    {
        try {
            DB::beginTransaction();

            // 1. Cari Header
            $header = DryFractionationHeader::find($id);
            
            if (!$header) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data not found'
                ], 404);
            }

            $data = $request->validated();
            $user = $request->user()->getDisplayNameAttribute();

            // 2. Update Header
            // Kita filter field mana saja yang BOLEH diupdate (Rule 1: Kecuali ID & Info Form)
            // Field audit (form_no, dll) tidak kita masukkan ke fill.
            $header->update([
                'date' => $data['date'],
                'posting_date' => $data['posting_date'],
                'company' => $data['company'],
                'plant' => $data['plant'],
                'crystallizer' => $data['crystallizer'],
                'feed_oil_iv' => $data['feed_oil_iv'],
                'initial_oil_level' => $data['initial_oil_level'],
                'filling_start_time' => $data['filling_start_time'],
                'filling_end_time' => $data['filling_end_time'],
                'cooling_start_temp' => $data['cooling_start_temp'],
                'cooling_start_time' => $data['cooling_start_time'],
                'agitator_speed' => $data['agitator_speed'],
                'water_pump_pres' => $data['water_pump_pres'],
                'remarks' => $data['remarks'],
                'is_completed' => $data['is_completed'] ?? $header->is_completed,
                
                // Update Audit Trail
                'updated_by' => $user,
                'updated_date' => now(),
            ]);

            // 3. Proses Details
            if (isset($data['details']) && is_array($data['details'])) {
                
                // Ambil daftar ID detail yang ada di database saat ini untuk header ini
                $existingDetails = DryFractionationDetail::where('id_hdr', $header->id)->get();
                
                // Cari angka suffix terakhir untuk generate ID baru (jika ada insert)
                // ID Format: DFMDPS21250000231 (Kita butuh angka '1' di ujung)
                $maxSuffix = 0;
                foreach ($existingDetails as $exDetail) {
                    // Ambil angka terakhir dari string ID
                    if (preg_match('/(\d+)$/', $exDetail->id, $matches)) {
                        $suffix = intval($matches[1]);
                        if ($suffix > $maxSuffix) {
                            $maxSuffix = $suffix;
                        }
                    }
                }

                foreach ($data['details'] as $row) {
                    // Cek apakah item ini punya ID (Berarti Update)
                    if (isset($row['id']) && !empty($row['id'])) {
                        // --- LOGIC UPDATE EXISTING DETAIL ---
                        $detailModel = DryFractionationDetail::where('id', $row['id'])
                            ->where('id_hdr', $header->id) // Safety check
                            ->first();

                        if ($detailModel) {
                            $detailModel->update([
                                'filtration_cycle_number' => $row['filtration_cycle_number'],
                                // 'filtration_date' => $row['filtration_date'],
                                'filtration_temp' => $row['filtration_temp'],
                                'time_start_filtration' => $row['time_start_filtration'],
                                'time_end_filtration' => $row['time_end_filtration'],
                                'load' => $row['load'],
                                'olein_iv' => $row['olein_iv'],
                                'olein_cp' => $row['olein_cp'],
                                'olein_ffa' => $row['olein_ffa'],
                                'olein_color_red' => $row['olein_color_red'],
                                'stearin_iv' => $row['stearin_iv'],
                                'stearin_ffa' => $row['stearin_ffa'],
                                'stearin_color_red' => $row['stearin_color_red'],
                                'stearin_pv' => $row['stearin_pv'],
                            ]);
                        }
                    } else {
                        // --- LOGIC INSERT NEW DETAIL (Rule 2) ---
                        $maxSuffix++; // Increment suffix
                        
                        // Kita harus merekonstruksi Base ID untuk detail
                        // Header ID: DFMPS2125000023
                        // Detail ID Target: DFMDPS2125000023 + Suffix
                        
                        // Cara paling aman: Ambil prefix 3 huruf dari header (DFM), sisipkan 'D', lalu sisa header
                        $prefix = substr($header->id, 0, 3); 
                        $restOfHeader = substr($header->id, 3);
                        
                        $newDetailId = $prefix . 'D' . $restOfHeader . $maxSuffix;

                        DryFractionationDetail::create([
                            ...$row, // Spread semua field dari request
                            'id' => $newDetailId,
                            'id_hdr' => $header->id,
                            'filtration_date' => $row['filtration_date'] ?? now(),
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Logsheet updated successfully',
                'data' => [
                    'header_id' => $header->id
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Logsheet',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
