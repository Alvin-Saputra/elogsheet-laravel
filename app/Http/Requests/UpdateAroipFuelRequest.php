<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAroipFuelRequest extends FormRequest
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
            // ROA Update Fields
            'roa' => 'sometimes|array',
            'roa.report_no' => 'sometimes|string|max:100',
            'roa.shipper' => 'nullable|string|max:100',
            'roa.buyer' => 'nullable|string|max:100',
            'roa.date_received' => 'sometimes|date',
            'roa.date_analyzed_start' => 'sometimes|date',
            'roa.date_analyzed_end' => 'sometimes|date',
            'roa.date_reported' => 'sometimes|date',
            'roa.lab_sample_id' => 'sometimes|string|max:100',
            'roa.customer_sample_id' => 'sometimes|string|max:100',
            'roa.seal_no' => 'nullable|string|max:100',
            'roa.weight_of_received_sample' => 'nullable|numeric',
            'roa.top_size_of_received_sample' => 'nullable|numeric',
            'roa.hardgrove_grindability_index' => 'nullable|numeric',

            // ROA Details
            'roa.details' => 'sometimes|array|min:1',
            'roa.details.*.parameter' => 'required_with:roa.details|string|max:45',
            'roa.details.*.unit' => 'nullable|string|max:20',
            'roa.details.*.basis' => 'nullable|string|max:6',
            'roa.details.*.result' => 'nullable|numeric',
            // Analytical Update Fields
            'analytical' => 'sometimes|array',
            'analytical.date' => 'sometimes|date',
            'analytical.material' => 'nullable|string|max:45',
            'analytical.quantity' => 'nullable|numeric',
            'analytical.supplier' => 'nullable|string|max:45',
            'analytical.police_no' => 'nullable|string|max:45',
            'analytical.analyst' => 'nullable|string|max:45',   
            'analytical.form_no' => 'nullable|string|max:45',
            'analytical.date_issued' => 'nullable|date',
            'analytical.revision_no' => 'nullable|integer',
            'analytical.revision_date' => 'nullable|date',
            // Analytical Details
            'analytical.details' => 'sometimes|array|min:1',
            'analytical.details.*.parameter' => 'required_with:analytical.details|string|max:45',
            'analytical.details.*.result' => 'nullable|numeric',
            'analytical.details.*.specification' => 'nullable|numeric',
            'analytical.details.*.status_ok' => 'nullable|string|in:Y,N',
            'analytical.details.*.remark' => 'nullable|string|max:100',
        ];
    }
}
