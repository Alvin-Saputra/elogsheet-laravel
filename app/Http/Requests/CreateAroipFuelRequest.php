<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAroipFuelRequest extends FormRequest
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
           | roa (optional, required only if roa_id is missing)
           |------------------------------------------------------------------
           */
            'roa' => 'required|array',

            // roa fields (if creating new roa)
            // 'roa.no_doc' => 'required_with:roa|string|unique:roa_incoming_plant_chemical_ingredient_header,no_doc',

            'roa.report_no' => 'required_with:roa|string|max:45',
            'roa.shipper' => 'required_with:roa|string|max:45',
            'roa.buyer' => 'required_with:roa|string|max:45',

            'roa.date_received' => 'required_with:roa|date',
            'roa.date_analyzed_start' => 'required_with:roa|date',
            'roa.date_analyzed_end' => 'required_with:roa|date',
            'roa.date_reported' => 'required_with:roa|date',
            'roa.lab_sample_id' => 'required_with:roa|string|max:45',
            'roa.customer_sample_id' => 'required_with:roa|string|max:45',
            'roa.seal_no' => 'required_with:roa|string|max:45',
            'roa.weight_of_received_sample' => 'required_with:roa|numeric',
            'roa.top_size_of_received_sample' => 'required_with:roa|numeric',
            'roa.hardgrove_grindability_index' => 'required_with:roa|numeric',
            'roa.details' => 'required_with:roa|array|min:1',
            'roa.details.*.parameter' => 'required_with:roa|string|max:45',
            'roa.details.*.unit' => 'nullable|string|min:1',
            'roa.details.*.basis' => 'nullable|string|min:1',
            'roa.details.*.result' => 'nullable|numeric',

            /*
           |------------------------------------------------------------------
           | Existing roa reference
           |------------------------------------------------------------------
           */
            // 'roa_no_doc' => 'required_without:roa|string|exists:roa_incoming_plant_chemical_ingredient_header,no_doc',

            /*
           |------------------------------------------------------------------
           | Analytical Result (always required)
           |------------------------------------------------------------------
           */
            'analytical' => 'required|array',
            // 'analytical.no_ref_roa' => 'required|string|max:100',
            'analytical.date' => 'required|date',
            'analytical.material' => 'required|string|max:45',
            'analytical.quantity' => 'required|numeric',
            'analytical.supplier' => 'required|string|max:45',
            'analytical.police_no' => 'required|string|max:45',
            'analytical.analyst' => 'required|string|max:45',


            'analytical.details' => 'required|array|min:1',
            'analytical.details.*.result' => 'required|numeric',
            'analytical.details.*.parameter' => 'required|string|max:45',
            'analytical.details.*.specification' => 'required|numeric',
            'analytical.details.*.status_ok' => 'required|in:Y,N',
            'analytical.details.*.remark' => 'nullable|string|max:100',
        ];
    }
}
