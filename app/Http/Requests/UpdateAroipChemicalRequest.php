<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'material' => 'sometimes|string|max:100',
            'quantity' => 'sometimes|numeric',
            'analyst' => 'sometimes|string|max:100',
            'supplier' => 'sometimes|string|max:100',
            'police_no' => 'sometimes|string|max:20|nullable',
            'batch_lot' => 'sometimes|string|max:100',
            'status' => 'sometimes|string|max:45',
            'form_no' => 'sometimes|string|max:45',
            'revision_no' => 'sometimes|integer',
            'revision_date' => 'sometimes|date',
            'details' => 'sometimes|array',
            'details.*.id' => 'required_with:details|string',
            'details.*.parameter' => 'required_with:details|string|max:100',
            'details.*.specification_min' => 'required_with:details|numeric',
            'details.*.specification_max' => 'required_with:details|numeric',
            'details.*.result_min' => 'required_with:details|numeric|nullable',
            'details.*.result_max' => 'nullable|numeric',
            'details.*.status_ok' => 'required_with:details|string|in:Y,N',
            'details.*.remark' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'details.*.parameter.required_with' => 'The parameter field is required for all detail items.',
            'details.*.specification_min.required_with' => 'The specification min field is required for all detail items.',
            'details.*.specification_max.required_with' => 'The specification max field is required for all detail items.',
            'details.*.status_ok.in' => 'The status must be either Y or N.',
        ];
    }
}
