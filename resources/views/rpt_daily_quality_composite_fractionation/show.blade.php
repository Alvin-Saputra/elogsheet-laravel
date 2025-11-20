@extends('layouts.app')

@section('page_title', 'Detail Daily Quality Composite Fractionation data')

@section('content')
    <div class="bg-white p-6 rounded-2xl shadow-md max-w-6xl mx-auto text-sm text-gray-700">
        <div class="flex items-center space-x-3 mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-red-600" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 17v-6h13M9 7v.01M3 17h.01M3 7h.01M3 12h.01M9 12h13" />
            </svg>
            <h3 class="text-2xl font-bold text-gray-700">Detail Data #{{ $data->id }}</h3>
        </div>

        <x-section title="Informasi Umum">
            <x-info label="Tanggal" :value="optional($data->transaction_date)->format('d M Y')" />
            <x-info label="Time" :value="$data->time" />
        </x-section>

        <x-section title="Parameter Proses">
            <x-info label="Work Center" :value="$data->work_center" />
            <x-info label="Crystalizer" :value="$data->crystalizer" />
        </x-section>

        <x-section title="Raw Material">
            <x-info label="M&I" :value="$data->rm_mni" />
            <x-info label="IV" :value="$data->rm_iv" />
            <x-info label="Colour R" :value="$data->rm_color_r" />
            <x-info label="Colour Y" :value="$data->rm_color_y" />
            <x-info label="Colour W" :value="$data->rm_color_w" />
            <x-info label="Colour B" :value="$data->rm_color_b" />
        </x-section>

        <x-section title="Finished Goods">
            <x-info label="FFA" :value="$data->fg_ffa" />
            <x-info label="M&I" :value="$data->fg_mni" />
            <x-info label="IV" :value="$data->fg_iv" />
            <x-info label="Colour R" :value="$data->fg_color_r" />
            <x-info label="Colour Y" :value="$data->fg_color_y" />
            <x-info label="Colour W" :value="$data->fg_color_w" />
            <x-info label="Colour B" :value="$data->fg_color_b" />
            <x-info label="CP" :value="$data->fg_cp" />
            <x-info label="Clarity" :value="$data->fg_clarity" />
            <x-info label="To Tank" :value="$data->fg_to_tank" />
        </x-section>

         <x-section title="Finished Goods">
            <x-info label="FFA" :value="$data->fg_ffa" />
            <x-info label="M&I" :value="$data->fg_mni" />
            <x-info label="IV" :value="$data->fg_iv" />
            <x-info label="Colour R" :value="$data->fg_color_r" />
            <x-info label="Colour Y" :value="$data->fg_color_y" />
            <x-info label="Colour W" :value="$data->fg_color_w" />
            <x-info label="Colour B" :value="$data->fg_color_b" />
            <x-info label="CP" :value="$data->fg_cp" />
            <x-info label="Clarity" :value="$data->fg_clarity" />
            <x-info label="To Tank" :value="$data->fg_to_tank" />
        </x-section>

           <x-section title="By Product">
            <x-info label="FFA" :value="$data->bp_ffa" />
            <x-info label="M&I" :value="$data->bp_mni" />
            <x-info label="IV" :value="$data->bp_iv" />
            <x-info label="PV" :value="$data->bp_pv" />
            <x-info label="Colour R" :value="$data->bp_color_r" />
            <x-info label="Colour Y" :value="$data->bp_color_y" />
            <x-info label="Colour W" :value="$data->bp_color_w" />
            <x-info label="Colour B" :value="$data->bp_color_b" />
            <x-info label="To Tank" :value="$data->bp_to_tank" />
        </x-section>

        <x-section title="Lainnya">
            <x-info label="Remarks" :value="$data->remarks" />
        </x-section>


        <x-section title="Validasi & Approval">
            <x-info label="Prepared By" :value="$data->prepared_by" />
            <x-info label="Prepared Date" :value="optional($data->prepared_date)->format('d M Y H:i')" />
            <x-info label="Prepared Status" :value="$data->prepared_status" />
            <x-info label="Prepared Status Remarks" :value="$data->prepared_status_remarks" />
            <x-info label="checked By" :value="$data->checked_by" />
            <x-info label="checked Date" :value="optional($data->checked_date)->format('d M Y H:i')" />
            <x-info label="checked Status" :value="$data->checked_status" />
            <x-info label="checked Status Remarks" :value="$data->checked_status_remarks" />
            <x-info label="Updated Date" :value="optional($data->updated_date)->format('d M Y H:i')" />
            <x-info label="Updated Status" :value="$data->updated_status" />
        </x-section>


        <div class="mt-6 text-right">
            <a href="{{ url()->previous() }}"
                class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1.707-9.707a1 1 0 011.414 0L13.414 13H7a1 1 0 110-2h6.414l-3.707-3.707a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
                Kembali
            </a>
        </div>
    </div>
@endsection
