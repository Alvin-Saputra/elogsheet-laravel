<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateFormTransferRequest;
use App\Models\LSFormTransferHeader;
use App\Models\LSFormTransferDetail;
use App\Models\MControlnumber;
use App\Models\MDataFormNo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FormTransferController extends Controller
{
    /**
     * GET /api/formtransfer
     * Get all Form Transfer data with filtering options
     */
    public function get(Request $request)
    {
        $query = LSFormTransferHeader::with('details');

        // Filter by transaction_date
        if ($request->filled('transaction_date')) {
            $query->whereDate('transaction_date', $request->transaction_date);
        }


        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('transaction_date', [
                $request->start_date,
                $request->end_date,
            ]);
        }

        if ($request->filled('plant')) {
            $query->where('plant', $request->plant);
        }

        // Filter by department
        if ($request->filled('from_dept')) {
            $query->where('from_dept', $request->from_dept);
        }

        if ($request->filled('to_dept')) {
            $query->where('to_dept', $request->to_dept);
        }

        // Filter by status (2-Step Approval Workflow)
        if ($request->filled('status')) {
            $status = strtolower(trim($request->status));
            $normalized = str_replace([' ', '_'], '', $status);

            if ($normalized === 'approved') {
                $query->where(function ($q) {
                    $q->whereRaw('LOWER(prepared_status) = ?', ['approved'])
                        ->orWhereRaw('LOWER(approved_status) = ?', ['approved']);
                });
            } elseif ($normalized === 'rejected') {
                $query->where(function ($q) {
                    $q->whereRaw('LOWER(prepared_status) = ?', ['rejected'])
                        ->orWhereRaw('LOWER(approved_status) = ?', ['rejected']);
                });
            } elseif ($normalized === 'inprogress') {
                $query->where(function ($q) {
                    $q->whereRaw('LOWER(prepared_status) = ?', ['submitted'])
                        ->orWhereRaw('LOWER(approved_status) = ?', ['submitted']);
                });
            } elseif ($normalized === 'submitted') {
                $query->where(function ($q) {
                    $q->whereNull('prepared_status')->orWhere('prepared_status', '');
                })->where(function ($q) {
                    $q->whereNull('approved_status')->orWhere('approved_status', '');
                });
            } else {
                $query->where('approved_status', $request->status);
            }
        }

        // Order by most recent first
        $query->orderBy('entry_date', 'desc');

        $result = $query->get();

        if ($request->anyFilled(['transaction_date', 'start_date', 'end_date', 'from_dept', 'to_dept', 'status', 'plant']) && $result->isEmpty()) {
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
     * GET /api/form-transfer/pending
     * Get pending approvals filtered by level.
     * 2-Step Workflow: prepared (Lead) → approved (Manager)
     */
    public function getPending(Request $request)
    {
        $level = strtolower(trim($request->query('level', '')));
        $query = LSFormTransferHeader::with('details');

        $pendingValues = ['pending', 'draft', 'null', ''];

        $pendingClause = function ($q, $column) use ($pendingValues) {
            $q->whereNull($column)
                ->orWhere($column, '')
                ->orWhere(function ($sub) use ($column, $pendingValues) {
                    $sub->whereRaw('LOWER(' . $column . ') IN (' . implode(',', array_fill(0, count($pendingValues), '?')) . ')', $pendingValues);
                });
        };

        if ($level === 'prepared') {
            // Lead level: prepared_status is null/pending
            $query->where(function ($q) use ($pendingClause) {
                $pendingClause($q, 'prepared_status');
            });
        } elseif ($level === 'approved') {
            // Manager level: prepared_status = 'approved' AND approved_status is null/pending
            $query->whereRaw('LOWER(prepared_status) = ?', ['approved'])
                ->where(function ($q) use ($pendingClause) {
                    $pendingClause($q, 'approved_status');
                });
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid level parameter. Must be: prepared or approved',
            ], 400);
        }

        $query->orderBy('entry_date', 'desc');
        $result = $query->get();

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * POST /api/formtransfer
     * Create Form Transfer header and details in a single transaction.
     */
    public function create(CreateFormTransferRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = $request->user()->getDisplayNameAttribute();
            $data = $request->validated();

            // Get form data from m_data_form_no where f_id = 20 (Form Transfer)
            $dataForm = MDataFormNo::find(20);
            if (!$dataForm) {
                return response()->json([
                    'success' => false,
                    'message' => 'Form configuration not found (f_id: 20)',
                ], 400);
            }

            // Get control number for Form Transfer (use master control config)
            $controlQuery = MControlnumber::where('lu_name', 'FT');
            if (!empty($data['plant'])) {
                $controlQuery->where('plantid', $data['plant']);
            }
            $control = $controlQuery->lockForUpdate()->first();

            if (!$control) {
                $fallbackQuery = MControlnumber::where('imenu', 'Form Transfer');
                if (!empty($data['plant'])) {
                    $fallbackQuery->where('plantid', $data['plant']);
                }
                $control = $fallbackQuery->lockForUpdate()->first();
            }

            if (!$control) {
                return response()->json([
                    'success' => false,
                    'message' => 'Control number configuration not found for Form Transfer',
                ], 400);
            }

            // Generate new Form Transfer document number
            // Format: {prefix} + {plant} + YY + padded sequence
            $nextNum = intval($control->autonumber ?? 0) + 1;
            $padLength = intval($control->lengthpad ?? 6);
            $paddedNum = str_pad($nextNum, $padLength, '0', STR_PAD_LEFT);
            $year = substr((string) $control->accountingyear, -2);
            $plantId = $data['plant'] ?? $control->plantid ?? '';
            $headerId = $control->prefix . $plantId . $year . $paddedNum;

            // Create Form Transfer header
            $header = LSFormTransferHeader::create([
                'id' => $headerId,
                'company' => $data['company'] ?? 'KPN',
                'plant' => $data['plant'] ?? 'PS21',
                'transaction_date' => $data['transaction_date'] ?? now(),
                'to_dept' => $data['to_dept'] ?? null,
                'from_dept' => $data['from_dept'] ?? null,
                'form_no' => $dataForm->f_code,
                'date_issued' => $dataForm->f_date_issued,
                'revision_no' => 1,
                'revision_date' => now()->toDateString(),
                'flag' => $data['flag'] ?? 'I',
                'entry_by' => $user,
                'entry_date' => now(),
                'updated_by' => $user,
                'updated_date' => now(),
            ]);

            // Create Form Transfer details
            if (isset($data['details']) && is_array($data['details'])) {
                foreach ($data['details'] as $index => $detail) {
                    // Detail ID format: {header_id}D{sequence} (e.g., Q18PS2125000001D001)
                    $detailId = $headerId . 'D' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);

                    LSFormTransferDetail::create([
                        'id' => $detailId,
                        'id_hdr' => $headerId,
                        'oil_type' => $detail['oil_type'],
                        'quantity' => $detail['quantity'],
                        'from_storage_tank_no' => $detail['from_storage_tank_no'] ?? null,
                        'from_refinery_fractionation' => $detail['from_refinery_fractionation'] ?? null,
                        'from_other' => $detail['from_other'] ?? null,
                        'to_storage_tank_no' => $detail['to_storage_tank_no'] ?? null,
                        'to_refinery_fractionation' => $detail['to_refinery_fractionation'] ?? null,
                        'to_auto_filling_tank' => $detail['to_auto_filling_tank'] ?? null,
                        'to_other' => $detail['to_other'] ?? null,
                        'quality_m_and_i' => $detail['quality_m_and_i'] ?? null,
                        'quality_ffa' => $detail['quality_ffa'] ?? null,
                        'quality_lov_color_r' => $detail['quality_lov_color_r'] ?? null,
                        'quality_lov_color_y' => $detail['quality_lov_color_y'] ?? null,
                        'quality_cp_temp' => $detail['quality_cp_temp'] ?? null,
                        'quality_smp' => $detail['quality_smp'] ?? null,
                        'quality_pv' => $detail['quality_pv'] ?? null,
                        'quality_iv' => $detail['quality_iv'] ?? null,
                        'remark' => $detail['remark'] ?? null,
                    ]);
                }
            }

            // Update control number
            DB::table('m_controlnumber')
                ->where('prefix', $control->prefix)
                ->update(['autonumber' => $nextNum]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Form Transfer created successfully',
                'data' => [
                    'header_id' => $header->id,
                    'detail_ids' => $header->details->pluck('id'),
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create Form Transfer',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * PUT /api/formtransfer/{id}
     * Update Form Transfer record
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $user = $request->user()->getDisplayNameAttribute();
            $data = $request->all();

            $header = LSFormTransferHeader::with('details')->findOrFail($id);

            // Update header
            $header->update([
                'transaction_date' => $data['transaction_date'] ?? $header->transaction_date,
                'to_dept' => $data['to_dept'] ?? $header->to_dept,
                'from_dept' => $data['from_dept'] ?? $header->from_dept,
                'flag' => $data['flag'] ?? 'U',
                'updated_by' => $user,
                'updated_date' => now(),
                'revision_no' => DB::raw('COALESCE(revision_no, 0) + 1'),
                'revision_date' => now(),
            ]);

            // Helper to get next detail ID
            $nextDetailId = function ($headerId) {
                $max = 0;
                $rows = LSFormTransferDetail::where('id_hdr', $headerId)->pluck('id')->toArray();
                foreach ($rows as $rid) {
                    if (preg_match('/D(\d+)$/', $rid, $m)) {
                        $num = intval($m[1]);
                        if ($num > $max) {
                            $max = $num;
                        }
                    }
                }
                return $headerId . 'D' . str_pad($max + 1, 3, '0', STR_PAD_LEFT);
            };

            // Sync details
            if (isset($data['details']) && is_array($data['details'])) {
                $existingIds = $header->details->pluck('id')->toArray();
                $receivedIds = [];

                foreach ($data['details'] as $row) {
                    if (!empty($row['id'])) {
                        // Update existing detail
                        $detail = LSFormTransferDetail::where('id', $row['id'])
                            ->where('id_hdr', $header->id)
                            ->first();

                        if (!$detail) {
                            DB::rollBack();
                            return response()->json([
                                'success' => false,
                                'message' => "Detail id {$row['id']} not found for header {$header->id}."
                            ], 404);
                        }

                        $detail->update([
                            'oil_type' => $row['oil_type'] ?? $detail->oil_type,
                            'quantity' => $row['quantity'] ?? $detail->quantity,
                            'from_storage_tank_no' => $row['from_storage_tank_no'] ?? $detail->from_storage_tank_no,
                            'from_refinery_fractionation' => $row['from_refinery_fractionation'] ?? $detail->from_refinery_fractionation,
                            'from_other' => $row['from_other'] ?? $detail->from_other,
                            'to_storage_tank_no' => $row['to_storage_tank_no'] ?? $detail->to_storage_tank_no,
                            'to_refinery_fractionation' => $row['to_refinery_fractionation'] ?? $detail->to_refinery_fractionation,
                            'to_auto_filling_tank' => $row['to_auto_filling_tank'] ?? $detail->to_auto_filling_tank,
                            'to_other' => $row['to_other'] ?? $detail->to_other,
                            'quality_m_and_i' => $row['quality_m_and_i'] ?? $detail->quality_m_and_i,
                            'quality_ffa' => $row['quality_ffa'] ?? $detail->quality_ffa,
                            'quality_lov_color_r' => $row['quality_lov_color_r'] ?? $detail->quality_lov_color_r,
                            'quality_lov_color_y' => $row['quality_lov_color_y'] ?? $detail->quality_lov_color_y,
                            'quality_cp_temp' => $row['quality_cp_temp'] ?? $detail->quality_cp_temp,
                            'quality_smp' => $row['quality_smp'] ?? $detail->quality_smp,
                            'quality_pv' => $row['quality_pv'] ?? $detail->quality_pv,
                            'quality_iv' => $row['quality_iv'] ?? $detail->quality_iv,
                            'remark' => $row['remark'] ?? $detail->remark,
                        ]);

                        $receivedIds[] = $detail->id;
                    } else {
                        // Create new detail
                        $newId = $nextDetailId($header->id);

                        $created = LSFormTransferDetail::create([
                            'id' => $newId,
                            'id_hdr' => $header->id,
                            'oil_type' => $row['oil_type'] ?? null,
                            'quantity' => $row['quantity'] ?? null,
                            'from_storage_tank_no' => $row['from_storage_tank_no'] ?? null,
                            'from_refinery_fractionation' => $row['from_refinery_fractionation'] ?? null,
                            'from_other' => $row['from_other'] ?? null,
                            'to_storage_tank_no' => $row['to_storage_tank_no'] ?? null,
                            'to_refinery_fractionation' => $row['to_refinery_fractionation'] ?? null,
                            'to_auto_filling_tank' => $row['to_auto_filling_tank'] ?? null,
                            'to_other' => $row['to_other'] ?? null,
                            'quality_m_and_i' => $row['quality_m_and_i'] ?? null,
                            'quality_ffa' => $row['quality_ffa'] ?? null,
                            'quality_lov_color_r' => $row['quality_lov_color_r'] ?? null,
                            'quality_lov_color_y' => $row['quality_lov_color_y'] ?? null,
                            'quality_cp_temp' => $row['quality_cp_temp'] ?? null,
                            'quality_smp' => $row['quality_smp'] ?? null,
                            'quality_pv' => $row['quality_pv'] ?? null,
                            'quality_iv' => $row['quality_iv'] ?? null,
                            'remark' => $row['remark'] ?? null,
                        ]);

                        $receivedIds[] = $created->id;
                    }
                }

                // Delete removed details
                $toDelete = array_diff($existingIds, $receivedIds);
                if (!empty($toDelete)) {
                    LSFormTransferDetail::whereIn('id', $toDelete)->delete();
                }
            }

            DB::commit();

            $header->refresh();
            $header->load('details');

            return response()->json([
                'success' => true,
                'message' => 'Form Transfer updated successfully',
                'data' => $header,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Record not found.'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /api/formtransfer/{id}
     * Soft delete Form Transfer record
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $header = LSFormTransferHeader::withTrashed()->findOrFail($id);

            if ($header->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Form Transfer record is already deleted.',
                ], 400);
            }

            $header->delete();
            LSFormTransferDetail::where('id_hdr', $id)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Form Transfer record has been deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Form Transfer record not found.',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Form Transfer record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/formtransfer/{id}/restore
     * Restore soft-deleted Form Transfer record
     */
    public function restore($id)
    {
        try {
            $header = LSFormTransferHeader::onlyTrashed()->findOrFail($id);
            $header->restore();

            LSFormTransferDetail::onlyTrashed()
                ->where('id_hdr', $id)
                ->restore();

            return response()->json([
                'success' => true,
                'message' => 'Form Transfer record has been restored successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Form Transfer record not found or not trashed',
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore Form Transfer record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /api/formtransfer/{id}/force
     * Permanently delete Form Transfer record
     */
    public function forceDelete($id)
    {
        try {
            DB::beginTransaction();

            $header = LSFormTransferHeader::withTrashed()->findOrFail($id);

            LSFormTransferDetail::where('id_hdr', $id)->forceDelete();
            $header->forceDelete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Form Transfer record has been permanently deleted.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Form Transfer record not found',
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to permanently delete Form Transfer record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * PUT /api/formtransfer/{id}/approval
     * Update approval status of Form Transfer record
     *
     * 2-Step Approval Workflow:
     * - prepared_status: Lead roles (LEAD, LEAD_QC)
     * - approved_status: Manager roles (MGR, MGR_QC, ADM)
     */
    public function updateApproval(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $header = LSFormTransferHeader::findOrFail($id);
            $user = Auth::user();
            $status = Str::ucfirst(strtolower($request->input('status')));
            $remark = $request->input('remarks');
            $level = $request->input('level'); // prepared, approved

            if (!$level || !in_array($level, ['prepared', 'approved'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid approval level. Must be: prepared or approved',
                ], 400);
            }

            $isSuccess = $this->processApprovalStatus($header, $level, $status, $remark, $user->username, $user->roles);

            if (!$isSuccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update this approval level',
                ], 403);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Approval status updated successfully',
                'data' => [
                    'id' => $header->id,
                    'level' => $level,
                    'status' => $status,
                    'remarks' => $remark,
                    'updated_by' => $user->username,
                    'updated_at' => now()->toDateTimeString(),
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Record not found',
            ], 404);
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
     * 2-Step Approval: prepared (Lead) → approved (Manager)
     *
     * @param LSFormTransferHeader $header
     * @param string $level - prepared, approved
     * @param string $status
     * @param string|null $remark
     * @param string $username
     * @param string $userRoles
     * @return bool
     */
    private function processApprovalStatus($header, $level, $status, $remark, $username, $userRoles)
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

        // Verify the level matches the role
        if ($level !== $fieldPrefix) {
            return false;
        }

        $header->update([
            "{$fieldPrefix}_status"         => $status,
            "{$fieldPrefix}_by"             => $username,
            "{$fieldPrefix}_role"           => json_encode($userRoles),
            "{$fieldPrefix}_date"           => now(),
            "{$fieldPrefix}_status_remarks" => $remark,
        ]);

        return true;
    }
}
