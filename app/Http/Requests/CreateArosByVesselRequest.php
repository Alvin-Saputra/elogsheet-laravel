<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateArosByVesselRequest extends FormRequest
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
              // header fields
            'company' => 'required|string|max:45',
            'plant' => 'required|string|max:45',
            'product_name' => 'required|string|max:45',
            'sampling_date' => 'required|date',
            'quantity' => 'nullable|numeric',
            'shipper' => 'nullable|string|max:45',
            'destination' => 'nullable|string|max:45',
            'vessel_name' => 'nullable|string|max:45',
            'hasil_analisa_ffa' => 'nullable|numeric',
            'hasil_analisa_iv' => 'nullable|numeric',
            'hasil_analisa_moisture' => 'nullable|numeric',
            'hasil_analisa_colour' => 'nullable|numeric',
            'hasil_analisa_pv' => 'nullable|numeric',
            'hasil_analisa_smp' => 'nullable|numeric',
            'remark' => 'nullable|string|max:255',

            // details array (required to have at least one item)
            'details' => 'nullable|array',
            'details.*.palka_s_palka' => 'nullable|numeric',
            'details.*.palka_s_ffa' => 'nullable|numeric',
            'details.*.palka_s_iv' => 'nullable|numeric',
            'details.*.palka_s_colour' => 'nullable|numeric',
            'details.*.palka_s_pv' => 'nullable|numeric',
            'details.*.palka_s_mni' => 'nullable|numeric',
            'details.*.palka_p_palka' => 'nullable|numeric',
            'details.*.palka_p_ffa' => 'nullable|numeric',
            'details.*.palka_p_iv' => 'nullable|numeric',
            'details.*.palka_p_colour' => 'nullable|numeric',
            'details.*.palka_p_pv' => 'nullable|numeric',
            'details.*.palka_p_mni' => 'nullable|numeric',
        ];
    }
}
