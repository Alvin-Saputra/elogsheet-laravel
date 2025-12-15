<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCoaRequest extends FormRequest
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
            'no_doc' => 'required|unique:coa_incoming_plant_chemical_ingredient_header|max:100',
            'product' => 'required|max:45',
            'grade' => 'required|max:45',
            'packing' => 'required',
            'quantity' => 'required|numeric',
            'tanggal_pengiriman' => 'required|date',
            'vehicle' => 'required|max:45',
            'lot_no' => 'required|max:45',
            'production_date' => 'required|date',
            'expired_date' => 'required|date',
            'detail' => 'required|array|min:1',
            'detail.*.parameter' => 'required|max:45',
            'detail.*.actual_min' => 'required|numeric|min:0',
            'detail.*.actual_max' => 'required|numeric',
            'detail.*.standard_min' => 'required|numeric|min:0',
            'detail.*.standard_max' => 'required|numeric',
            'detail.*.method' => 'required|max:45',
        ];
    }
}
