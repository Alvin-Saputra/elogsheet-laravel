<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateDryFraRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Ubah ke true agar request diizinkan
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
            // --- HEADER RULES ---
            'date'               => 'required|date',
            'posting_date'       => 'required|date',
            'company'            => 'required|string|max:100',
            'plant'              => 'required|string|max:50',
            'crystallizer'       => 'required|string|max:100',
            
            // Kolom numerik & desimal (gunakan nullable jika tidak wajib diisi user)
            'feed_oil_iv'        => 'nullable|numeric|min:0',
            'initial_oil_level'  => 'nullable|numeric|min:0',
            'cooling_start_temp' => 'nullable|numeric',
            'agitator_speed'     => 'nullable|numeric|min:0',
            'water_pump_pres'    => 'nullable|numeric|min:0',
            
            // Kolom Waktu (Time)
            // Sesuaikan format: 'H:i' untuk "14:30" atau 'H:i:s' untuk "14:30:00"
            'filling_start_time' => 'nullable|date_format:H:i', 
            'filling_end_time'   => 'nullable|date_format:H:i',
            'cooling_start_time' => 'nullable|date_format:H:i',

            'remarks'            => 'nullable|string|max:500',
            'is_completed'       => 'nullable|boolean',

            // --- DETAILS RULES (Array) ---
            'details'            => 'nullable|array',
            
            // Validasi untuk setiap item di dalam array 'details'
            'details.*.filtration_cycle_number' => 'required|numeric',
            // 'details.*.filtration_date'         => 'required|date',
            
            'details.*.filtration_temp'         => 'nullable|numeric',
            'details.*.time_start_filtration'   => 'nullable|date_format:H:i',
            'details.*.time_end_filtration'     => 'nullable|date_format:H:i',
            
            'details.*.load'                    => 'nullable|numeric|min:0',
            'details.*.olein_iv'                => 'nullable|numeric|min:0',
            'details.*.olein_cp'                => 'nullable|numeric',
            'details.*.olein_ffa'               => 'nullable|numeric|min:0',
            'details.*.olein_color_red'         => 'nullable|numeric|min:0',
            
            'details.*.stearin_iv'              => 'nullable|numeric|min:0',
            'details.*.stearin_ffa'             => 'nullable|numeric|min:0',
            'details.*.stearin_color_red'       => 'nullable|numeric|min:0',
            'details.*.stearin_pv'              => 'nullable|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            // Custom messages untuk Header
            'date.required'               => 'Tanggal laporan wajib diisi.',
            // 'company.required'            => 'Nama perusahaan wajib diisi.',
            // 'filling_end_time.after'      => 'Waktu selesai filling harus setelah waktu mulai.',
            // 'cooling_start_time.date_format' => 'Format waktu cooling start salah (Gunakan Jam:Menit).',

            // Custom messages untuk Detail (Array)
            'details.array'               => 'Format data detail tidak valid.',
            // 'details.*.filtration_cycle_number.required' => 'Nomor siklus filtrasi pada baris detail wajib diisi.',
            'details.*.filtration_date.required'         => 'Tanggal filtrasi pada baris detail wajib diisi.',
            // 'details.*.time_end_filtration.after'        => 'Waktu selesai filtrasi harus lebih akhir dari waktu mulai.',
            'details.*.olein_iv.numeric'                 => 'Nilai Olein IV harus berupa angka.',
        ];
    }
}