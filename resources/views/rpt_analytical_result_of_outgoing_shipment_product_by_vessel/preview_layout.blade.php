@extends('layouts.app')

@section('page_title', 'Analytical Result Outgoing Shipment Product By Vessel - Preview')

@section('content')
    @php
        $fmt = function ($val, $decimals = 4) {
            if ($val === null || $val === '') {
                return '-';
            }
            return number_format((float) $val, $decimals);
        };
    @endphp

    <style>
        .report-table th,
        .report-table td {
            border: 1px solid #222;
            padding: 6px;
            font-size: 12px;
        }

        .report-table thead th {
            background: #efefef;
            text-align: center;
            font-weight: 600;
        }
    </style>

    <div class="max-w-5xl mx-auto mb-4">
        <a href="{{ route('analytical-result-outgoing-shipment-product-by-vessel.index') }}"
            class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 text-sm font-semibold rounded-lg shadow">
            &larr; Back to List
        </a>
    </div>

    <div class="max-w-5xl mx-auto bg-white p-6 rounded shadow-sm">
        <table class="w-full mb-4">
            <tr>
                <td class="w-1/5 align-top">
                    <div class="font-bold text-lg">PT. PRISCOLIN</div>
                    <div class="text-sm">BEKASI</div>
                </td>
                <td class="w-3/5 text-center align-top">
                    <h3 class="text-xl font-bold uppercase leading-tight">
                        Analytical Result of Outgoing Shipment<br>Product by Vessel
                    </h3>
                </td>
                <td class="w-1/5 align-top text-right text-xs">
                    <div class="border p-2 inline-block text-left">
                        <div><strong>Form No</strong> : {{ $header->form_no ?? 'F/QCO-020' }}</div>
                        <div><strong>Issued Date</strong> : {{ $header->date_issued ? \Carbon\Carbon::parse($header->date_issued)->format('ymd') : '-' }}</div>
                        <div><strong>Rev.</strong> : {{ $header->revision_no ?? '-' }}</div>
                        <div><strong>Rev. Date</strong> : {{ $header->revision_date ? \Carbon\Carbon::parse($header->revision_date)->format('ymd') : '-' }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="border border-gray-400 p-3 rounded mb-4 text-sm">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <div><strong class="inline-block w-36">Sampling Date</strong>: {{ $header->sampling_date ? \Carbon\Carbon::parse($header->sampling_date)->format('d-m-Y') : '-' }}</div>
                    <div><strong class="inline-block w-36">Product Name</strong>: {{ $header->product_name ?? '-' }}</div>
                    <div><strong class="inline-block w-36">Quantity</strong>: {{ $header->quantity ?? '-' }}</div>
                </div>
                <div>
                    <div><strong class="inline-block w-36">Vessel Name</strong>: {{ $header->vessel_name ?? '-' }}</div>
                    <div><strong class="inline-block w-36">Shipper</strong>: {{ $header->shipper ?? '-' }}</div>
                    <div><strong class="inline-block w-36">Destination</strong>: {{ $header->destination ?? '-' }}</div>
                </div>
            </div>
        </div>

        <table class="w-full report-table mb-4">
            <thead>
                <tr>
                    <th colspan="12">Hasil Analisa Tiap Palka</th>
                </tr>
                <tr>
                    <th colspan="6">Palka S</th>
                    <th colspan="6">Palka P</th>
                </tr>
                <tr>
                    <th>Palka No</th>
                    <th>FFA</th>
                    <th>IV</th>
                    <th>Colour</th>
                    <th>PV</th>
                    <th>M&amp;I</th>
                    <th>Palka No</th>
                    <th>FFA</th>
                    <th>IV</th>
                    <th>Colour</th>
                    <th>PV</th>
                    <th>M&amp;I</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($header->details as $detail)
                    <tr>
                        <td class="text-center">{{ $fmt($detail->palka_s_palka) }}</td>
                        <td class="text-center">{{ $fmt($detail->palka_s_ffa) }}</td>
                        <td class="text-center">{{ $fmt($detail->palka_s_iv) }}</td>
                        <td class="text-center">{{ $fmt($detail->palka_s_colour) }}</td>
                        <td class="text-center">{{ $fmt($detail->palka_s_pv) }}</td>
                        <td class="text-center">{{ $fmt($detail->palka_s_mni) }}</td>
                        <td class="text-center">{{ $fmt($detail->palka_p_palka) }}</td>
                        <td class="text-center">{{ $fmt($detail->palka_p_ffa) }}</td>
                        <td class="text-center">{{ $fmt($detail->palka_p_iv) }}</td>
                        <td class="text-center">{{ $fmt($detail->palka_p_colour) }}</td>
                        <td class="text-center">{{ $fmt($detail->palka_p_pv) }}</td>
                        <td class="text-center">{{ $fmt($detail->palka_p_mni) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center italic">No data available</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <table class="w-full report-table mb-6">
            <thead>
                <tr>
                    <th colspan="2">Hasil Analisa Komposit Palka</th>
                </tr>
            </thead>
            <tbody>
                <tr><td class="text-center">FFA</td><td class="text-center">{{ $fmt($header->hasil_analisa_ffa) }}</td></tr>
                <tr><td class="text-center">IV</td><td class="text-center">{{ $fmt($header->hasil_analisa_iv) }}</td></tr>
                <tr><td class="text-center">Moisture</td><td class="text-center">{{ $fmt($header->hasil_analisa_moisture) }}</td></tr>
                <tr><td class="text-center">Colour</td><td class="text-center">{{ $fmt($header->hasil_analisa_colour) }}</td></tr>
                <tr><td class="text-center">PV</td><td class="text-center">{{ $fmt($header->hasil_analisa_pv) }}</td></tr>
                <tr><td class="text-center">SMP</td><td class="text-center">{{ $fmt($header->hasil_analisa_smp) }}</td></tr>
            </tbody>
        </table>

        <div class="grid grid-cols-3 text-center mt-10 text-xs gap-4">
            <div>
                <strong>Done by</strong><br>
                {{ $header->entry_by ?? '-' }}<br><br>
                ( {{ $header->entry_by ?? '_______________________' }} )<br>
                <small>Date: {{ $header->entry_date ? \Carbon\Carbon::parse($header->entry_date)->format('d-m-Y H:i') : '' }}</small>
            </div>
            <div>
                <strong>Prepared by</strong><br>
                {{ $header->prepared_by ?? '-' }}<br><br>
                ( {{ $header->prepared_by ?? '_______________________' }} )<br>
                <small>Date: {{ $header->prepared_date ? \Carbon\Carbon::parse($header->prepared_date)->format('d-m-Y H:i') : '' }}</small>
            </div>
            <div>
                <strong>Approved by</strong><br>
                {{ $header->approved_by ?? '-' }}<br><br>
                ( {{ $header->approved_by ?? '_______________________' }} )<br>
                <small>Date: {{ $header->approved_date ? \Carbon\Carbon::parse($header->approved_date)->format('d-m-Y H:i') : '' }}</small>
            </div>
        </div>

        <div class="mt-6 text-center text-xs text-gray-600 italic">
            Dokumen ini telah disetujui secara elektronik melalui sistem [E-Logsheet],
            sehingga tidak memerlukan tanda tangan asli.
        </div>
    </div>
@endsection
