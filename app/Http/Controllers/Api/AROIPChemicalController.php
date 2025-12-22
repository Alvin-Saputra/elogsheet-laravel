<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateAroipChemicalRequest;
use App\Http\Requests\UpdateAroipApprovalRequest;
use App\Http\Requests\UpdateAroipChemicalRequest;
use App\Models\AROIPChemicalDetail;
use App\Models\AROIPChemicalHeader;
use App\Models\COADetail;
use App\Models\COAHeader;
use App\Models\MControlnumber;
use App\Models\MDataFormNo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Schema;

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
                        'id_coa',
                        'no_ref_coa',
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
                        'exp_date'
                    ]),
                    ['details' => $item->details]
                ),
                'coa' => $item->coa ?: null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedResult,
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

            // Get form data from m_data_form_no where f_id = 17
            $dataForm = MDataFormNo::find(17);
            if (! $dataForm) {
                return response()->json([
                    'success' => false,
                    'message' => 'Form configuration not found (f_id: 17)',
                ], 400);
            }

            // Handle COA creation if coa data is provided
            $coaNoDoc = null;
            if (isset($data['coa'])) {
                // Get control number for COA (using prefix 'Q11' and plantid 'PS21')
                $coaControl = MControlnumber::where('prefix', 'Q11')
                    ->where('plantid', 'PS21')
                    ->lockForUpdate()
                    ->first();

                if (! $coaControl) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Control number configuration not found for COA',
                    ], 400);
                }

                // Generate COA document number
                $coaNextNum = intval($coaControl->autonumber ?? 0) + 1;
                $paddedNum = str_pad($coaNextNum, 6, '0', STR_PAD_LEFT);
                $year = (string) $coaControl->accountingyear;
                $suffix = 'COA' . ($coaControl->plantid ?? '') . $year . $paddedNum;
                $coaId = ($coaControl->prefix ?? 'Q11') . $suffix;

                // Create COA header
                $coaHeader = COAHeader::create([
                    'id' => $coaId,
                    'no_doc' => $data['coa']['no_doc'],
                    'product' => $data['coa']['product'],
                    'grade' => $data['coa']['grade'],
                    'packing' => $data['coa']['packing'],
                    'quantity' => $data['coa']['quantity'],
                    'tanggal_pengiriman' => $data['coa']['tanggal_pengiriman'],
                    'vehicle' => $data['coa']['vehicle'],
                    'lot_no' => $data['coa']['lot_no'],
                    'production_date' => $data['coa']['production_date'],
                    'expired_date' => $data['coa']['expired_date'],
                    'issue_by' => $user,
                    'issue_date' => now(),
                    'updated_by' => $user,
                    'updated_date' => now(),
                ]);

                // Create COA details
                if (isset($data['coa']['details']) && is_array($data['coa']['details'])) {
                    foreach ($data['coa']['details'] as $index => $detail) {
                        $detailId = $coaId . 'D' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);

                        COADetail::create([
                            'id' => $detailId,
                            'id_hdr' => $coaId,
                            'parameter' => $detail['parameter'] ?? null,
                            'actual_min' => $detail['actual_min'] ?? null,
                            'actual_max' => $detail['actual_max'] ?? null,
                            'standard_min' => $detail['standard_min'] ?? null,
                            'standard_max' => $detail['standard_max'] ?? null,
                            'method' => $detail['method'] ?? null,
                        ]);
                    }
                }

                // Update COA control number
                DB::table('m_controlnumber')
                    ->where('prefix', $coaControl->prefix)
                    ->where('plantid', $coaControl->plantid)
                    ->update(['autonumber' => $coaNextNum]);

                $coaNoDoc = $coaHeader->id;
            }

            // Get control number for AROIP (using prefix 'Q11' and plantid 'PS21')
            $control = MControlnumber::where('prefix', 'Q11')
                ->where('plantid', 'PS21')
                ->lockForUpdate()
                ->first();

            if (! $control) {
                return response()->json([
                    'success' => false,
                    'message' => 'Control number configuration not found',
                ], 400);
            }

            // Generate new AROIP document number
            $nextNum = intval($control->autonumber) + 1;
            $paddedNum = str_pad($nextNum, 6, '0', STR_PAD_LEFT);
            $headerId = $control->prefix . $control->plantid . $control->accountingyear . $paddedNum;

            // Create AROIP header
            $header = AROIPChemicalHeader::create([
                'id' => $headerId,
                'form_no' => $dataForm->f_code,
                'date_issued' => $dataForm->f_date_issued,
                'revision_no' => $dataForm->f_revision_no,
                'revision_date' => $dataForm->f_revision_date,
                'entry_by' => $user,
                'entry_date' => now(),
                'id_coa' => $coaHeader->id,
                'date' => $data['analytical']['date'] ?? null,
                'exp_date' => $data['analytical']['exp_date'] ?? null,
                'no_ref_coa' => $data['analytical']['no_ref_coa'] ?? null,
                'material' => $data['analytical']['material'] ?? null,
                'quantity' => $data['analytical']['received_quantity'] ?? null,
                'analyst' => $data['analytical']['analyst'] ?? null,
                'supplier' => $data['analytical']['supplier'] ?? null,
                'police_no' => $data['analytical']['police_no'] ?? null,
                'batch_lot' => $data['analytical']['batch_lot'] ?? null,
                'status' => $data['analytical']['status'] ?? null,
                'updated_by' => $user,
                'updated_date' => now(),
            ]);

            // Create AROIP details
            if (isset($data['analytical']['details']) && is_array($data['analytical']['details'])) {
                foreach ($data['analytical']['details'] as $index => $detail) {
                    $detailId = $headerId . 'D' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
                    AROIPChemicalDetail::create([
                        'id' => $detailId,
                        'id_hdr' => $headerId,
                        'parameter' => $detail['parameter'] ?? null,
                        'specification_min' => $detail['specification_min'] ?? null,
                        'specification_max' => $detail['specification_max'] ?? null,
                        'result_min' => $detail['result_min'] ?? null,
                        'result_max' => $detail['result_max'] ?? null,
                        'status_ok' => $detail['status_ok'] ?? null,
                        'remark' => $detail['remark'] ?? null,
                    ]);
                }
            }

            // Update AROIP control number
            DB::table('m_controlnumber')
                ->where('prefix', $control->prefix)
                ->where('plantid', $control->plantid)
                ->update(['autonumber' => $nextNum]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'AROIP Chemical created successfully',
                'data' => [
                    'aroip_header_id' => $header->id,
                    'coa_header_id' => $coaHeader->id ?? null,
                    'aroip_detail_ids' => $header->details->pluck('id'),
                    'coa_detail_ids' => isset($coaHeader) ? $coaHeader->details->pluck('id') : [],
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create AROIP Chemical',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * PUT /api/ariopchemical/{id}
     * Update AROIP chemical record
     */
    // public function 
    // update(UpdateAroipChemicalRequest $request, $id)
    // {
    //     try {
    //         DB::beginTransaction();
    //         $user = $request->user()->getDisplayNameAttribute();

    //         // Find the record (excluding soft deleted ones by default)
    //         $header = AROIPChemicalHeader::findOrFail($id);

    //         $data = $request->validated();

    //         // Update header
    //         $header->update([
    //             'material' => $data['material'] ?? $header->material,
    //             'quantity' => $data['quantity'] ?? $header->quantity,
    //             'analyst' => $data['analyst'] ?? $header->analyst,
    //             'supplier' => $data['supplier'] ?? $header->supplier,
    //             'police_no' => $data['police_no'] ?? $header->police_no,
    //             'batch_lot' => $data['batch_lot'] ?? $header->batch_lot,
    //             'status' => $data['status'] ?? $header->status,
    //             'form_no' => $data['form_no'] ?? $header->form_no,
    //             'revision_no' => $data['revision_no'] ?? $header->revision_no,
    //             'revision_date' => $data['revision_date'] ?? $header->revision_date,
    //             'updated_by' => $user,
    //             'updated_date' => now(),
    //         ]);

    //         // Update or create details if provided
    //         if (isset($data['details'])) {
    //             $existingDetailIds = $header->details->pluck('id')->toArray();
    //             $updatedDetailIds = [];

    //             foreach ($data['details'] as $detailData) {
    //                 $detail = AROIPChemicalDetail::updateOrCreate(
    //                     ['id' => $detailData['id']],
    //                     [
    //                         'id_hdr' => $header->id,
    //                         'parameter' => $detailData['parameter'],
    //                         'specification_min' => $detailData['specification_min'],
    //                         'specification_max' => $detailData['specification_max'],
    //                         'result_min' => $detailData['result_min'] ?? null,
    //                         'result_max' => $data['result_max'] ?? null,
    //                         'status_ok' => strtoupper($detailData['status_ok']),
    //                         'remark' => $detailData['remark'] ?? null,
    //                     ]
    //                 );
    //                 $updatedDetailIds[] = $detail->id;
    //             }

    //             // Delete details that were not included in the update
    //             $detailsToDelete = array_diff($existingDetailIds, $updatedDetailIds);
    //             if (! empty($detailsToDelete)) {
    //                 AROIPChemicalDetail::whereIn('id', $detailsToDelete)->delete();
    //             }
    //         }

    //         DB::commit();

    //         // Reload the model with its relationships
    //         $header->load(['details', 'coa.details']);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'AROIP Chemical record updated successfully',
    //             'data' => [
    //                 'analytical' => [
    //                     'header_id' => $header->id,
    //                     'detail_ids' => $header->details->pluck('id')->toArray(),
    //                 ],
    //                 'coa' => $header->coa ? [
    //                     'coa_id' => $header->coa->id,
    //                     'detail_ids' => $header->coa->details->pluck('id')->toArray(),
    //                 ] : null,
    //             ],
    //         ]);
    //     } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'AROIP Chemical record not found',
    //             'error' => $e->getMessage(),
    //         ], 404);
    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to update AROIP Chemical record',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function update(UpdateAroipChemicalRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $user = $request->user()->getDisplayNameAttribute(); // Atau sesuaikan dengan auth user logic Anda

            // 1. Find the AROIP Header
            $header = AROIPChemicalHeader::with(['details', 'coa.details'])->findOrFail($id);

            $data = $request->validated();

            // --- A. UPDATE AROIP HEADER ---
            $header->update([
                'material'      => $data['material'] ?? $header->material,
                'quantity'      => $data['quantity'] ?? $header->quantity,
                'analyst'       => $data['analyst'] ?? $header->analyst,
                'supplier'      => $data['supplier'] ?? $header->supplier,
                'police_no'     => $data['police_no'] ?? $header->police_no,
                'batch_lot'     => $data['batch_lot'] ?? $header->batch_lot,
                'status'        => $data['status'] ?? $header->status,
                'form_no'       => $data['form_no'] ?? $header->form_no,
                'revision_no'   => $data['revision_no'] ?? $header->revision_no,
                'revision_date' => $data['revision_date'] ?? $header->revision_date,
                'updated_by'    => $user,
                'updated_date'  => now(),
            ]);

            // --- B. UPDATE AROIP DETAILS ---
            if (isset($data['details'])) {
                $existingAroipDetailIds = $header->details->pluck('id')->toArray();
                $updatedAroipDetailIds = [];

                foreach ($data['details'] as $detailData) {
                    // Pastikan ID ada untuk update, jika tidak handle logic create (opsional)
                    $detailId = $detailData['id'];

                    $detail = AROIPChemicalDetail::updateOrCreate(
                        ['id' => $detailId],
                        [
                            'id_hdr'            => $header->id,
                            'parameter'         => $detailData['parameter'],
                            'specification_min' => $detailData['specification_min'],
                            'specification_max' => $detailData['specification_max'],
                            'result_min'        => $detailData['result_min'] ?? null,
                            'result_max'        => $detailData['result_max'] ?? null, // FIXED: Typo variable $data -> $detailData
                            'status_ok'         => strtoupper($detailData['status_ok']),
                            'remark'            => $detailData['remark'] ?? null,
                        ]
                    );
                    $updatedAroipDetailIds[] = $detail->id;
                }

                // Delete removed AROIP details
                $detailsToDelete = array_diff($existingAroipDetailIds, $updatedAroipDetailIds);
                if (!empty($detailsToDelete)) {
                    AROIPChemicalDetail::whereIn('id', $detailsToDelete)->delete();
                }
            }

            // --- C. UPDATE COA HEADER & DETAILS ---
            // Cek apakah user mengirim data 'coa' DAN apakah AROIP ini punya COA terkait
            if (isset($data['coa']) && $header->coa) {
                $coaHeader = $header->coa;

                // 1. Update COA Header
                $coaHeader->update([
                    'no_doc'             => $data['coa']['no_doc'] ?? $coaHeader->no_doc,
                    'product'            => $data['coa']['product'] ?? $coaHeader->product,
                    'grade'              => $data['coa']['grade'] ?? $coaHeader->grade,
                    'packing'            => $data['coa']['packing'] ?? $coaHeader->packing,
                    'quantity'           => $data['coa']['quantity'] ?? $coaHeader->quantity,
                    'tanggal_pengiriman' => $data['coa']['tanggal_pengiriman'] ?? $coaHeader->tanggal_pengiriman,
                    'vehicle'            => $data['coa']['vehicle'] ?? $coaHeader->vehicle,
                    'lot_no'             => $data['coa']['lot_no'] ?? $coaHeader->lot_no,
                    'production_date'    => $data['coa']['production_date'] ?? $coaHeader->production_date,
                    'expired_date'       => $data['coa']['expired_date'] ?? $coaHeader->expired_date,
                    'updated_by'         => $user,
                    'updated_date'       => now(),
                ]);

                // 2. Update COA Details
                if (isset($data['coa']['details']) && is_array($data['coa']['details'])) {
                    $existingCoaDetailIds = $coaHeader->details->pluck('id')->toArray();
                    $updatedCoaDetailIds = [];

                    foreach ($data['coa']['details'] as $index => $coaDetailData) {
                        // Logic ID: Gunakan ID dari request, atau generate baru jika kosong (item baru)
                        $coaDetailId = $coaDetailData['id'] ?? null;

                        if (!$coaDetailId) {
                            // Generate ID baru: COA_ID + 'D' + Sequence (Sederhana)
                            // Note: Logic ini asumsi sederhana. Lebih aman jika frontend kirim ID atau backend query max sequence.
                            // Disini kita coba cari next number berdasarkan count existing + index
                            $nextIndex = count($existingCoaDetailIds) + $index + 1;
                            $coaDetailId = $coaHeader->id . 'D' . str_pad($nextIndex, 3, '0', STR_PAD_LEFT);
                        }

                        $coaDetail = COADetail::updateOrCreate(
                            ['id' => $coaDetailId],
                            [
                                'id_hdr'       => $coaHeader->id,
                                'parameter'    => $coaDetailData['parameter'] ?? null,
                                'actual_min'   => $coaDetailData['actual_min'] ?? null,
                                'actual_max'   => $coaDetailData['actual_max'] ?? null,
                                'standard_min' => $coaDetailData['standard_min'] ?? null,
                                'standard_max' => $coaDetailData['standard_max'] ?? null,
                                'method'       => $coaDetailData['method'] ?? null,
                            ]
                        );
                        $updatedCoaDetailIds[] = $coaDetail->id;
                    }

                    // Delete removed COA details
                    $coaDetailsToDelete = array_diff($existingCoaDetailIds, $updatedCoaDetailIds);
                    if (!empty($coaDetailsToDelete)) {
                        COADetail::whereIn('id', $coaDetailsToDelete)->delete();
                    }
                }
            }

            DB::commit();

            // Reload data untuk response
            $header->refresh();
            $header->load(['details', 'coa.details']);

            return response()->json([
                'success' => true,
                'message' => 'AROIP and COA updated successfully',
                'data' => [
                    'analytical' => $header,
                    'coa' => $header->coa
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Record not found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update record',
                'error' => $e->getMessage()
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

    /**
     * Update approval status of AROIP Chemical record
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateApproval($id, UpdateAroipApprovalRequest $request)
    {
        try {
            DB::beginTransaction();
            $header = AROIPChemicalHeader::findOrFail($id);
            $user = auth()->user();
            $status = strtolower($request->input('status')); // Ensure consistent case
            $remark = $request->input('remarks'); // Changed from 'prepared_status_remarks' to 'remarks'

            $userRoles = $user->roles;
            $isSuccess = $this->processApprovalStatus($header, $status, $remark, $user->username, $userRoles);

            if (! $isSuccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update approval status',
                ], 403);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Approval status updated successfully',
                'data' => [
                    'id' => $header->id,
                    'status' => $status,
                    'remarks' => $remark,  // Include remarks in response for verification
                    'updated_by' => $user->username,
                    'updated_at' => now()->toDateTimeString(),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update approval status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process approval status update (helper method)
     */
    private function processApprovalStatus($header, $status, $remark, $username, $userRoles)
    {
        $LEAD_QC = ['LEAD', 'LEAD_QC'];
        $QC_Control_MGR = ['MGR', 'MGR_QC', 'ADM'];
        $fieldPrefix = '';

        if (in_array($userRoles, $QC_Control_MGR, true)) {
            $fieldPrefix = 'approved';
        } elseif (in_array($userRoles, $LEAD_QC, true)) {
            $fieldPrefix = 'prepared';
        } else {
            return false;
        }

        $updates = [
            "{$fieldPrefix}_status" => $status,
            "{$fieldPrefix}_by" => $username,
            "{$fieldPrefix}_date" => now(),
            "{$fieldPrefix}_status_remarks" => $remark,
            'updated_by' => $username,
            'updated_date' => now(),
        ];

        // Only include role if the column exists
        if (Schema::hasColumn($header->getTable(), "{$fieldPrefix}_role")) {
            $updates["{$fieldPrefix}_role"] = json_encode($userRoles);
        }

        // If this is an approval (not preparation), update the main status
        if ($fieldPrefix === 'approved') {
            $updates['status'] = $status;
        }

        return $header->update($updates);
    }
}
