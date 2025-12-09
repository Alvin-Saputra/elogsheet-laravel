@extends('layouts.app')

@section('page_title', 'Detail Report: ' . $header->id)

@section('content')
    <div class="bg-white p-6 rounded shadow-md max-w-4xl mx-auto">

        <div class="mb-4">
            <a href="{{ url()->previous() }}"
                class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 text-sm font-semibold rounded-lg shadow transition">
                &larr; Back to List
            </a>
        </div>

        <h2 class="text-2xl font-semibold text-gray-800 mb-4">
            Report ID: <span class="text-blue-600">{{ $header->id }}</span>
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div>
                <strong class="text-gray-600 block text-sm">Arrival Date:</strong>
                <span class="text-sm">{{ $header->arrival_date->format('Y-m-d H:i') }}</span>
            </div>

            <div>
                <strong class="text-gray-600 block text-sm">Material:</strong>
                <span class="text-sm">{{ $header->material ?? 'N/A' }}</span>
            </div>


            <div>
                <strong class="text-gray-600 block text-sm">Ship's Name:</strong>
                <span class="text-sm">{{ $header->vessel_vehicle ?? 'N/A' }}</span>
            </div>

            <div>
                <strong class="text-gray-600 block text-sm">Contract/Do Number:</strong>
                <span class="text-sm">{{ $header->contract_do ?? 'N/A' }}</span>
            </div>

            <div>
                <strong class="text-gray-600 block text-sm">FFA:</strong>
                <span class="text-sm">{{ $header->ss_ffa ?? 'N/A' }}</span>
            </div>

            <div>
                <strong class="text-gray-600 block text-sm">M&I:</strong>
                <span class="text-sm">{{ $header->ss_mni ?? 'N/A' }}</span>
            </div>

            <div>
                <strong class="text-gray-600 block text-sm">Others:</strong>
                <span class="text-sm">{{ $header->ss_others ?? 'N/A' }}</span>
            </div>

        </div>

        <br>

        <table class="min-w-full bg-white border border-gray-400">
            <thead class="bg-gray-200 text-gray-800 text-sm">
                <tr>
                    <th rowspan="2" class="px-3 py-2 border border-gray-400 text-center">No</th>
                    <th rowspan="2" class="px-3 py-2 border border-gray-400 text-center">Sampling Date</th>
                    <th rowspan="2" class="px-3 py-2 border border-gray-400 text-center">Police No</th>
                    <th colspan="15" class="px-3 py-2 border border-gray-400 text-center">Parameter



                </tr>
                <tr>
                    <th class="px-3 py-2 border border-gray-400 text-center">FFA</th>
                    <th class="px-3 py-2 border border-gray-400 text-center">Moisture</th>
                    <th class="px-3 py-2 border border-gray-400 text-center">IV</th>
                    <th class="px-3 py-2 border border-gray-400 text-center">DOBI</th>
                    <th class="px-3 py-2 border border-gray-400 text-center">PV</th>
                    <th class="px-3 py-2 border border-gray-400 text-center">Color R</th>
                    <th class="px-3 py-2 border border-gray-400 text-center">Color Y</th>
                    <th class="px-3 py-2 border border-gray-400 text-center">Analis</th>
                    <th class="px-3 py-2 border border-gray-400 text-center">Remark</th>
                </tr>

            </thead>

            <tbody class="text-gray-700 text-sm">
                @forelse ($header->details as $detail)
                    <tr>
                        {{-- Palka S --}}
                        <td class="px-3 py-1 border border-gray-400 text-center">
                            {{ $detail->no ?? '-' }}
                        </td>
                        <td class="px-3 py-1 border border-gray-400 text-center">
                            {{ $detail->sampling_date?->format('Y-m-d') ?? '-' }}
                        </td>
                        <td class="px-3 py-1 border border-gray-400 text-center">
                            {{ $detail->police_no ?? '-' }}
                        </td>
                        <td class="px-3 py-1 border border-gray-400 text-center">
                            {{ $detail->p_ffa ?? '-' }}
                        </td>
                        <td class="px-3 py-1 border border-gray-400 text-center">
                            {{ $detail->p_moisture ?? '-' }}
                        </td>

                        <td class="px-3 py-1 border border-gray-400 text-center">
                            {{ $detail->p_iv ?? '-' }}
                        </td>

                        <td class="px-3 py-1 border border-gray-400 text-center">
                            {{ $detail->p_dobi ?? '-' }}
                        </td>

                        <td class="px-3 py-1 border border-gray-400 text-center">
                            {{ $detail->p_pv ?? '-' }}
                        </td>

                        <td class="px-3 py-1 border border-gray-400 text-center">
                            {{ $detail->p_color_r ?? '-' }}
                        </td>

                        <td class="px-3 py-1 border border-gray-400 text-center">
                            {{ $detail->p_color_y ?? '-' }}
                        </td>

                        <td class="px-3 py-1 border border-gray-400 text-center">
                            {{ $detail->analis ?? '-' }}
                        </td>

                        <td class="px-3 py-1 border border-gray-400 text-center">
                            {{ $detail->remarks ?? '-' }}
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


    </div>
@endsection
