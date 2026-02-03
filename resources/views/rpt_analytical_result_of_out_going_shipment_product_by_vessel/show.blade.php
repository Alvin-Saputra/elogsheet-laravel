@extends('layouts.app')

@section('page_title', 'Detail Analytical Result Outgoing Shipment')

@section('content')
    {{-- Keep the PHP helpers for data formatting --}}
    @php
        $displayDate = $header->loading_date ?? ($header->entry_date ?? null);

        $toFloat = function ($v) {
            if ($v === null || $v === '') {
                return null;
            }
            $s = str_replace([',', ' '], ['', ''], (string) $v);
            return is_numeric($s) ? (float) $s : null;
        };

        $firstValue = function ($obj, array $keys) {
            foreach ($keys as $k) {
                if (is_object($obj) && isset($obj->$k) && $obj->$k !== '') {
                    return $obj->$k;
                }
                if (is_array($obj) && array_key_exists($k, $obj) && $obj[$k] !== '') {
                    return $obj[$k];
                }
            }
            return null;
        };

        $fmt = function ($val, $decimals = 3) use ($toFloat) {
            $n = $toFloat($val);
            return $n !== null ? number_format($n, $decimals) : '-';
        };

        $details = $header->details ?? collect([]);
    @endphp

    <div class="bg-white p-6 rounded-2xl shadow-md max-w-6xl mx-auto text-sm text-gray-700">

        {{-- TITLE HEADER --}}
        <div class="flex items-center space-x-3 mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-blue-600" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <div>
                <h3 class="text-2xl font-bold text-gray-700">Analytical Result Outgoing Shipment</h3>
                <span class="text-gray-500 text-xs uppercase tracking-wide">By Vessel</span>
                <h4>{{ $header->id }}</h4>
            </div>
        </div>

        {{-- DOCUMENT CONTROL INFO --}}


        {{-- SHIPMENT INFO --}}
        <x-section title="Informasi Pengiriman">
            <x-info label="Loading Date" :value="$displayDate ? \Carbon\Carbon::parse($displayDate)->format('d M Y H:i') : '-'" />
            <x-info label="Product Name" :value="$header->product_name ?? '-'" />
            <x-info label="Quantity" :value="$header->quantity ?? ($header->qty ?? '-')" />
            <x-info label="Ship's Name" :value="$header->shipper ?? '-'" />
            <x-info label="Destination" :value="$header->destination ?? '-'" />
            <x-info label="Vessel Name" :value="$header->vessel_name ?? '-'" />
        </x-section>

        {{-- ANALYTICAL DETAILS TABLE --}}
        <div class="mt-6 border-t border-gray-100 pt-4">
            <h4 class="text-lg font-semibold text-gray-800 mb-4 px-2">Hasil Analisa</h4>
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full bg-white border border-gray-400">
                    <thead class="bg-gray-200 text-gray-800 text-sm">
                        <tr>
                            <th colspan="15" class="px-3 py-2 border border-gray-400 text-center">Hasil Analisa Tiap Palka
                            </th>

                        </tr>
                        <tr>
                            <th colspan="6" class="px-3 py-2 border border-gray-400 text-center">Palka S</th>

                            <th colspan="6" class="px-3 py-2 border border-gray-400 text-center">Palka P</th>
                        </tr>
                        <tr>
                            {{-- Palka S --}}
                            <th class="px-3 py-2 border border-gray-400 text-center">Palka No</th>
                            <th class="px-3 py-2 border border-gray-400 text-center">FFA</th>
                            <th class="px-3 py-2 border border-gray-400 text-center">IV</th>
                            <th class="px-3 py-2 border border-gray-400 text-center">COLOUR R</th>
                            <th class="px-3 py-2 border border-gray-400 text-center">PV</th>
                            <th class="px-3 py-2 border border-gray-400 text-center">M&amp;I</th>

                            {{-- Palka C --}}


                            {{-- Palka P --}}
                            <th class="px-3 py-2 border border-gray-400 text-center">Palka No</th>
                            <th class="px-3 py-2 border border-gray-400 text-center">FFA</th>
                            <th class="px-3 py-2 border border-gray-400 text-center">IV</th>
                            <th class="px-3 py-2 border border-gray-400 text-center">COLOUR R</th>
                            <th class="px-3 py-2 border border-gray-400 text-center">PV</th>
                            <th class="px-3 py-2 border border-gray-400 text-center">M&amp;I</th>

                        </tr>
                    </thead>

                    <tbody class="text-gray-700 text-sm">
                        @forelse ($header->details as $detail)
                            <tr>
                                {{-- Palka S --}}
                                <td class="px-3 py-1 border border-gray-400 text-center">
                                    {{ $detail->palka_s_palka ?? '-' }}
                                </td>
                                <td class="px-3 py-1 border border-gray-400 text-center">
                                    {{ $detail->palka_s_ffa ?? '-' }}
                                </td>
                                <td class="px-3 py-1 border border-gray-400 text-center">
                                    {{ $detail->palka_s_iv ?? '-' }}
                                </td>
                                <td class="px-3 py-1 border border-gray-400 text-center">
                                    {{ $detail->palka_s_colour ?? '-' }}
                                </td>
                                <td class="px-3 py-1 border border-gray-400 text-center">
                                    {{ $detail->palka_s_pv ?? '-' }}
                                </td>
                                <td class="px-3 py-1 border border-gray-400 text-center">
                                    {{ $detail->palka_s_mni ?? '-' }}
                                </td>


                                {{-- Palka P --}}
                                <td class="px-3 py-1 border border-gray-400 text-center">
                                    {{ $detail->palka_p_palka ?? '-' }}
                                </td>
                                <td class="px-3 py-1 border border-gray-400 text-center">
                                    {{ $detail->palka_p_ffa ?? '-' }}
                                </td>
                                <td class="px-3 py-1 border border-gray-400 text-center">
                                    {{ $detail->palka_p_iv ?? '-' }}
                                </td>
                                <td class="px-3 py-1 border border-gray-400 text-center">
                                    {{ $detail->palka_p_colour ?? '-' }}
                                </td>
                                <td class="px-3 py-1 border border-gray-400 text-center">
                                    {{ $detail->palka_p_pv ?? '-' }}
                                </td>
                                <td class="px-3 py-1 border border-gray-400 text-center">
                                    {{ $detail->palka_p_mni ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="px-3 py-2 text-center text-gray-500 italic">
                                    No Palka data found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <br>

        <table class="min-w-full bg-white border border-gray-400">
            <thead class="bg-gray-200 text-gray-800 text-sm">
                <tr>
                    <th colspan="2" class="px-3 py-2 border border-gray-400 text-center">Hasil Analisa Komposit Palka
                    </th>

                </tr>
            <tbody>
                <tr>
                    <td class="px-3 py-1 border border-gray-400 text-center">FFA</td>
                    <td class="px-3 py-1 border border-gray-400 text-center">{{ $header->hasil_analisa_ffa }}</td>
                </tr>

                <tr>
                    <td class="px-3 py-1 border border-gray-400 text-center">IV</td>
                    <td class="px-3 py-1 border border-gray-400 text-center">{{ $header->hasil_analisa_iv }}</td>
                </tr>

                <tr>
                    <td class="px-3 py-1 border border-gray-400 text-center">Moisture</td>
                    <td class="px-3 py-1 border border-gray-400 text-center">{{ $header->hasil_analisa_moisture }}</td>
                </tr>

                <tr>
                    <td class="px-3 py-1 border border-gray-400 text-center">Colour R</td>
                    <td class="px-3 py-1 border border-gray-400 text-center">{{ $header->hasil_analisa_colour }}</td>
                </tr>

                <tr>
                    <td class="px-3 py-1 border border-gray-400 text-center">PV</td>
                    <td class="px-3 py-1 border border-gray-400 text-center">{{ $header->hasil_analisa_pv }}</td>
                </tr>

                <tr>
                    <td class="px-3 py-1 border border-gray-400 text-center">SMP</td>
                    <td class="px-3 py-1 border border-gray-400 text-center">{{ $header->hasil_analisa_smp }}</td>
                </tr>

            </tbody>
        </table>
        <br>
        <br>
        {{-- VALIDATION & APPROVAL --}}
        <x-section title="Validasi & Approval">
            {{-- Corrected By (QC Leader) --}}
            <x-info label="Prepared By" :value="optional($header->preparedByUser)->fullname ?? ($header->prepared_by ?? '-')" />
            <x-info label="Role" :value="optional($header->preparedByUser)->roles" />
            <x-info label="Date" :value="$header->prepared_date ? \Carbon\Carbon::parse($header->prepared_date)->format('d M Y H:i') : ''" />

            {{-- Approved By (QC Head) --}}
            <x-info label="Approved By" :value="optional($header->approvedByUser)->fullname ?? ($header->approved_by ?? '-')" />
            <x-info label="Role" :value="optional($header->approvedByUser)->roles" />
            <x-info label="Date" :value="$header->approved_date ? \Carbon\Carbon::parse($header->approved_date)->format('d M Y H:i') : ''" />


            {{-- Entry By --}}
            <x-info label="Entry By" :value="optional($header->entriedByUser)->fullname ?? ($header->entry_by ?? '-')" />
            <x-info label="Role" :value="optional($header->entriedByUser)->roles" />
            <x-info label="Date" :value="$header->entry_date ? \Carbon\Carbon::parse($header->entry_date)->format('d M Y H:i') : ''" />
        </x-section>


        <x-section title="Informasi Dokumen">
            <x-info label="Form No" :value="$header->form_no ?? 'F/QCO-013'" />
            <x-info label="Issued Date" :value="$header->entry_date ? \Carbon\Carbon::parse($header->entry_date)->format('d M Y') : '-'" />
            <x-info label="Revision" :value="$header->revision_no ?? '-'" />
            <x-info label="Rev. Date" :value="$header->revision_date ? \Carbon\Carbon::parse($header->revision_date)->format('d M Y') : '-'" />
        </x-section>

        {{-- BACK BUTTON --}}
        <div class="mt-8 text-right">
            <a href="{{ route('analytical-result-outgoing-shipment-by-vessel.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition duration-150 ease-in-out">
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
