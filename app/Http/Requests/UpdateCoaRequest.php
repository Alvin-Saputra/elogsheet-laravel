<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCoaRequest extends FormRequest
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
        $id = $this->route('id');
        return [
            'no_doc' => 'required|max:100|unique:coa_incoming_plant_chemical_ingredient_header,no_doc,' . $id . ',id',
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
            'detail.*.actual_max' => 'nullable|numeric',
            'detail.*.standard_min' => 'nullable|numeric',
            'detail.*.standard_max' => 'nullable|numeric',
            'detail.*.method' => 'nullable|max:45',
        ];
    }
}
