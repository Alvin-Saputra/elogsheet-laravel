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
        // 1. LOGIKA VALIDASI UNIK (PENTING)
        // Kita perlu mengecualikan (ignore) record COA yang sedang diedit agar tidak dianggap duplikat oleh Laravel.
        // Karena route parameter isinya ID AROIP, kita harus cari ID COA-nya dulu.
        
        $aroipId = $this->route('id'); // Ambil ID dari URL: /api/ariopchemical/{id}
        $uniqueIgnore = '';

        if ($aroipId) {
            $aroip = AROIPChemicalHeader::find($aroipId);
            // Jika ketemu dan punya relasi COA, ambil ID COA-nya
            if ($aroip && $aroip->id_coa) {
                // Format ignore unique: ,ID_YANG_DI_IGNORE,NAMA_KOLOM_ID
                $uniqueIgnore = ',' . $aroip->id_coa . ',id';
            }
        }

        return [
            // --- AROIP RULES (BAWAAN) ---
            'material'      => 'sometimes|string|max:100',
            'quantity'      => 'sometimes|numeric',
            'analyst'       => 'sometimes|string|max:100',
            'supplier'      => 'sometimes|string|max:100',
            'police_no'     => 'sometimes|string|max:20|nullable',
            'batch_lot'     => 'sometimes|string|max:100',
            'status'        => 'sometimes|string|max:45',
            'form_no'       => 'sometimes|string|max:45',
            'revision_no'   => 'sometimes|integer',
            'revision_date' => 'sometimes|date',
            
            // AROIP Details
            'details'                     => 'sometimes|array',
            'details.*.id'                => 'required_with:details|string',
            'details.*.parameter'         => 'required_with:details|string|max:100',
            'details.*.specification_min' => 'required_with:details|numeric',
            'details.*.specification_max' => 'required_with:details|numeric',
            'details.*.result_min'        => 'required_with:details|numeric|nullable',
            'details.*.result_max'        => 'nullable|numeric',
            'details.*.status_ok'         => 'required_with:details|string|in:Y,N',
            'details.*.remark'            => 'nullable|string',

            // --- COA RULES (BARU) ---
            // 'sometimes' di 'coa' artinya: validasi bawahnya hanya jalan jika frontend mengirim data 'coa'
            'coa' => 'sometimes|array',

            // Header COA
            // 'required_with:coa' artinya field ini wajib ada jika array 'coa' dikirim
            'coa.no_doc'             => 'required_with:coa|max:100|unique:coa_incoming_plant_chemical_ingredient_header,no_doc' . $uniqueIgnore,
            'coa.product'            => 'required_with:coa|max:45',
            'coa.grade'              => 'required_with:coa|max:45',
            'coa.packing'            => 'required_with:coa',
            'coa.quantity'           => 'required_with:coa|numeric',
            'coa.tanggal_pengiriman' => 'required_with:coa|date',
            'coa.vehicle'            => 'required_with:coa|max:45',
            'coa.lot_no'             => 'required_with:coa|max:45',
            'coa.production_date'    => 'required_with:coa|date',
            'coa.expired_date'       => 'required_with:coa|date',

            // Details COA
            'coa.details'              => 'required_with:coa|array|min:1',
            'coa.details.*.id'         => 'sometimes|string', // Opsional (untuk mapping update)
            'coa.details.*.parameter'  => 'required_with:coa.details|max:45',
            'coa.details.*.actual_min' => 'required_with:coa.details|numeric|min:0',
            'coa.details.*.actual_max' => 'nullable|numeric',
            'coa.details.*.standard_min' => 'nullable|numeric',
            'coa.details.*.standard_max' => 'nullable|numeric',
            'coa.details.*.method'     => 'nullable|max:45',
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
            // Pesan Error AROIP
            'details.*.parameter.required_with' => 'The parameter field is required for all detail items.',
            'details.*.specification_min.required_with' => 'The specification min field is required for all detail items.',
            'details.*.specification_max.required_with' => 'The specification max field is required for all detail items.',
            'details.*.status_ok.in' => 'The status must be either Y or N.',

            // Pesan Error COA
            'coa.no_doc.unique' => 'The COA Document Number has already been used by another record.',
            'coa.no_doc.required_with' => 'COA Document Number is required.',
            'coa.details.required_with' => 'COA details are required when updating COA data.',
            'coa.details.*.parameter.required_with' => 'Parameter field in COA details is required.',
            'coa.details.*.actual_min.required_with' => 'Actual Min field in COA details is required.',
        ];
    }
}