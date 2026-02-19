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
            /*
            |------------------------------------------------------------------
            | Header Fields
            |------------------------------------------------------------------
            */
            'transaction_date' => 'nullable|date',
            'company' => 'required|string|max:45',
            'plant' => 'required|string|max:45',
            'to_dept' => 'nullable|string|max:45',
            'from_dept' => 'nullable|string|max:45',
            'flag' => 'nullable|in:I,U',

            /*
            |------------------------------------------------------------------
            | Details Array
            |------------------------------------------------------------------
            */
            'details' => 'required|array|min:1',

            /*
            |------------------------------------------------------------------
            | Detail Item Fields
            |------------------------------------------------------------------
            */
            'details.*.oil_type' => 'required|string|max:45',
            'details.*.quantity' => 'required|numeric',
            'details.*.from_storage_tank_no' => 'nullable|string|max:45',
            'details.*.from_refinery_fractionation' => 'nullable|string|max:45',
            'details.*.from_other' => 'nullable|string|max:45',
            'details.*.to_storage_tank_no' => 'nullable|string|max:45',
            'details.*.to_refinery_fractionation' => 'nullable|string|max:45',
            'details.*.to_auto_filling_tank' => 'nullable|integer',
            'details.*.to_other' => 'nullable|string|max:45',

            /*
            |------------------------------------------------------------------
            | Quality Fields (nullable numeric)
            |------------------------------------------------------------------
            */
            'details.*.quality_m_and_i' => 'nullable|numeric',
            'details.*.quality_ffa' => 'nullable|numeric',
            'details.*.quality_lov_color_r' => 'nullable|numeric',
            'details.*.quality_lov_color_y' => 'nullable|numeric',
            'details.*.quality_cp_temp' => 'nullable|numeric',
            'details.*.quality_smp' => 'nullable|numeric',
            'details.*.quality_pv' => 'nullable|numeric',
            'details.*.quality_iv' => 'nullable|numeric',

            /*
            |------------------------------------------------------------------
            | Detail Remarks
            |------------------------------------------------------------------
            */
            'details.*.remark' => 'nullable|string',
        ];
    }
}
