@extends('layouts.app')

@section('page_title', 'Detail Daily Storage Tank Analytical data')

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
            <x-info label="Company" :value="$data->company"/>
        </x-section>

        <x-section title="Daily Storage Tank Analytical">
            <x-info label="Tank No" :value="$data->tank_no"/>
            <x-info label="Oil Type" :value="$data->oil_type" />
            <x-info label="Kapasitas Tanki" :value="$data->kapasitas_tanki" />
            <x-info label="Quantity" :value="$data->quantity" />
            <x-info label="Empty Space" :value="$data->empty_space" />
            <x-info label="Suhu" :value="$data->suhu" />
        </x-section>

         <x-section title="Quality Parameter">
            <x-info label="FFA" :value="$data->qp_ffa"/>
            <x-info label="Moisture" :value="$data->qp_moisture" />
            <x-info label="LoviBondColorR" :value="$data->qp_lovibond_color_r"/>
            <x-info label="LoviBondColorY" :value="$data->qp_lovibond_color_y"/>
            <x-info label="IV" :value="$data->qp_iv" />
            <x-info label="PV" :value="$data->qp_pv" />
            <x-info label="Slip Melting Point" :value="$data->qp_slip_melting_point"/>
            <x-info label="Cloud Point" :value="$data->qp_cloud_point"/>
            <x-info label="AnV" :value="$data->qp_anv"/>
            <x-info label="B-Carotene" :value="$data->qp_anv"/>
            <x-info label="P" :value="$data->qp_anv"/>
            <x-info label="Dobi" :value="$data->qp_dobi"/>
            <x-info label="Totox" :value="$data->qp_totox"/>
            <x-info label="Odor" :value="$data->qp_odor"/>

        </x-section>

        <x-section title="Lainnya">
            <x-info label="Remarks" :value="$data->remarks" />
        </x-section>

        
          <x-section title="Validasi & Approval">
            <x-info label="Prepared By" :value="$data->prepared_by" />
            <x-info label="Prepared Date" :value="optional($data->prepared_date)->format('d M Y H:i')" />
            <x-info label="Prepared Status" :value="$data->prepared_status" />
            <x-info label="Prepared Status Remarks" :value="$data->prepared_status_remarks" />
            <x-info label="approved By" :value="$data->approved_by" />
            <x-info label="approved Date" :value="optional($data->approved_date)->format('d M Y H:i')" />
            <x-info label="approved Status" :value="$data->approved_status" />
            <x-info label="approved Status Remarks" :value="$data->approved_status_remarks" />
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
