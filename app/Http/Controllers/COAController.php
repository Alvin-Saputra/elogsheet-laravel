<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCoaRequest;
use DateTime;
use App\Models\COADetail;
use App\Models\COAHeader;
use Illuminate\Http\Request;
use App\Models\MControlnumber;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\UpdateCoaRequest;
use Illuminate\Support\Facades\Validator;

class COAController extends Controller
{
    // mobile CRUD
    // web view approve view table, export (table), preview (sebelum expoert), details, filter (tanggal aja)

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
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

    // ----public function----

    // ----API Request Function----

    /**
     * POST /api/coa-plant-chemical
     * Create new certificate of analysis data
     */
    public function create(CreateCoaRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = $request->user()->getDisplayNameAttribute();
            $validated = $request->validated();

            $details = $validated['detail'];
            unset($validated['detail']);


            $prefix = 'Q11';

            $controlRow = DB::table('m_controlnumber')
                ->where('prefix', $prefix)
                ->lockForUpdate()
                ->first();

            if (!$controlRow) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'error' => 'CONTROL_NUMBER_NOT_FOUND',
                    'message' => "No m_controlnumber row found for prefix {$prefix}",
                ], 400);
            }

            $autonum = (int) ($controlRow->autonumber ?? 0);

            do {
                $autonum++;
                $padded = str_pad($autonum, 6, '0', STR_PAD_LEFT);
                $header_id = $prefix . now()->format('y') . $padded;
                $exists = COAHeader::where('id', $header_id)->exists();
            } while ($exists);

            $headerPayload = array_merge(
                ['id' => $header_id],
                $validated,
                [
                    'issue_by' => $user,
                    'issue_date' => now(),
                    'updated_by' => $user,
                    'updated_date' => now(),
                ]
            );

            $header = COAHeader::create($headerPayload);

            DB::table('m_controlnumber')
                ->where('prefix', $prefix)
                ->update(['autonumber' => $autonum]);

            $detailIds = [];
            foreach ($details as $index => $det) {
                $detArr = (array) $det;
                $detailId = $header->id . 'D' . ($index + 1);

                $detailPayload = array_merge($detArr, [
                    'id_hdr' => $header->id,
                    'id' => $detailId,
                ]);

                $detailRecord = COADetail::create($detailPayload);
                $detailIds[] = $detailRecord->id;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'header_id' => $header_id,
                'detail_ids' => $detailIds,
            ], 201);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'data' => $th->getMessage(),
            ], 500);
        }
    }


    /**
     * GET /api/coa-plant-chemical
     * Get all COA data with issue_date filter option
     */
    public function get(Request $request)
    {
        $query = COAHeader::with('details');
        if ($request->filled('issue_date')) {
            $query->whereDate('issue_date', $request->issue_date);
        }

        $result = $query->get();

        if ($request->filled('issue_date') && $result->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No data found for the given filter.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    /**
     * PUT /api/coa-plant-chemical/{id}
     * Update Header and Sync Details
     */
    public function update(UpdateCoaRequest $request, $id)
    {
        $header = COAHeader::find($id);

        if (!$header) {
            return response()->json([
                'status' => false,
                'message' => 'COA not found'
            ], 404);
        }

        $data = $request->validated();

        DB::beginTransaction();

        try {
            $userDisplayName = $request->user()->getDisplayNameAttribute();


            $header->update([
                'no_doc' => $data['no_doc'],
                'product' => $data['product'],
                'grade' => $data['grade'],
                'packing' => $data['packing'],
                'quantity' => $data['quantity'],
                'tanggal_pengiriman' => $data['tanggal_pengiriman'],
                'vehicle' => $data['vehicle'],
                'lot_no' => $data['lot_no'],
                'production_date' => $data['production_date'],
                'expired_date' => $data['expired_date'],
                'updated_by' => $userDisplayName,
                'updated_date' => now(),
            ]);


            COADetail::where('id_hdr', $id)->delete();

            $newDetails = [];
            $detailIds = [];

            foreach ($request->detail as $index => $item) {
                $detailId = $id . 'D' . ($index + 1);

                $newDetails[] = [
                    'id' => $detailId,
                    'id_hdr' => $id,
                    'parameter' => $item['parameter'],
                    'actual_min' => $item['actual_min'],
                    'actual_max' => $item['actual_max'] ?? null,
                    'standard_min' => $item['standard_min'] ?? null,
                    'standard_max' => $item['standard_max'] ?? null,
                    'method' => $item['method'] ?? null,
                ];

                $detailIds[] = $detailId;
            }

            COADetail::insert($newDetails);

            DB::commit();

            return response()->json([
                'success' => true,
                'id_header' => $header->id,
                'id_det' => $detailIds,
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // TODO: Implement delete logic for COA after AROIPChemicalController delete flow is finalized.
    public function destroy(Request $request, $id)
    {
        //
    }
}
