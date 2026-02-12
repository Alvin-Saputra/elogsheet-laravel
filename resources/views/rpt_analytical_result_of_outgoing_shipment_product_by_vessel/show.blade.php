@extends('layouts.app')

@section('page_title', 'Detail Report: ' . $header->id)

@section('content')
    @php
        $fmt = function ($val, $decimals = 4) {
            if ($val === null || $val === '') {
                return '-';
            }
            return number_format((float) $val, $decimals);
        };
    @endphp

    <div class="bg-white p-6 rounded shadow-md max-w-6xl mx-auto">
        <div class="mb-4">
            <a href="{{ route('analytical-result-outgoing-shipment-product-by-vessel.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 text-sm font-semibold rounded-lg shadow transition">
                &larr; Back to List
            </a>
        </div>

        <h2 class="text-2xl font-semibold text-gray-800 mb-4">
            Report ID: <span class="text-blue-600">{{ $header->id }}</span>
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div>
                <strong class="text-gray-600 block text-sm">Sampling Date:</strong>
                <span class="text-sm">{{ $header->sampling_date ? \Carbon\Carbon::parse($header->sampling_date)->format('Y-m-d') : 'N/A' }}</span>
            </div>
            <div>
                <strong class="text-gray-600 block text-sm">Product:</strong>
                <span class="text-sm">{{ $header->product_name ?? 'N/A' }}</span>
            </div>
            <div>
                <strong class="text-gray-600 block text-sm">Quantity:</strong>
                <span class="text-sm">{{ $header->quantity ?? 'N/A' }}</span>
            </div>

            <div>
                <strong class="text-gray-600 block text-sm">Vessel Name:</strong>
                <span class="text-sm">{{ $header->vessel_name ?? 'N/A' }}</span>
            </div>
            <div>
                <strong class="text-gray-600 block text-sm">Shipper:</strong>
                <span class="text-sm">{{ $header->shipper ?? 'N/A' }}</span>
            </div>
            <div>
                <strong class="text-gray-600 block text-sm">Destination:</strong>
                <span class="text-sm">{{ $header->destination ?? 'N/A' }}</span>
            </div>
        </div>

        <table class="min-w-full bg-white border border-gray-400 mb-6">
            <thead class="bg-gray-200 text-gray-800 text-sm">
                <tr>
                    <th colspan="12" class="px-3 py-2 border border-gray-400 text-center">Hasil Analisa Tiap Palka</th>
                </tr>
                <tr>
                    <th colspan="6" class="px-3 py-2 border border-gray-400 text-center">Palka S</th>
                    <th colspan="6" class="px-3 py-2 border border-gray-400 text-center">Palka P</th>
                </tr>
                <tr>
                    <th class="px-3 py-2 border border-gray-400 text-center">Palka No</th>
                    <th class="px-3 py-2 border border-gray-400 text-center">FFA</th>
                    <th class="px-3 py-2 border border-gray-400 text-center">IV</th>
                    <th class="px-3 py-2 border border-gray-400 text-center">Colour</th>
                    <th class="px-3 py-2 border border-gray-400 text-center">PV</th>
                    <th class="px-3 py-2 border border-gray-400 text-center">M&amp;I</th>

                    <th class="px-3 py-2 border border-gray-400 text-center">Palka No</th>
                    <th class="px-3 py-2 border border-gray-400 text-center">FFA</th>
                    <th class="px-3 py-2 border border-gray-400 text-center">IV</th>
                    <th class="px-3 py-2 border border-gray-400 text-center">Colour</th>
                    <th class="px-3 py-2 border border-gray-400 text-center">PV</th>
                    <th class="px-3 py-2 border border-gray-400 text-center">M&amp;I</th>
                </tr>
            </thead>

            <tbody class="text-gray-700 text-sm">
                @forelse ($header->details as $detail)
                    <tr>
                        <td class="px-3 py-1 border border-gray-400 text-center">{{ $fmt($detail->palka_s_palka) }}</td>
                        <td class="px-3 py-1 border border-gray-400 text-center">{{ $fmt($detail->palka_s_ffa) }}</td>
                        <td class="px-3 py-1 border border-gray-400 text-center">{{ $fmt($detail->palka_s_iv) }}</td>
                        <td class="px-3 py-1 border border-gray-400 text-center">{{ $fmt($detail->palka_s_colour) }}</td>
                        <td class="px-3 py-1 border border-gray-400 text-center">{{ $fmt($detail->palka_s_pv) }}</td>
                        <td class="px-3 py-1 border border-gray-400 text-center">{{ $fmt($detail->palka_s_mni) }}</td>

                        <td class="px-3 py-1 border border-gray-400 text-center">{{ $fmt($detail->palka_p_palka) }}</td>
                        <td class="px-3 py-1 border border-gray-400 text-center">{{ $fmt($detail->palka_p_ffa) }}</td>
                        <td class="px-3 py-1 border border-gray-400 text-center">{{ $fmt($detail->palka_p_iv) }}</td>
                        <td class="px-3 py-1 border border-gray-400 text-center">{{ $fmt($detail->palka_p_colour) }}</td>
                        <td class="px-3 py-1 border border-gray-400 text-center">{{ $fmt($detail->palka_p_pv) }}</td>
                        <td class="px-3 py-1 border border-gray-400 text-center">{{ $fmt($detail->palka_p_mni) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="px-3 py-2 text-center text-gray-500 italic">
                            No Palka data found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <table class="min-w-full bg-white border border-gray-400">
            <thead class="bg-gray-200 text-gray-800 text-sm">
                <tr>
                    <th colspan="2" class="px-3 py-2 border border-gray-400 text-center">Hasil Analisa Komposit Palka</th>
                </tr>
            </thead>
            <tbody class="text-gray-700 text-sm">
                <tr>
                    <td class="px-3 py-1 border border-gray-400 text-center">FFA</td>
                    <td class="px-3 py-1 border border-gray-400 text-center">{{ $fmt($header->hasil_analisa_ffa) }}</td>
                </tr>
                <tr>
                    <td class="px-3 py-1 border border-gray-400 text-center">IV</td>
                    <td class="px-3 py-1 border border-gray-400 text-center">{{ $fmt($header->hasil_analisa_iv) }}</td>
                </tr>
                <tr>
                    <td class="px-3 py-1 border border-gray-400 text-center">Moisture</td>
                    <td class="px-3 py-1 border border-gray-400 text-center">{{ $fmt($header->hasil_analisa_moisture) }}</td>
                </tr>
                <tr>
                    <td class="px-3 py-1 border border-gray-400 text-center">Colour</td>
                    <td class="px-3 py-1 border border-gray-400 text-center">{{ $fmt($header->hasil_analisa_colour) }}</td>
                </tr>
                <tr>
                    <td class="px-3 py-1 border border-gray-400 text-center">PV</td>
                    <td class="px-3 py-1 border border-gray-400 text-center">{{ $fmt($header->hasil_analisa_pv) }}</td>
                </tr>
                <tr>
                    <td class="px-3 py-1 border border-gray-400 text-center">SMP</td>
                    <td class="px-3 py-1 border border-gray-400 text-center">{{ $fmt($header->hasil_analisa_smp) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection
