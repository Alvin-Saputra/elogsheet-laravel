<?php

namespace App\Http\Controllers;

use DateTime;
use App\Models\COADetail;
use App\Models\COAHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\MControlnumber;

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

    // create new COA
    public function create(Request $request)
    {
        try {
            DB::beginTransaction();

            // Use accessor as virtual attribute
            $user = $request->user()->getDisplayNameAttribute();

            // Validate request (matches your rules)
            $validated = $request->validate([
                'no_doc' => 'required|unique:coa_incoming_plant_chemical_ingredient_header|max:100',
                'product' => 'required|max:45',
                'grade' => 'required|max:45',
                'packing' => 'required',
                'quantity' => 'required|numeric',
                'tanggal_pengiriman' => 'required|date',
                'vehicle' => 'required|max:45',
                'lot_no' => 'required|max:45',
                'production_date' => 'required|date',
                'expired_date' => 'required|date',
                'detail' => 'required|array|min:1',
                'detail.*.parameter' => 'required|max:45',
                'detail.*.actual_min' => 'required|numeric|min:0',
                'detail.*.actual_max' => 'required|numeric',
                'detail.*.standard_min' => 'required|numeric|min:0',
                'detail.*.standard_max' => 'required|numeric',
                'detail.*.method' => 'required|max:45'
            ]);

            // pull details out
            $details = $validated['detail'];
            unset($validated['detail']);

            // --- SAFE ID GENERATION ---
            $prefix = 'Q11';

            // lock the control row for this prefix so concurrent requests are serialized
            $controlRow = DB::table('m_controlnumber')
                ->where('prefix', $prefix)
                ->lockForUpdate()
                ->first();

            if (!$controlRow) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'error' => 'CONTROL_NUMBER_NOT_FOUND',
                    'message' => "No m_controlnumber row found for prefix {$prefix}"
                ], 400);
            }

            // start from current autonumber (may be null or zero)
            $autonum = intval($controlRow->autonumber ?? 0);

            // loop until we find a header_id that's not used yet (safe in presence of mismatched data)
            do {
                $autonum++;
                $padded = str_pad($autonum, 6, '0', STR_PAD_LEFT);
                $header_id = $prefix . now()->format('y') . $padded;
                $exists = COAHeader::where('id', $header_id)->exists();
            } while ($exists);

            // Build header payload (use validated fields)
            $headerPayload = array_merge(
                ['id' => $header_id],
                $validated,
                [
                    'issue_by' => $user,
                    'issue_date' => now(),
                ]
            );

            // create header
            $header = COAHeader::create($headerPayload);

            // update control autonumber (raw query avoids model PK issues)
            DB::table('m_controlnumber')
                ->where('prefix', $prefix)
                ->update(['autonumber' => $autonum]);

            // insert details
            $detailIds = [];
            foreach ($details as $index => $det) {
                $detArr = (array) $det; // ensure array
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
                'data' => $th->getMessage()
            ], 500);
        }
    }
    // get all coa data + issue_date filter
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

    // update data by id
    public function update(Request $request, COAHeader $header)
    {
        try {
            $user = $request->user()->getDisplayNameAttribute();
            $validated = $request->validate([
                'no_doc' => 'required|max:100|unique:coa_incoming_plant_chemical_ingredient_header,no_doc,' . $header->id . ',id',
                'product' => 'required|max:45',
                'grade' => 'required|max:45',
                'packing' => 'required',
                'quantity' => 'required|numeric',
                'tanggal_pengiriman' => 'required|date',
                'vehicle' => 'required|max:45',
                'lot_no' => 'required|max:45',
                'production_date' => 'required|date',
                'expired_date' => 'required|date',
                'detail' => 'required|array|min:1',
                'detail.*.parameter' => 'required|max:45',
                'detail.*.actual_min' => 'required|numeric|min:0',
                'detail.*.actual_max' => 'required|numeric',
                'detail.*.standard_min' => 'required|numeric|min:0',
                'detail.*.standard_max' => 'required|numeric',
                'detail.*.method' => 'required|max:45'
            ]);
            DB::transaction(function () use ($validated, $header, $user) {
                $details = $validated['detail'];
                unset($validated['detail']);

                $header->update(array_merge(
                    $validated,
                    [
                        'updated_by' => $user,
                        'updated_date' => now(),
                    ]
                ));

                $existingIds = $header->details()->pluck('id')->toArray();
                $processedIds = [];

                $header->details()->delete();

                foreach ($details as $index => $det) {
                    $detailId = $header->id . 'D' . ($index + 1);

                    $header->details()->create([
                        'id' => $detailId,
                        'id_hdr' => $header->id,
                        'parameter' => $det['parameter'],
                        'actual_min' => $det['actual_min'],
                        'actual_max' => $det['actual_max'],
                        'standard_min' => $det['standard_min'],
                        'standard_max' => $det['standard_max'],
                        'method' => $det['method'],
                    ]);
                }

            });
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'data' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy(Request $request, $id)
    {
        //
    }
}
