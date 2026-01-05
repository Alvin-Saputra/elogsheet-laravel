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
            'quantity' => 'required|numeric',
            'shipper' => 'required|string|max:45',
            'destination' => 'required|string|max:45',
            'vessel_name' => 'required|string|max:45',
            'hasil_analisa_ffa' => 'required|numeric',
            'hasil_analisa_iv' => 'required|numeric',
            'hasil_analisa_moisture' => 'required|numeric',
            'hasil_analisa_colour' => 'required|numeric',
            'hasil_analisa_pv' => 'required|numeric',
            'hasil_analisa_smp' => 'required|numeric',

            // details array (required to have at least one item)
            'details' => 'required|array|min:1',
            'details.*.palka_s_palka' => 'required|numeric',
            'details.*.palka_s_ffa' => 'required|numeric',
            'details.*.palka_s_iv' => 'required|numeric',
            'details.*.palka_s_colour' => 'required|numeric',
            'details.*.palka_s_pv' => 'required|numeric',
            'details.*.palka_s_mni' => 'required|numeric',
            'details.*.palka_p_palka' => 'required|numeric',
            'details.*.palka_p_ffa' => 'required|numeric',
            'details.*.palka_p_iv' => 'required|numeric',
            'details.*.palka_p_colour' => 'required|numeric',
            'details.*.palka_p_pv' => 'required|numeric',
            'details.*.palka_p_mni' => 'required|numeric',
        ];
    }
}
