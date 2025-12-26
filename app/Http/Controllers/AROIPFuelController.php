<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateAroipFuelRequest;
use App\Http\Requests\UpdateAroipFuelRequest;
use App\Models\AROIPFuelDetail;
use App\Models\AROIPFuelHeader;
use App\Models\MControlnumber;
use App\Models\MDataFormNo;
use App\Models\ROADetail;
use App\Models\ROAHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            if (! $dataForm) {
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

            if (! $roaControl) {
                throw new \Exception('Control number configuration not found for ROA');
            }

            // Generate ID ROA
            $roaNextNum = intval($roaControl->autonumber ?? 0) + 1;
            $paddedNum = str_pad($roaNextNum, 6, '0', STR_PAD_LEFT);
            $year = (string) $roaControl->accountingyear;
            $suffix = 'ROA'.($roaControl->plantid ?? '').$year.$paddedNum;
            $roaId = ($roaControl->prefix ?? 'Q12A').$suffix;

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
                $detailId = $roaId.'D'.str_pad($index + 1, 3, '0', STR_PAD_LEFT);
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

            if (! $control) {
                throw new \Exception('Control number configuration not found for AROIP');
            }

            // Generate ID AROIP
            $nextNum = intval($control->autonumber) + 1;
            $paddedNum = str_pad($nextNum, 6, '0', STR_PAD_LEFT);
            // Format ID sesuaikan kebutuhan, ini contoh standar:
            $headerId = $control->prefix.$control->plantid.$control->accountingyear.$paddedNum;

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
                $detailId = $headerId.'D'.str_pad($index + 1, 3, '0', STR_PAD_LEFT);
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

    public function update(UpdateAroipFuelRequest $request, $id)
    {
        try {
            DB::beginTransaction();

            $user = $request->user()->getDisplayNameAttribute();
            $data = $request->validated();

            // Find the header
            $header = AROIPFuelHeader::with(['details', 'roa.details'])->findOrFail($id);

            // Helper: compute next detail id for a header by scanning existing ids in DB
            $nextDetailIdForHeader = function ($headerId, $detailModelClass) {
                // fetch existing ids from DB to be safe across concurrent transactions
                $rows = $detailModelClass::where('id_hdr', $headerId)->pluck('id')->toArray();
                $max = 0;
                foreach ($rows as $rid) {
                    if (preg_match('/D(\d+)$/', $rid, $m)) {
                        $num = intval($m[1]);
                        if ($num > $max) {
                            $max = $num;
                        }
                    }
                }
                // next sequence
                $nextSeq = $max + 1;

                return $headerId.'D'.str_pad($nextSeq, 3, '0', STR_PAD_LEFT);
            };

            // ----------------------------
            // A) Update ROA (header + details) if provided
            // ----------------------------
            if (isset($data['roa'])) {
                $roaPayload = $data['roa'];

                // load the related ROA header
                $roaHeader = ROAHeader::with('details')->findOrFail($header->id_roa);

                // update ROA header fields
                $roaHeader->update([
                    'report_no' => $roaPayload['report_no'] ?? $roaHeader->report_no,
                    'shipper' => $roaPayload['shipper'] ?? $roaHeader->shipper,
                    'buyer' => $roaPayload['buyer'] ?? $roaHeader->buyer,
                    'date_received' => $roaPayload['date_received'] ?? $roaHeader->date_received,
                    'date_analyzed_start' => $roaPayload['date_analyzed_start'] ?? $roaHeader->date_analyzed_start,
                    'date_analyzed_end' => $roaPayload['date_analyzed_end'] ?? $roaHeader->date_analyzed_end,
                    'date_reported' => $roaPayload['date_reported'] ?? $roaHeader->date_reported,
                    'lab_sample_id' => $roaPayload['lab_sample_id'] ?? $roaHeader->lab_sample_id,
                    'customer_sample_id' => $roaPayload['customer_sample_id'] ?? $roaHeader->customer_sample_id,
                    'seal_no' => $roaPayload['seal_no'] ?? $roaHeader->seal_no,
                    'weight_of_received_sample' => $roaPayload['weight_of_received_sample'] ?? $roaHeader->weight_of_received_sample,
                    'top_size_of_received_sample' => $roaPayload['top_size_of_received_sample'] ?? $roaHeader->top_size_of_received_sample,
                    'updated_by' => $user,
                    'updated_date' => now(),
                ]);

                // sync roa details if provided
                if (isset($roaPayload['details']) && is_array($roaPayload['details'])) {
                    $existingIds = $roaHeader->details->pluck('id')->toArray();
                    $receivedIds = [];

                    foreach ($roaPayload['details'] as $row) {
                        // if client provided an id, update that detail
                        if (! empty($row['id'])) {
                            $detail = ROADetail::where('id', $row['id'])
                                ->where('id_hdr', $roaHeader->id)
                                ->first();

                            if (! $detail) {
                                DB::rollBack();

                                return response()->json([
                                    'success' => false,
                                    'message' => "ROA detail id {$row['id']} not found for ROA {$roaHeader->id}.",
                                ], 404);
                            }

                            $detail->update([
                                'parameter' => $row['parameter'] ?? $detail->parameter,
                                'unit' => $row['unit'] ?? $detail->unit,
                                'basis' => $row['basis'] ?? $detail->basis,
                                'result' => $row['result'] ?? $detail->result,
                            ]);

                            $receivedIds[] = $detail->id;
                        } else {
                            // no id -> create new deterministic id
                            $newId = $nextDetailIdForHeader($roaHeader->id, ROADetail::class);

                            $created = ROADetail::create([
                                'id' => $newId,
                                'id_hdr' => $roaHeader->id,
                                'parameter' => $row['parameter'] ?? null,
                                'unit' => $row['unit'] ?? null,
                                'basis' => $row['basis'] ?? null,
                                'result' => $row['result'] ?? null,
                            ]);

                            $receivedIds[] = $created->id;
                        }
                    }

                    // delete details removed by the client (sync)
                    $toDelete = array_diff($existingIds, $receivedIds);
                    if (! empty($toDelete)) {
                        ROADetail::whereIn('id', $toDelete)->delete();
                    }
                }
            }

            // ----------------------------
            // B) Update AROIP Fuel header if provided
            // ----------------------------
            if (isset($data['analytical'])) {
                $analPayload = $data['analytical'];

                $header->update([
                    'date' => $analPayload['date'] ?? $header->date,
                    'material' => $analPayload['material'] ?? $header->material,
                    'quantity' => $analPayload['quantity'] ?? $header->quantity,
                    'supplier' => $analPayload['supplier'] ?? $header->supplier,
                    'police_no' => $analPayload['police_no'] ?? $header->police_no,
                    'analyst' => $analPayload['analyst'] ?? $header->analyst,
                    'updated_by' => $user,
                    'updated_date' => now(),
                    'form_no' => $analPayload['form_no'] ?? $header->form_no,
                    'date_issued' => $analPayload['date_issued'] ?? $header->date_issued,
                    'revision_no' => $analPayload['revision_no'] ?? $header->revision_no,
                    'revision_date' => $analPayload['revision_date'] ?? $header->revision_date,
                ]);

                // sync analytical details if provided
                if (isset($analPayload['details']) && is_array($analPayload['details'])) {
                    $existingIds = $header->details->pluck('id')->toArray();
                    $receivedIds = [];

                    foreach ($analPayload['details'] as $row) {
                        if (! empty($row['id'])) {
                            $detail = AROIPFuelDetail::where('id', $row['id'])
                                ->where('id_hdr', $header->id)
                                ->first();

                            if (! $detail) {
                                DB::rollBack();

                                return response()->json([
                                    'success' => false,
                                    'message' => "Analytical detail id {$row['id']} not found for header {$header->id}.",
                                ], 404);
                            }

                            $detail->update([
                                'parameter' => $row['parameter'] ?? $detail->parameter,
                                'result_min' => $row['result_min'] ?? $detail->result_min,
                                'result_max' => $row['result_max'] ?? $detail->result_max,
                                'specification_min' => $row['specification_min'] ?? $detail->specification_min,
                                'specification_max' => $row['specification_max'] ?? $detail->specification_max,
                                'status_ok' => isset($row['status_ok']) ? strtoupper($row['status_ok']) : $detail->status_ok,
                                'remark' => $row['remark'] ?? $detail->remark,
                            ]);

                            $receivedIds[] = $detail->id;
                        } else {
                            // no id -> create new deterministic id
                            $newId = $nextDetailIdForHeader($header->id, AROIPFuelDetail::class);

                            $created = AROIPFuelDetail::create([
                                'id' => $newId,
                                'id_hdr' => $header->id,
                                'parameter' => $row['parameter'] ?? null,
                                'result_min' => $row['result_min'] ?? null,
                                'result_max' => $row['result_max'] ?? null,
                                'specification_min' => $row['specification_min'] ?? null,
                                'specification_max' => $row['specification_max'] ?? null,
                                'status_ok' => isset($row['status_ok']) ? strtoupper($row['status_ok']) : null,
                                'remark' => $row['remark'] ?? null,
                            ]);

                            $receivedIds[] = $created->id;
                        }
                    }

                    // delete details removed by the client (sync)
                    $toDelete = array_diff($existingIds, $receivedIds);
                    if (! empty($toDelete)) {
                        AROIPFuelDetail::whereIn('id', $toDelete)->delete();
                    }
                }
            }

            DB::commit();

            // return fresh header with relations
            $header->refresh();
            $header->load(['details', 'roa.details']);

            return response()->json([
                'success' => true,
                'message' => 'AROIP Fuel record updated successfully',
                'data' => [
                    'aroip_header' => $header,
                ],
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Record not found: '.$e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update AROIP Fuel record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function get(Request $request)
    {
        $query = AROIPFuelHeader::with(['details', 'roa.details']);

        // Filter by date_issued
        if ($request->filled('entry_date')) {
            $query->whereDate('entry_date', $request->entry_date);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('entry_date', [
                $request->start_date,
                $request->end_date,
            ]);
        }

        // Order by most recent first
        $query->orderBy('entry_date', 'desc');

        $result = $query->get();

        if ($request->anyFilled(['entry_date', 'start_date', 'end_date']) && $result->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No data found for the given filters.',
            ], 404);
        }

        // Format the response with 'analytical' and 'coa' keys
        $formattedResult = $result->map(function ($item) {
            return [
                'analytical' => array_merge(
                    $item->only([
                        'id',
                        'id_roa',
                        'material',
                        'quantity',
                        'analyst',
                        'supplier',
                        'police_no',
                        'batch_lot',
                        'status',
                        'flag',
                        'entry_by',
                        'entry_date',
                        'prepared_by',
                        'prepared_date',
                        'prepared_status',
                        'prepared_status_remarks',
                        'approved_by',
                        'approved_date',
                        'approved_status',
                        'approved_status_remarks',
                        'updated_by',
                        'updated_date',
                        'form_no',
                        'date_issued',
                        'revision_no',
                        'revision_date',
                        'deleted_at',
                        'date',
                        'exp_date',
                    ]),
                    ['details' => $item->details]
                ),
                'roa' => $item->roa ?: null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedResult,
        ]);
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Find the record (including soft deleted ones)
            $header = AROIPFuelHeader::withTrashed()->findOrFail($id);

            // Check if already soft deleted
            if ($header->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'AROIP Fuel record is already deleted.',
                ], 400);
            }

            // Soft delete the header
            $header->delete();

            AROIPFuelDetail::where('id_hdr', $id)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'AROIP Fuel record has been deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'AROIP Fuel record not found.',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete AROIP Fuel record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
