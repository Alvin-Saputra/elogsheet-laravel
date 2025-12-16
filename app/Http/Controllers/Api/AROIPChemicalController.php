<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateAroipChemicalRequest;
use App\Models\AROIPChemicalDetail;
use App\Models\AROIPChemicalHeader;
use App\Models\COADetail;
use App\Models\COAHeader;
use App\Models\MControlnumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AROIPChemicalController extends Controller
{
    public function get()
    {
        //
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

            $willCreateCoa = !empty($data['coa']);
            $willUseExistingCoa = !empty($data['coa_id']);

            if (!$willCreateCoa && !$willUseExistingCoa) {
                throw new \RuntimeException('Either coa or coa_id must be provided.');
            }

            $prefix = 'Q11';
            $control = MControlnumber::where('prefix', $prefix)
                ->lockForUpdate()
                ->first();

            if (!$control) {
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
                $suffix = 'COA' . $plant . $year . $padded;
                $coaId = ($control->prefix ?? $prefix) . $suffix;

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
                    $detailId = ($control->prefix ?? $prefix) . 'D' . $suffix . $i;
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
                if (!$coa) {
                    throw new \RuntimeException('COA_NOT_FOUND');
                }
            }


            $nextNum += 1;
            $paddedAro = str_pad($nextNum, $padLen, '0', STR_PAD_LEFT);
            $suffixAroip = $plant . $year . $paddedAro;
            $aroipHeaderId = ($control->prefix ?? $prefix) . $suffixAroip;


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
                'date_issued' => $analytical['date_issued'],
                'revision_no' => $analytical['revision_no'],
                'revision_date' => $analytical['revision_date'],
            ];

            $aroipHeader = AROIPChemicalHeader::create($headerPayload);

            $detailIds = [];
            foreach ($analytical['details'] as $i => $row) {
                $detailId = ($control->prefix ?? $prefix) . 'D' . $suffixAroip . $i;

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
                ->when($control->plantid, fn($q) => $q->where('plantid', $control->plantid))
                ->update(['autonumber' => $nextNum]);

            DB::commit();

            return response()->json([
                'success' => true,
                'coa_created' => $willCreateCoa,
                'coa_id' => $coa->id,
                'coa_no_doc' => $coa->no_doc,
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

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
