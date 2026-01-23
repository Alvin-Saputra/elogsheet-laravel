@extends('layouts.app')

@section('page_title', 'Detail Logsheet Dry Fractionation')

@section('content')
    <div class="max-w-6xl mx-auto">

        {{-- ================= HEADER & BACK BUTTON ================= --}}
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-3">
                <div class="bg-blue-100 p-2 rounded-full">
                    {{-- Icon: Beaker/Science --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-800">Detail Logsheet</h3>
                    <p class="text-sm text-gray-500">
                        Dry Fractionation | {{ \Carbon\Carbon::parse($header->date)->format('d F Y') }}
                    </p>
                </div>
            </div>

            <a href="{{ route('logsheet-monitoring-dry-fractionation.index') }}"
                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                Kembali
            </a>
        </div>

        <div class="space-y-6">

            {{-- ================= SECTION 1: BATCH INFORMATION (HEADER) ================= --}}
            <div class="bg-white p-6 rounded-xl shadow-md">
                <h4 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Batch Information</h4>
                <div class="lg:col-span-4 grid grid-cols-1 md:grid-cols-3 gap-4 bg-gray-50 p-4 rounded-lg mt-2">
                    
                    {{-- Crystallizer Info --}}
                    <div>
                        <span class="block text-xs font-bold text-gray-500 uppercase">Crystallizer (Batch #)</span>
                        <span class="block text-gray-800 font-bold text-lg">{{ $header->crystallizer }}</span>
                    </div>

                    {{-- Filling Times --}}
                    <div>
                        <span class="block text-xs font-bold text-gray-500 uppercase">Filling Time</span>
                        <span class="block text-gray-800 font-medium">
                            {{ \Carbon\Carbon::parse($header->filling_start_time)->format('H:i') }} - 
                            {{ \Carbon\Carbon::parse($header->filling_end_time)->format('H:i') }}
                        </span>
                    </div>

                    {{-- Cooling Info --}}
                    <div>
                        <span class="block text-xs font-bold text-gray-500 uppercase">Cooling Start</span>
                        <span class="block text-gray-800 font-medium">
                            {{ \Carbon\Carbon::parse($header->cooling_start_time)->format('H:i') }}
                        </span>
                        <span class="text-xs text-gray-500">Temp: {{ $header->cooling_start_temp ?? '-' }}°C</span>
                    </div>

                    {{-- Feed Oil IV --}}
                    <div>
                        <span class="block text-xs font-bold text-gray-500 uppercase">Feed Oil IV</span>
                        <span class="block text-gray-800 font-medium">{{ $header->feed_oil_iv ?? '-' }}</span>
                    </div>

                    {{-- Process Parameters --}}
                    <div class="lg:col-span-4 grid grid-cols-1 md:grid-cols-3 gap-4 bg-gray-50 p-4 rounded-lg mt-2">
                        <div>
                            <span class="block text-xs font-bold text-gray-500 uppercase">Initial Oil Level</span>
                            <span class="block text-gray-800 font-bold">{{ $header->initial_oil_level }} %</span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-gray-500 uppercase">Agitator Speed</span>
                            <span class="block text-gray-800 font-bold">{{ $header->agitator_speed }} Hz</span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-gray-500 uppercase">Water Pump Pressure</span>
                            <span class="block text-gray-800 font-bold">{{ $header->water_pump_pres }} Bar</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= SECTION 2: FILTRATION DETAILS (TABLE) ================= --}}
            <div class="bg-white p-6 rounded-xl shadow-md">
                <div class="flex justify-between items-center border-b pb-2 mb-4">
                    <h4 class="text-lg font-bold text-gray-800">Filtration Details</h4>
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">
                        {{ $header->details->count() }} Cycles
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs text-center border-collapse">
                        <thead class="bg-gray-100 text-gray-700 font-bold uppercase tracking-wider">
                            <tr>
                                <th rowspan="2" class="p-2 border">Cycle #</th>
                                <th rowspan="2" class="p-2 border">Temp (°C)</th>
                                <th colspan="2" class="p-2 border">Time</th>
                                <th rowspan="2" class="p-2 border">Load (%)</th>
                                <th colspan="4" class="p-2 border bg-blue-50 text-blue-800">Olein Analysis</th>
                                <th colspan="4" class="p-2 border bg-green-50 text-green-800">Stearin Analysis</th>
                            </tr>
                            <tr>
                                {{-- Time Sub-headers --}}
                                <th class="p-2 border">Start</th>
                                <th class="p-2 border">End</th>

                                {{-- Olein Sub-headers --}}
                                <th class="p-2 border bg-blue-50">IV</th>
                                <th class="p-2 border bg-blue-50">CP</th>
                                <th class="p-2 border bg-blue-50">FFA</th>
                                <th class="p-2 border bg-blue-50">Color</th>

                                {{-- Stearin Sub-headers --}}
                                <th class="p-2 border bg-green-50">IV</th>
                                <th class="p-2 border bg-green-50">FFA</th>
                                <th class="p-2 border bg-green-50">Color</th>
                                <th class="p-2 border bg-green-50">PV</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($header->details as $detail)
                                <tr class="hover:bg-gray-50">
                                    <td class="p-2 border font-medium">{{ $detail->filtration_cycle_number }}</td>
                                    <td class="p-2 border">{{ $detail->filtration_temp }}</td>
                                    <td class="p-2 border whitespace-nowrap">
                                        {{ $detail->time_start_filtration ? \Carbon\Carbon::parse($detail->time_start_filtration)->format('H:i') : '-' }}
                                    </td>
                                    <td class="p-2 border whitespace-nowrap">
                                        {{ $detail->time_end_filtration ? \Carbon\Carbon::parse($detail->time_end_filtration)->format('H:i') : '-' }}
                                    </td>
                                    <td class="p-2 border">{{ $detail->load }}</td>
                                    
                                    {{-- Olein Data --}}
                                    <td class="p-2 border bg-blue-50/30">{{ $detail->olein_iv }}</td>
                                    <td class="p-2 border bg-blue-50/30">{{ $detail->olein_cp }}</td>
                                    <td class="p-2 border bg-blue-50/30">{{ $detail->olein_ffa }}</td>
                                    <td class="p-2 border bg-blue-50/30">{{ $detail->olein_color_red }}</td>

                                    {{-- Stearin Data --}}
                                    <td class="p-2 border bg-green-50/30">{{ $detail->stearin_iv }}</td>
                                    <td class="p-2 border bg-green-50/30">{{ $detail->stearin_ffa }}</td>
                                    <td class="p-2 border bg-green-50/30">{{ $detail->stearin_color_red }}</td>
                                    <td class="p-2 border bg-green-50/30">{{ $detail->stearin_pv }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="p-4 text-center text-gray-400 italic">No filtration details found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ================= SECTION 3: APPROVAL STATUS ================= --}}
            <div class="bg-white p-6 rounded-xl shadow-md">
                <h4 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Approval Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- LEADER APPROVAL --}}
                    <div class="border-l-4 {{ $header->prepared_status === 'Approved' ? 'border-green-500' : ($header->prepared_status === 'Rejected' ? 'border-red-500' : 'border-gray-300') }} bg-gray-50 p-4 rounded-r-lg">
                        <span class="block text-xs font-bold text-gray-500 uppercase mb-2">Shift Leader</span>
                        
                        <div class="mb-2">
                            <span class="font-bold text-sm text-gray-700">Status:</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $header->prepared_status === 'Approved' ? 'bg-green-100 text-green-800' : 
                                  ($header->prepared_status === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ $header->prepared_status ?? 'Pending' }}
                            </span>
                        </div>

                        <div class="text-sm text-gray-700 mb-1">
                            <span class="font-bold">By:</span> {{ $header->prepared_by ?? '-' }}
                        </div>
                        <div class="text-sm text-gray-700">
                            <span class="font-bold">Date:</span> 
                            {{ $header->prepared_date ? \Carbon\Carbon::parse($header->prepared_date)->format('d M Y H:i') : '-' }}
                        </div>

                        @if ($header->prepared_note)
                             <div class="mt-3 text-xs text-red-700 bg-red-100 p-2 rounded border border-red-200">
                                <strong>Remark:</strong> {{ $header->prepared_note }}
                            </div>
                        @endif
                    </div>

                    {{-- MANAGER APPROVAL --}}
                    <div class="border-l-4 {{ $header->checked_status === 'Approved' ? 'border-green-500' : ($header->checked_status === 'Rejected' ? 'border-red-500' : 'border-gray-300') }} bg-gray-50 p-4 rounded-r-lg">
                        <span class="block text-xs font-bold text-gray-500 uppercase mb-2">Production Manager</span>
                        
                        <div class="mb-2">
                            <span class="font-bold text-sm text-gray-700">Status:</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $header->checked_status === 'Approved' ? 'bg-green-100 text-green-800' : 
                                  ($header->checked_status === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ $header->checked_status ?? 'Pending' }}
                            </span>
                        </div>

                        <div class="text-sm text-gray-700 mb-1">
                            <span class="font-bold">By:</span> {{ $header->checked_by ?? '-' }}
                        </div>
                        <div class="text-sm text-gray-700">
                            <span class="font-bold">Date:</span> 
                            {{ $header->checked_date ? \Carbon\Carbon::parse($header->checked_date)->format('d M Y H:i') : '-' }}
                        </div>

                        @if ($header->checked_note)
                            <div class="mt-3 text-xs text-red-700 bg-red-100 p-2 rounded border border-red-200">
                                <strong>Remark:</strong> {{ $header->checked_note }}
                            </div>
                        @endif
                    </div>

                </div>
            </div>

        </div>
    </div>
@endsection