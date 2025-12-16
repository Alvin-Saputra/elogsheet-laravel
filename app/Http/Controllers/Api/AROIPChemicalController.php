<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateAroipChemicalRequest;
use App\Http\Requests\UpdateAroipChemicalRequest;
use App\Models\AROIPChemicalDetail;
use App\Models\AROIPChemicalHeader;
use App\Models\COADetail;
use App\Models\COAHeader;
use App\Models\MControlnumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AROIPChemicalController extends Controller
{
    /**
     * GET /api/ariopchemical
     * Get all AROIP chemical data with filtering options
     */
    public function get(Request $request)
    {
        $query = AROIPChemicalHeader::with(['details', 'coa.details']);

        // Filter by date_issued
        if ($request->filled('date_issued')) {
            $query->whereDate('date_issued', $request->date_issued);
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

        if ($request->anyFilled(['date_issued', 'start_date', 'end_date']) && $result->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No data found for the given filters.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * POST /api/ariopchemical
     * Create AROIP + COA in a single transaction.
     */
    public function create(CreateAroipChemicalRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = $request->user()->getDisplayNameAttribute();
            $data = $request->validated();

            $willCreateCoa = ! empty($data['coa']);
            $willUseExistingCoa = ! empty($data['coa_id']);

            if (! $willCreateCoa && ! $willUseExistingCoa) {
                throw new \RuntimeException('Either coa or coa_id must be provided.');
            }

            $prefix = 'Q11';
            $control = MControlnumber::where('prefix', $prefix)
                ->lockForUpdate()
                ->first();

            if (! $control) {
                throw new \RuntimeException("CONTROL_NUMBER_NOT_FOUND for prefix {$prefix}");
            }

            $nextNum = intval($control->autonumber ?? 0);
            $padLen = $control->lengthpad ?: 6;
            $year = (string) $control->accountingyear;
            $plant = $control->plantid ?? '';

            $coa = null;
            if ($willCreateCoa) {
                $nextNum += 1;
                $padded = str_pad($nextNum, $padLen, '0', STR_PAD_LEFT);
                $suffix = 'COA'.$plant.$year.$padded;
                $coaId = ($control->prefix ?? $prefix).$suffix;

                $coaPayload = array_merge(
                    ['id' => $coaId],

                    $data['coa'],
                    [
                        'issue_by' => $user,
                        'issue_date' => now(),
                        'updated_by' => $user,
                        'updated_date' => now(),
                    ]
                );

                $coa = COAHeader::create($coaPayload);

                $coaDetailIds = [];
                foreach ($data['coa']['details'] as $i => $det) {
                    $detailId = ($control->prefix ?? $prefix).'D'.$suffix.$i;
                    $detArr = (array) $det;
                    $detailPayload = array_merge($detArr, [
                        'id_hdr' => $coa->id,
                        'id' => $detailId,
                    ]);
                    $detailRecord = COADetail::create($detailPayload);
                    $coaDetailIds[] = $detailRecord->id;
                }
            } else {
                $coa = COAHeader::find($data['coa_id']);
                if (! $coa) {
                    throw new \RuntimeException('COA_NOT_FOUND');
                }
            }

            $nextNum += 1;
            $paddedAro = str_pad($nextNum, $padLen, '0', STR_PAD_LEFT);
            $suffixAroip = $plant.$year.$paddedAro;
            $aroipHeaderId = ($control->prefix ?? $prefix).$suffixAroip;

            $analytical = $data['analytical'];
            $headerPayload = [
                'id' => $aroipHeaderId,
                'id_coa' => $coa->no_doc,
                'no_ref_coa' => $coa->no_doc,
                'material' => $analytical['material'],
                'quantity' => $analytical['received_quantity'],
                'analyst' => $analytical['analyst'],
                'supplier' => $analytical['supplier'],
                'police_no' => $analytical['police_no'],
                'batch_lot' => $analytical['batch_lot'],
                'status' => $analytical['status'],
                'entry_by' => $user,
                'entry_date' => now(),
                'updated_by' => $user,
                'updated_date' => now(),
                'form_no' => $analytical['form_no'],
                'date_issued' => now(),
                'revision_no' => $analytical['revision_no'],
                'revision_date' => $analytical['revision_date'],
            ];

            $aroipHeader = AROIPChemicalHeader::create($headerPayload);

            $detailIds = [];
            foreach ($analytical['details'] as $i => $row) {
                $detailId = ($control->prefix ?? $prefix).'D'.$suffixAroip.$i;

                $detailPayload = [
                    'id' => $detailId,
                    'id_hdr' => $aroipHeader->id,
                    'parameter' => $row['parameter'],
                    'specification_min' => $row['specification_min'],
                    'specification_max' => $row['specification_max'],
                    'result_min' => $row['result_min'],
                    'result_max' => $row['result_max'] ?? null,
                    'status_ok' => strtoupper($row['status_ok']),
                    'remark' => $row['remark'] ?? null,
                ];

                $detail = AROIPChemicalDetail::create($detailPayload);
                $detailIds[] = $detail->id;
            }

            DB::table('m_controlnumber')
                ->where('prefix', $control->prefix)
                ->when($control->plantid, fn ($q) => $q->where('plantid', $control->plantid))
                ->update(['autonumber' => $nextNum]);

            DB::commit();

            return response()->json([
                'success' => true,
                'coa_id' => $coa->id,
                'coa_detail_ids' => $coaDetailIds,
                'aroip_header_id' => $aroipHeader->id,
                'aroip_detail_ids' => $detailIds,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => 'CREATE_FAILED',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * PUT /api/ariopchemical/{id}
     * Update AROIP chemical record
     */
    public function update(UpdateAroipChemicalRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $user = $request->user()->getDisplayNameAttribute();

            // Find the record (excluding soft deleted ones by default)
            $header = AROIPChemicalHeader::findOrFail($id);

            $data = $request->validated();

            // Update header
            $header->update([
                'material' => $data['material'] ?? $header->material,
                'quantity' => $data['quantity'] ?? $header->quantity,
                'analyst' => $data['analyst'] ?? $header->analyst,
                'supplier' => $data['supplier'] ?? $header->supplier,
                'police_no' => $data['police_no'] ?? $header->police_no,
                'batch_lot' => $data['batch_lot'] ?? $header->batch_lot,
                'status' => $data['status'] ?? $header->status,
                'form_no' => $data['form_no'] ?? $header->form_no,
                'revision_no' => $data['revision_no'] ?? $header->revision_no,
                'revision_date' => $data['revision_date'] ?? $header->revision_date,
                'updated_by' => $user,
                'updated_date' => now(),
            ]);

            // Update or create details if provided
            if (isset($data['details'])) {
                $existingDetailIds = $header->details->pluck('id')->toArray();
                $updatedDetailIds = [];

                foreach ($data['details'] as $detailData) {
                    $detail = AROIPChemicalDetail::updateOrCreate(
                        ['id' => $detailData['id']],
                        [
                            'id_hdr' => $header->id,
                            'parameter' => $detailData['parameter'],
                            'specification_min' => $detailData['specification_min'],
                            'specification_max' => $detailData['specification_max'],
                            'result_min' => $detailData['result_min'] ?? null,
                            'result_max' => $data['result_max'] ?? null,
                            'status_ok' => strtoupper($detailData['status_ok']),
                            'remark' => $detailData['remark'] ?? null,
                        ]
                    );
                    $updatedDetailIds[] = $detail->id;
                }

                // Delete details that were not included in the update
                $detailsToDelete = array_diff($existingDetailIds, $updatedDetailIds);
                if (! empty($detailsToDelete)) {
                    AROIPChemicalDetail::whereIn('id', $detailsToDelete)->delete();
                }
            }

            DB::commit();

            // Reload the model with its relationships
            $header->load(['details', 'coa.details']);

            return response()->json([
                'success' => true,
                'message' => 'AROIP Chemical record updated successfully',
                'data' => $header,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'AROIP Chemical record not found',
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update AROIP Chemical record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified AROIP Chemical record.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Find the record (including soft deleted ones)
            $header = AROIPChemicalHeader::withTrashed()->findOrFail($id);

            // Check if already soft deleted
            if ($header->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'AROIP Chemical record is already deleted.',
                ], 400);
            }

            // Soft delete the header
            $header->delete();

            AROIPChemicalDetail::where('id_hdr', $id)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'AROIP Chemical record has been deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'AROIP Chemical record not found.',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete AROIP Chemical record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Restore a soft-deleted AROIP Chemical record.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)
    {
        try {
            $header = AROIPChemicalHeader::onlyTrashed()->findOrFail($id);
            $header->restore();

            // Optionally restore related details
            AROIPChemicalDetail::onlyTrashed()
                ->where('id_hdr', $id)
                ->restore();

            return response()->json([
                'success' => true,
                'message' => 'AROIP Chemical record has been restored successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'AROIP Chemical record not found or not trashed',
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore AROIP Chemical record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Permanently delete the specified AROIP Chemical record.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceDelete($id)
    {
        try {
            DB::beginTransaction();

            $header = AROIPChemicalHeader::withTrashed()->findOrFail($id);

            // Permanently delete the details first
            AROIPChemicalDetail::where('id_hdr', $id)->forceDelete();

            // Then delete the header
            $header->forceDelete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'AROIP Chemical record has been permanently deleted.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'AROIP Chemical record not found',
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to permanently delete AROIP Chemical record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
