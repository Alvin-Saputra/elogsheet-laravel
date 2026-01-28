    @extends('layouts.app')

    @section('page_title', 'Detail Quality Report QC')

    @section('content')
        <div class="bg-white p-6 rounded-2xl shadow-md max-w-6xl mx-auto text-sm text-gray-700">
            {{-- Header --}}
            <div class="flex items-center space-x-3 mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-red-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-6h13M9 7v.01M3 17h.01M3 7h.01M3 12h.01M9 12h13" />
                </svg>
                <h3 class="text-2xl font-bold text-gray-700">
                    Detail Data #{{ $report->id }}
                </h3>
            </div>


            {{-- Section: Informasi Umum --}}
            <x-section title="Informasi Umum">
                <x-info label="Tanggal" :value="$report->transaction_date?->format('d M Y')" />
                <x-info label="Jam" :value="$report->time?->format('H:i')" />
                <x-info label="Shift" :value="$report->shift" />
                <x-info label="Remarks" :value="$report->remarks" />
            </x-section>

            {{-- Section: Proses --}}
            <x-section title="Parameter Proses">
                <x-info label="Refinery" :value="$report->work_center" />
                <x-info label="Oil Type" :value="$report->oil_type" />
            </x-section>

            {{-- Section: Raw Material --}}
            <x-section title="Raw Material">
                <x-info label="Source Tank" :value="$report->rm_tank_source" />
                <x-info label="Flowrate" :value="$report->rm_flow_rate" />
                <x-info label="FFA (%)" :value="$report->rm_ffa" />
                <x-info label="M&I (%)" :value="$report->{'rm_m&i'}" />
                <x-info label="DOBI" :value="$report->rm_dobi" />
                <x-info label="IV" :value="$report->rm_iv" />
                <x-info label="PV (%)" :value="$report->rm_pv" />
                <x-info label="AV" :value="$report->rm_av" />
                <x-info label="Color R" :value="$report->rm_color_r" />
                <x-info label="Color Y" :value="$report->rm_color_y" />
                <x-info label="Color B" :value="$report->rm_color_b" />
            </x-section>

            {{-- Section: Bleaching Oil --}}
            <x-section title="Bleach Oil">
                <x-info label="Color (R)" :value="$report->bo_color_r" />
                <x-info label="Color (Y)" :value="$report->bo_color_y" />
                <x-info label="Color (B)" :value="$report->bo_color_b" />
                <x-info label="BREAK TEST" :value="$report->bo_break_test" />
            </x-section>

            {{-- Section: RBDPO OIL (Finish Good) --}}
            <x-section :title="$report->oil_type">
                <x-info label="FFA (%)" :value="$report->fg_ffa" />
                <x-info label="Moist" :value="$report->fg_moisture" />
                <x-info label="Impurities" :value="$report->fg_impurities" />
                <x-info label="IV" :value="$report->fg_iv" />
                <x-info label="PV (%)" :value="$report->fg_pv" />
                <x-info label="Color R" :value="$report->fg_color_r" />
                <x-info label="Color Y" :value="$report->fg_color_y" />
                <x-info label="Color B" :value="$report->fg_color_b" />
                <x-info label="To Tank" :value="$report->fg_tank_to" />
            </x-section>

            {{-- Section: Fatty Product --}}
            <x-section title="Fatty Acid">
                <x-info label="FFA (%)" :value="$report->bp_ffa" />
                <x-info label="M&I (%)" :value="$report->{'bp_m&i'}" />
                <x-info label="To Tank" :value="$report->bp_to_tank" />
            </x-section>

            {{-- Section: Fatty Product --}}
            <x-section title="SBE">
                <x-info label="OC (%)" :value="$report->w_sbe_qc" />
                {{-- <x-info label="M&I (%)" :value="$report->{'w_sbe_m&i'}" /> --}}
            </x-section>

            {{-- Section: Validasi --}}
            {{-- Section: Validasi & Approval --}}
            <x-section title="Validasi & Approval">

                {{-- Prepared Shift --}}
                @php
                    $name = $report->prepared_fullname ?? $report->prepared_by;
                    $role = $report->prepared_role ?? 'Shift Leader';
                    $displayTextPrepared = "$name ($role)";
                @endphp

                <x-info label="Prepared By" :value="$displayTextPrepared" />
                <x-info label="Prepared Date" :value="$report->prepared_date?->format('d M Y H:i')" />
                <x-info label="Prepared Status" :value="$report->prepared_status" />

                {{-- Remarks --}}
                <x-info label="Prepared Remarks" :value="$report->prepared_status_remarks_shift" />

                @php
                    $name = $report->checked_fullname ?? $report->checked_by;
                    $role = $report->checked_role ?? 'Shift Leader';
                    $displayTextChecked = "$name ($role)";
                @endphp
                {{-- Checked --}}
                <x-info label="Checked By" :value="$displayTextChecked" />
                <x-info label="Checked Date" :value="$report->checked_date?->format('d M Y H:i')" />
                <x-info label="Checked Status" :value="$report->checked_status" />
                <x-info label="Checked Remarks" :value="$report->checked_status_remarks" />

                @php
                    $name = $report->updated_fullname ?? $report->updated_by;
                    $role = $report->updated_role ?? 'Shift Leader';
                    $displayTextUpdated = "$name ($role)";
                @endphp
                {{-- Updated --}}
                <x-info label="Updated By" :value="$displayTextUpdated" />
                <x-info label="Updated Date" :value="$report->updated_date?->format('d M Y H:i')" />

            </x-section>


            {{-- Section: Lainnya --}}
            <x-section title="Lainnya">
                <x-info label="Company" :value="$report->company" />
                <x-info label="Plant" :value="$report->plant" />


                {{-- Entry --}}

                @php
                    $name = $report->entry_fullname ?? $report->entry_by;
                    $role = $report->entry_role ?? 'Shift Leader';
                    $displayTextEntry = "$name ($role)";
                @endphp
                <x-info label="Entry By" :value="$displayTextEntry" />
                <x-info label="Entry Date" :value="$report->entry_date?->format('d M Y H:i')" />
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
