<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateArosProductByTruckRequest extends FormRequest
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
            'loading_date' => 'nullable|date',
            'product_name' => 'nullable|string|max:45',
            'quantity' => 'nullable|string|max:45',
            'ships_name' => 'nullable|string|max:45',
            'destination' => 'nullable|string|max:45',
            'load_port' => 'nullable|string|max:45',

            'details' => 'nullable|array',
      
            'details.*.id' => 'nullable|string|exists:t_analytical_result_outgoing_shipment_product_truck_detail,id',

            'details.*.ships_tank' => 'nullable|string|max:45',
            'details.*.no_police' => 'nullable|string|max:45',

            'details.*.ffa' => 'nullable|numeric|between:0,999999.9999',
            'details.*.m_and_i' => 'nullable|numeric|between:0,999999.9999',
            'details.*.iv' => 'nullable|numeric|between:0,999999.9999',
            'details.*.lovibond_color_red' => 'nullable|numeric|between:0,999999.9999',
            'details.*.lovibond_color_yellow' => 'nullable|numeric|between:0,999999.9999',
            'details.*.pv' => 'nullable|numeric|between:0,999999.9999',

            'details.*.other' => 'nullable|string|max:45',
            'details.*.remark' => 'nullable|string|max:45',
        ];
    }
}
