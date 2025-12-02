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
                <span class="text-sm">{{ $header->arrival->format('Y-m-d H:i') }}</span>
            </div>

            <div>
                <strong class="text-gray-600 block text-sm">Material:</strong>
                <span class="text-sm">{{ $header->material ?? 'N/A' }}</span>
            </div>

            <div>
                <strong class="text-gray-600 block text-sm">Quantity:</strong>
                <span class="text-sm">{{ $header->quantity ?? 'N/A' }}</span>
            </div>

            <div>
                <strong class="text-gray-600 block text-sm">Ship's Name:</strong>
                <span class="text-sm">{{ $header->shipName ?? 'N/A' }}</span>
            </div>

            <div>
                <strong class="text-gray-600 block text-sm">Contract/Do Number:</strong>
                <span class="text-sm">{{ $header->contract_do_nomor ?? 'N/A' }}</span>
            </div>

            <div>
                <strong class="text-gray-600 block text-sm">FFA:</strong>
                <span class="text-sm">{{ $header->ffa ?? 'N/A' }}</span>
            </div>

            <div>
                <strong class="text-gray-600 block text-sm">M&I:</strong>
                <span class="text-sm">{{ $header->mni ?? 'N/A' }}</span>
            </div>

            <div>
                <strong class="text-gray-600 block text-sm">DOBI:</strong>
                <span class="text-sm">{{ $header->dobi ?? 'N/A' }}</span>
            </div>

            <div>
                <strong class="text-gray-600 block text-sm">Others:</strong>
                <span class="text-sm">{{ $header->others ?? 'N/A' }}</span>
            </div>

        </div>

        <br>

        <table class="min-w-full bg-white border border-gray-400">
            <thead class="bg-gray-200 text-gray-800 text-sm">
                <tr>
                    <th colspan="15" class="px-3 py-2 border border-gray-400 text-center">Hasil Analisa Tiap Palka
                    </th>

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
                    <td class="px-3 py-1 border border-gray-400 text-center">Dobi</td>
                    <td class="px-3 py-1 border border-gray-400 text-center">{{ $header->hasil_analisa_dobi }}</td>
                </tr>

                <tr>
                    <td class="px-3 py-1 border border-gray-400 text-center">PV</td>
                    <td class="px-3 py-1 border border-gray-400 text-center">{{ $header->hasil_analisa_pv }}</td>
                </tr>

                <tr>
                    <td class="px-3 py-1 border border-gray-400 text-center">AnV</td>
                    <td class="px-3 py-1 border border-gray-400 text-center">{{ $header->hasil_analisa_anv }}</td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection
