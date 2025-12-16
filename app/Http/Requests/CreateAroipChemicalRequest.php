<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAroipChemicalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [

            /*
            |------------------------------------------------------------------
            | COA (optional, required only if coa_id is missing)
            |------------------------------------------------------------------
            */
            'coa' => 'required_without:coa_id|array',

            'coa.no_doc' => 'required_with:coa|string|max:100|unique:coa_incoming_plant_chemical_ingredient_header,no_doc',
            'coa.product' => 'required_with:coa|string|max:45',
            'coa.grade' => 'required_with:coa|string|max:45',
            'coa.packing' => 'required_with:coa|string|max:45',
            'coa.quantity' => 'required_with:coa|numeric',
            'coa.tanggal_pengiriman' => 'required_with:coa|date',
            'coa.vehicle' => 'required_with:coa|string|max:45',
            'coa.lot_no' => 'required_with:coa|string|max:45',
            'coa.production_date' => 'required_with:coa|date',
            'coa.expired_date' => 'required_with:coa|date',

            'coa.details' => 'required_with:coa|array|min:1',
            'coa.details.*.parameter' => 'required_with:coa|string|max:45',
            'coa.details.*.actual_min' => 'required_with:coa|numeric|min:0',
            'coa.details.*.actual_max' => 'nullable|numeric',
            'coa.details.*.standard_min' => 'nullable|numeric',
            'coa.details.*.standard_max' => 'nullable|numeric',
            'coa.details.*.method' => 'required_with:coa|string|max:45',

            /*
            |------------------------------------------------------------------
            | Existing COA reference
            |------------------------------------------------------------------
            */
            'coa_id' => 'required_without:coa|string|exists:coa_incoming_plant_chemical_ingredient_header,id',

            /*
            |------------------------------------------------------------------
            | Analytical Result (always required)
            |------------------------------------------------------------------
            */
            'analytical' => 'required|array',

            'analytical.material' => 'required|string|max:45',
            'analytical.received_quantity' => 'required|numeric',
            'analytical.analyst' => 'required|string|max:45',
            'analytical.supplier' => 'required|string|max:45',
            'analytical.police_no' => 'required|string|max:45',
            'analytical.batch_lot' => 'required|string|max:45',
            'analytical.status' => 'required|string|max:45',

            'analytical.form_no' => 'required|string|max:45',
            'analytical.revision_no' => 'required|integer',
            'analytical.revision_date' => 'required|date',

            'analytical.details' => 'required|array|min:1',
            'analytical.details.*.parameter' => 'required|string|max:45',
            'analytical.details.*.specification_min' => 'required|numeric',
            'analytical.details.*.specification_max' => 'required|numeric',
            'analytical.details.*.result_min' => 'required|numeric',
            'analytical.details.*.result_max' => 'nullable|numeric',
            'analytical.details.*.status_ok' => 'required|in:Y,N',
            'analytical.details.*.remark' => 'nullable|string|max:100',
        ];
    }
}
