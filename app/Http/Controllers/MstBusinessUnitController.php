<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MBusinessUnit;

class MstBusinessUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = MBusinessUnit::query();

        if ($request->filled('kode')) {
            $query->where('bu_code', 'like', '%' . $request->kode . '%');
        }

        $businessUnits = $query->paginate(10);
        if ($request->expectsJson()) {
            $businessUnits = $query->get()->toArray();
            return response()->json([
                'success' => true,
                'data' => $businessUnits
            ]);
        }

        return view('mst_business_unit.index', compact('businessUnits'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('mst_business_unit.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'bu_code' => 'required|string|max:10|unique:m_business_unit,bu_code',
            'bu_name' => 'required|string|max:100',
            'isactive' => 'required|in:T,F',
        ]);

        MBusinessUnit::create($request->only(['bu_code', 'bu_name', 'isactive']));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'bu_code' => $request['bu_code']
                ]
            ]);
        }

        return redirect()->route('business-unit.index')->with('success', 'Business Unit berhasil ditambahkan.');
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
    public function edit($bu_code)
    {
        $unit = MBusinessUnit::findOrFail($bu_code);
        return view('mst_business_unit.edit', compact('unit'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $bu_code)
    {
        $request->validate([
            'bu_code' => 'required|string|max:10',
            'bu_name' => 'required|string|max:100',
            'isactive' => 'required|in:T,F',
        ]);

        $unit = MBusinessUnit::findOrFail($bu_code);
        $unit->update([
            'bu_code' => $request->bu_code,
            'bu_name' => $request->bu_name,
            'isactive' => $request->isactive,
        ]);
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
            ]);
        }
        return redirect()->route('business-unit.index')->with('success', 'Business Unit berhasil diupdate');
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $bu_code)
    {
        $unit = MBusinessUnit::findOrFail($bu_code);
        $unit->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
            ]);
        }

        return redirect()->route('business-unit.index')->with('success', 'Data berhasil dihapus');
    }

    // public function export()
    // {
    //     return Excel::download(new BusinessUnitExport, 'business_units.xlsx');
    // }
}
