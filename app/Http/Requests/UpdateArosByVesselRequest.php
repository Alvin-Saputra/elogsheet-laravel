<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateArosByVesselRequest extends FormRequest
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
            // header fields - all optional for update (controller will preserve existing values)
            'company' => 'nullable|string|max:45',
            'plant' => 'nullable|string|max:45',
            'product_name' => 'nullable|string|max:45',
            'sampling_date' => 'nullable|date',
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

            // details are optional on update; when present must be an array
            // Option B: when client wants to update an existing detail row it MUST provide details.*.id
            // If id is provided it must exist; if omitted it's treated as a NEW row to create.
            'details' => 'nullable|array',
            'details.*.id' => 'nullable|string|exists:t_analytical_result_outgoing_shipment_by_vessel_detail,id',
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
