<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateFormTransferRequest extends FormRequest
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
            
            'details' => 'required|array|min:1',

            'details.*.oil_type' => 'nullable|string|max:45',
            'details.*.quantity' => 'nullable|string|max:45',

            'details.*.form_storage_tank_no' => 'nullable|string|max:45',
            'details.*.form_refinery_fractionation' => 'nullable|string|max:45',
            'details.*.form_other' => 'nullable|string|max:45',

            'details.*.to_storage_tank_no' => 'nullable|string|max:45',
            'details.*.to_refinery_fractionation' => 'nullable|string|max:45',
            'details.*.to_auto_filing_tank' => 'nullable|integer',
            'details.*.to_other' => 'nullable|string|max:45',

            'details.*.quality_m_and_i' => 'nullable|numeric',
            'details.*.quality_ffa' => 'nullable|numeric',
            'details.*.quality_lov_color_r' => 'nullable|numeric',
            'details.*.quality_lov_color_y' => 'nullable|numeric',
            'details.*.quality_cp_temp' => 'nullable|numeric',
            'details.*.quality_smp' => 'nullable|numeric',
            'details.*.quality_pv' => 'nullable|numeric',
            'details.*.quality_iv' => 'nullable|numeric',

            'details.*.remark' => 'nullable|string|max:45',
        ];
    }
}
