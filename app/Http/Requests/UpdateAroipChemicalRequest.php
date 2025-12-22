<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\AROIPChemicalHeader; // Pastikan Model di-import

class UpdateAroipChemicalRequest extends FormRequest
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
        $aroipId = $this->route('id');
        $uniqueIgnore = '';

        if ($aroipId) {
            $aroip = AROIPChemicalHeader::find($aroipId);
            if ($aroip && $aroip->id_coa) {
                $uniqueIgnore = ',' . $aroip->id_coa . ',id';
            }
        }

        return [
            // AROIP header fields (root-level or grouped under 'analytical')
            'material' => 'sometimes|string|max:100',
            'quantity' => 'sometimes|numeric',
            'analyst' => 'sometimes|string|max:100',
            'supplier' => 'sometimes|string|max:100',
            'police_no' => 'sometimes|string|max:20|nullable',
            'batch_lot' => 'sometimes|string|max:100',
            'status' => 'sometimes|string|max:45',
            'form_no' => 'sometimes|string|max:45',

            // Analytical / AROIP grouping support
            'analytical' => 'sometimes|array',
            'analytical.date' => 'sometimes|date',
            'analytical.exp_date' => 'sometimes|date',
            'analytical.no_ref_coa' => 'sometimes|string|max:100',
            'analytical.material' => 'sometimes|string|max:100',
            'analytical.received_quantity' => 'sometimes|numeric',
            'analytical.analyst' => 'sometimes|string|max:100',
            'analytical.supplier' => 'sometimes|string|max:100',
            'analytical.police_no' => 'sometimes|string|max:20|nullable',
            'analytical.batch_lot' => 'sometimes|string|max:100',
            'analytical.status' => 'sometimes|string|max:45',

            // AROIP (analytical) details - on update we require id for existing rows.
            // Use the real DB table name for exists rule.
            'details' => 'sometimes|array',
            'details.*.id' => 'required_with:details|string|exists:t_analytical_result_incoming_plant_chemical_ingredient_detail,id',
            'details.*.parameter' => 'required_with:details|string|max:100',
            'details.*.specification_min' => 'required_with:details|numeric',
            'details.*.specification_max' => 'required_with:details|numeric',
            'details.*.result_min' => 'nullable|numeric',
            'details.*.result_max' => 'nullable|numeric',
            'details.*.status_ok' => 'required_with:details|string|in:Y,N',
            'details.*.remark' => 'nullable|string',

            'analytical.details' => 'sometimes|array',
            'analytical.details.*.id' => 'required_with:analytical.details|string|exists:t_analytical_result_incoming_plant_chemical_ingredient_detail,id',
            'analytical.details.*.parameter' => 'required_with:analytical.details|string|max:100',
            'analytical.details.*.specification_min' => 'required_with:analytical.details|numeric',
            'analytical.details.*.specification_max' => 'required_with:analytical.details|numeric',
            'analytical.details.*.result_min' => 'nullable|numeric',
            'analytical.details.*.result_max' => 'nullable|numeric',
            'analytical.details.*.status_ok' => 'required_with:analytical.details|string|in:Y,N',
            'analytical.details.*.remark' => 'nullable|string',

            // COA block
            'coa' => 'sometimes|array',
            'coa.no_doc' => 'required_with:coa|max:100|unique:coa_incoming_plant_chemical_ingredient_header,no_doc' . $uniqueIgnore,
            'coa.product' => 'required_with:coa|max:45',
            'coa.grade' => 'required_with:coa|max:45',
            'coa.packing' => 'required_with:coa',
            'coa.quantity' => 'required_with:coa|numeric',
            'coa.tanggal_pengiriman' => 'required_with:coa|date',
            'coa.vehicle' => 'required_with:coa|max:45',
            'coa.lot_no' => 'required_with:coa|max:45',
            'coa.production_date' => 'required_with:coa|date',
            'coa.expired_date' => 'required_with:coa|date',

            // COA details - use the real COA detail table for exists rule
            'coa.details' => 'required_with:coa|array|min:1',
            'coa.details.*.id' => 'sometimes|string|exists:coa_incoming_plant_chemical_ingredient_detail,id',
            'coa.details.*.parameter' => 'required_with:coa.details|max:45',
            'coa.details.*.actual_min' => 'required_with:coa.details|numeric|min:0',
            'coa.details.*.actual_max' => 'nullable|numeric',
            'coa.details.*.standard_min' => 'nullable|numeric',
            'coa.details.*.standard_max' => 'nullable|numeric',
            'coa.details.*.method' => 'nullable|max:45',
        ];
    }

    public function messages(): array
    {
        return [
            'details.*.id.required_with' => 'Each analytical detail must include its id when updating.',
            'analytical.details.*.id.required_with' => 'Each analytical detail must include its id when updating.',
            'details.*.parameter.required_with' => 'The parameter field is required for all analytical detail items.',
            'details.*.specification_min.required_with' => 'The specification min field is required for all analytical detail items.',
            'details.*.specification_max.required_with' => 'The specification max field is required for all analytical detail items.',
            'details.*.status_ok.in' => 'The status must be either Y or N.',

            'coa.no_doc.unique' => 'The COA Document Number has already been used by another record.',
            'coa.no_doc.required_with' => 'COA Document Number is required.',
            'coa.details.required_with' => 'COA details are required when updating COA data.',
            'coa.details.*.parameter.required_with' => 'Parameter field in COA details is required.',
            'coa.details.*.actual_min.required_with' => 'Actual Min field in COA details is required.',
            'coa.details.*.id.exists' => 'Provided COA detail id does not exist.',
        ];
    }
}