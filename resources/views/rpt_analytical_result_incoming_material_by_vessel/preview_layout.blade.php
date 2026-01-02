@extends('layouts.app')

@section('page_title', 'Start Up Produksi Checklist - Layout Preview')

@section('content')
    <div class="bg-white p-6 rounded shadow-md text-sm relative max-w-4xl mx-auto">
        {{-- Form Info (Unchanged) --}}
        <table class="w-full mb-4">
            <tbody>
                <tr class="align-top">
                    {{-- Column 1: Logo and Bekasi --}}
                    <td class="w-1/5 text-center">
                        {{-- 
                          TODO: Replace this path with your actual logo.
                          You can use {{ asset('images/logo.png') }} if it's in the public/images folder.
                        --}}
                        <img src="{{ asset('images/KPN Corp.jpg') }}" alt="Logo" class="h-12 mx-auto mb-1">
                        <span class="font-bold">Bekasi</span>
                    </td>

                    {{-- Column 2: Titles --}}
                    <td class="w-3/5 text-center pt-2">
                        <h3 class="text-xl font-bold uppercase">Analytical Result Incoming<br> Material By Vessel</h3>
                    </td>

                    {{-- Column 3: Form Info --}}
                    <td class="w-1/5">
                        {{-- This block was moved from its 'absolute' position --}}
                        <div class="text-xs leading-tight text-left border border-gray-400 p-2 rounded-md">
                            <div><strong>Form No.</strong> : {{ $header->form_no ?? 'F-QOC-009' }}</div>
                            <div><strong>Date Issued</strong> :
                                {{ $header->date_issued ? \Carbon\Carbon::parse($header->form_date_issued)->format('ymd') : '241019' }}
                            </div>
                            <div><strong>Revision</strong> : {{ $header->revision_no ?? '00' }}</div>
                            <div><strong>Rev. Date</strong> :
                                {{ $header->revision_date ? \Carbon\Carbon::parse($header->form_rev_date)->format('ymd') : '00' }}
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- Header Info Section (Unchanged) --}}
        <div class="border border-gray-400 p-2 rounded-md mb-4">
            <table class="w-full">
                <tbody>
                    <tr class="align-top">
                        <td class="w-2/5 pr-1">
                            <div class="flex mb-1">
                                <strong class="w-28">Tanggal</strong>:
                                {{ $header->arrival ? \Carbon\Carbon::parse($header->transaction_date)->format('d-m-Y') : '' }}
                            </div>

                            <div class="flex mb-1">
                                <strong class="w-28">Material</strong>: {{ $header->material ?? '' }}
                            </div>

                            <div class="flex mb-1">
                                <strong class="w-28">Quantity</strong>: {{ $header->quantity ?? '' }}
                            </div>

                            <div class="flex mb-1">
                                <strong class="w-28">FFA</strong>: {{ $header->ffa ?? '' }}
                            </div>

                            <div class="flex mb-1">
                                <strong class="w-28">DOBI</strong>: {{ $header->dobi ?? '' }}
                            </div>

                        </td>
                        <td class="w-2/5 px-1">
                            <div class="flex mb-1">
                                <strong class="w-28">Ship's Name</strong>:
                                {{ $header->ship_name ?? 'N/A' }}
                            </div>
                            <div class="flex mb-1">
                                <strong class="w-28">Remarks</strong>: {{ $header->remarks ?? '-' }}
                            </div>
                            <div class="flex mb-1">
                                <strong class="w-28">Contract/DO Number</strong>: {{ $header->contract_do_nomor ?? '-' }}
                            </div>

                            <div class="flex mb-1">
                                <strong class="w-28">M&I</strong>: {{ $header->mni ?? '' }}
                            </div>

                            <div class="flex mb-1">
                                <strong class="w-28">Others</strong>: {{ $header->others ?? '' }}
                            </div>
                        </td>


                    </tr>

                </tbody>
            </table>
        </div>
        
        <br>

        <table class="min-w-full bg-white border border-gray-400">
            <thead class="bg-gray-200 text-gray-800 text-sm">
                <tr>
                    <th colspan="15" class="px-3 py-2 border border-gray-400 text-center">Hasil Analisa Tiap Palka</th>

                </tr>
                <tr>
                    <th colspan="5" class="px-3 py-2 border border-gray-400 text-center">Palka S</th>
                    <th colspan="5" class="px-3 py-2 border border-gray-400 text-center">Palka C</th>
                    <th colspan="5" class="px-3 py-2 border border-gray-400 text-center">Palka P</th>
                </tr>
                <tr>
                    {{-- Palka S --}}
                    <th class="px-3 py-2 border border-gray-400 text-center">Palka No</th>
                    <th class="px-3 py-2 border border-gray-400 text-center">FFA</th>
                    <th class="px-3 py-2 border border-gray-400 text-center">IV</th>
                    <th class="px-3 py-2 border border-gray-400 text-center">DOBI</th>
                    <th class="px-3 py-2 border border-gray-400 text-center">M&amp;I</th>

                    {{-- Palka C --}}
                    <th class="px-3 py-2 border border-gray-400 text-center">Palka No</th>
                    <th class="px-3 py-2 border border-gray-400 text-center">FFA</th>
                    <th class="px-3 py-2 border border-gray-400 text-center">IV</th>
                    <th class="px-3 py-2 border border-gray-400 text-center">DOBI</th>
                    <th class="px-3 py-2 border border-gray-400 text-center">M&amp;I</th>

                    {{-- Palka P --}}
                    <th class="px-3 py-2 border border-gray-400 text-center">Palka No</th>
                    <th class="px-3 py-2 border border-gray-400 text-center">FFA</th>
                    <th class="px-3 py-2 border border-gray-400 text-center">IV</th>
                    <th class="px-3 py-2 border border-gray-400 text-center">DOBI</th>
                    <th class="px-3 py-2 border border-gray-400 text-center">M&amp;I</th>
                </tr>
            </thead>

            <tbody class="text-gray-700 text-sm">
                @forelse ($header->details as $detail)
                    <tr>
                        {{-- Palka S --}}
                        <td class="px-3 py-1 border border-gray-400 text-center">
                            {{ $detail->palka_s_no ?? '-' }}
                        </td>
                        <td class="px-3 py-1 border border-gray-400 text-center">
                            {{ $detail->palka_s_ffa ?? '-' }}
                        </td>
                        <td class="px-3 py-1 border border-gray-400 text-center">
                            {{ $detail->palka_s_iv ?? '-' }}
                        </td>
                        <td class="px-3 py-1 border border-gray-400 text-center">
                            {{ $detail->palka_s_dobi ?? '-' }}
                        </td>
                        <td class="px-3 py-1 border border-gray-400 text-center">
                            {{ $detail->palka_s_mni ?? '-' }}
                        </td>

                        {{-- Palka C --}}
                        <td class="px-3 py-1 border border-gray-400 text-center">
                            {{ $detail->palka_c_no ?? '-' }}
                        </td>
                        <td class="px-3 py-1 border border-gray-400 text-center">
                            {{ $detail->palka_c_ffa ?? '-' }}
                        </td>
                        <td class="px-3 py-1 border border-gray-400 text-center">
                            {{ $detail->palka_c_iv ?? '-' }}
                        </td>
                        <td class="px-3 py-1 border border-gray-400 text-center">
                            {{ $detail->palka_c_dobi ?? '-' }}
                        </td>
                        <td class="px-3 py-1 border border-gray-400 text-center">
                            {{ $detail->palka_c_mni ?? '-' }}
                        </td>

                        {{-- Palka P --}}
                        <td class="px-3 py-1 border border-gray-400 text-center">
                            {{ $detail->palka_p_no ?? '-' }}
                        </td>
                        <td class="px-3 py-1 border border-gray-400 text-center">
                            {{ $detail->palka_p_ffa ?? '-' }}
                        </td>
                        <td class="px-3 py-1 border border-gray-400 text-center">
                            {{ $detail->palka_p_iv ?? '-' }}
                        </td>
                        <td class="px-3 py-1 border border-gray-400 text-center">
                            {{ $detail->palka_p_dobi ?? '-' }}
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

        <br><br>

        <table class="min-w-full bg-white border border-gray-400">
            <thead class="bg-gray-200 text-gray-800 text-sm">
                <tr>
                    <th colspan="2" class="px-3 py-2 border border-gray-400 text-center">Hasil Analisa Komposit Palka</th>

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
                        <td class="px-3 py-1 border border-gray-400 text-center">{{ $header->hasil_analisa_moisture}}</td>
                    </tr>

                     <tr>
                        <td class="px-3 py-1 border border-gray-400 text-center">Dobi</td>
                        <td class="px-3 py-1 border border-gray-400 text-center">{{ $header->hasil_analisa_dobi}}</td>
                    </tr>

                     <tr>
                        <td class="px-3 py-1 border border-gray-400 text-center">PV</td>
                        <td class="px-3 py-1 border border-gray-400 text-center">{{ $header->hasil_analisa_pv}}</td>
                    </tr>

                     <tr>
                        <td class="px-3 py-1 border border-gray-400 text-center">AnV</td>
                        <td class="px-3 py-1 border border-gray-400 text-center">{{ $header->hasil_analisa_anv}}</td>
                    </tr>
                </tbody>
        </table>



        {{-- Signature Section (Unchanged) --}}
        <div class="grid grid-cols-3 text-center mt-10 text-xs gap-4">

            <div>
                <strong> Done by, </strong><br>
                (Operator)<br>
                <br>
                ( {{ $header->entry_by ?? '_______________________' }} )<br>
                <small>Date:
                    {{ $header->entry_date ? \Carbon\Carbon::parse($header->entry_date)->format('d-m-Y H:i') : '' }}</small>

            </div>
            <div>
                <strong>Prepared by:</strong><br>
                (Shift Leader)<br>

                <br>
                ( {{ $header->prepared_by ?? '_______________________' }} )<br>
                <small>Date:
                    {{ $header->prepared_date ? \Carbon\Carbon::parse($header->prepared_date)->format('d-m-Y H:i') : '' }}</small>
            </div>
            <div>
                <strong>Approved by:</strong><br>
                (Section Head)<br>

                <br>
                ( {{ $header->approved_by ?? '_______________________' }} )<br>
                <small>Date:
                    {{ $header->approved_date ? \Carbon\Carbon::parse($header->checked_date)->format('d-m-Y H:i') : '' }}</small>
            </div>
        </div>

        {{-- Electronic Approval Footer (Unchanged) --}}
        <div class="mt-6 text-center text-xs text-gray-600 italic">
            Dokumen ini telah disetujui secara elektronik melalui sistem [E-Logsheet],
            sehingga tidak memerlukan tanda tangan asli.
        </div>
    </div>
@endsection
