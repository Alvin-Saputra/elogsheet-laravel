<div class="overflow-x-auto mb-6">
    <table class="min-w-full border border-gray-400 text-center text-xs">
        <thead class="bg-gray-100 font-bold">
            <tr>
                {{-- HEADER COLUMNS --}}
                <th class="border border-gray-400 p-2 align-middle">Crystallizer<br>(Batch #)</th>
                <th class="border border-gray-400 p-2 align-middle">Filling<br>Start</th>
                <th class="border border-gray-400 p-2 align-middle">Filling<br>End</th>
                <th class="border border-gray-400 p-2 align-middle">Cooling<br>Start</th>
                <th class="border border-gray-400 p-2 align-middle">Oil Level<br>(%)</th>
                <th class="border border-gray-400 p-2 align-middle">Agitator<br>(Hz)</th>
                <th class="border border-gray-400 p-2 align-middle">Pump<br>(Bar)</th>

                {{-- DETAIL COLUMNS --}}
                <th class="border border-gray-400 p-2 align-middle bg-blue-50">Cycle #</th>
                <th class="border border-gray-400 p-2 align-middle bg-blue-50">Filt. Temp</th>
                <th class="border border-gray-400 p-2 align-middle bg-blue-50">Time Start Filtration</th>
                <th class="border border-gray-400 p-2 align-middle bg-blue-50">Time End Filtration</th>
                <th class="border border-gray-400 p-2 align-middle bg-blue-50">Load (%)</th>
                <th class="border border-gray-400 p-2 align-middle bg-blue-50">Olein IV</th>
                <th class="border border-gray-400 p-2 align-middle bg-blue-50">Olein CP(°C)</th>
                <th class="border border-gray-400 p-2 align-middle bg-blue-50">Olein FFA</th>
                <th class="border border-gray-400 p-2 align-middle bg-blue-50">Olein Color(Red)</th>
                <th class="border border-gray-400 p-2 align-middle bg-blue-50">Stearin IV</th>
                <th class="border border-gray-400 p-2 align-middle bg-blue-50">Stearin FFA</th>
                <th class="border border-gray-400 p-2 align-middle bg-blue-50">Stearin Color (Red)</th>
                <th class="border border-gray-400 p-2 align-middle bg-blue-50">Stearin PV</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($headers as $header)
                @php
                    // Count how many details exist for this header to calculate rowspan
                    $rowCount = $header->details->count();
                @endphp

                @if ($rowCount > 0)
                    {{-- LOOP THROUGH DETAILS --}}
                    @foreach ($header->details as $index => $detail)
                        <tr class="hover:bg-gray-50">

                            {{-- HEADER DATA (Only render on the first row of the group) --}}
                            @if ($index === 0)
                                <td class="border border-gray-400 p-1 align-middle bg-white"
                                    rowspan="{{ $rowCount }}">
                                    {{ $header->crystallizer }}
                                </td>
                                <td class="border border-gray-400 p-1 align-middle bg-white"
                                    rowspan="{{ $rowCount }}">
                                    {{ \Carbon\Carbon::parse($header->filling_start_time)->format('H:i') }}
                                </td>
                                <td class="border border-gray-400 p-1 align-middle bg-white"
                                    rowspan="{{ $rowCount }}">
                                    {{ \Carbon\Carbon::parse($header->filling_end_time)->format('H:i') }}
                                </td>
                                <td class="border border-gray-400 p-1 align-middle bg-white"
                                    rowspan="{{ $rowCount }}">
                                    {{ \Carbon\Carbon::parse($header->cooling_start_time)->format('H:i') }}
                                </td>
                                <td class="border border-gray-400 p-1 align-middle bg-white"
                                    rowspan="{{ $rowCount }}">
                                    {{ $header->initial_oil_level }}
                                </td>
                                <td class="border border-gray-400 p-1 align-middle bg-white"
                                    rowspan="{{ $rowCount }}">
                                    {{ $header->agitator_speed }}
                                </td>
                                <td class="border border-gray-400 p-1 align-middle bg-white"
                                    rowspan="{{ $rowCount }}">
                                    {{ $header->water_pump_pres }}
                                </td>
                            @endif

                            {{-- DETAIL DATA (Rendered every row) --}}
                            <td class="border border-gray-400 p-1 text-gray-700 bg-blue-50/30">
                                {{ $detail->filtration_cycle_number }}
                            </td>
                            <td class="border border-gray-400 p-1 text-gray-700 bg-blue-50/30">
                                {{ $detail->filtration_temp }}°C
                            </td>
                            <td class="border border-gray-400 p-1 text-gray-700 bg-blue-50/30">
                                {{ $detail->time_start_filtration ? \Carbon\Carbon::parse($detail->time_start_filtration)->format('H:i') : '-' }}
                            </td>
                            <td class="border border-gray-400 p-1 text-gray-700 bg-blue-50/30">
                                {{ $detail->time_end_filtration ? \Carbon\Carbon::parse($detail->time_end_filtration)->format('H:i') : '-' }}
                            </td>
                            <td class="border border-gray-400 p-1 text-gray-700 bg-blue-50/30">
                                {{ $detail->load }}
                            </td>
                            <td class="border border-gray-400 p-1 text-gray-700 bg-blue-50/30">
                                {{ $detail->olein_iv }}
                            </td>
                            <td class="border border-gray-400 p-1 text-gray-700 bg-blue-50/30">
                                {{ $detail->olein_cp }}
                            </td>
                            <td class="border border-gray-400 p-1 text-gray-700 bg-blue-50/30">
                                {{ $detail->olein_ffa }}
                            </td>
                            <td class="border border-gray-400 p-1 text-gray-700 bg-blue-50/30">
                                {{ $detail->olein_color_red }}
                            </td>
                            <td class="border border-gray-400 p-1 text-gray-700 bg-blue-50/30">
                                {{ $detail->stearin_iv }}
                            </td>
                            <td class="border border-gray-400 p-1 text-gray-700 bg-blue-50/30">
                                {{ $detail->stearin_ffa }}
                            </td>
                            <td class="border border-gray-400 p-1 text-gray-700 bg-blue-50/30">
                                {{ $detail->stearin_color_red }}
                            </td>
                            <td class="border border-gray-400 p-1 text-gray-700 bg-blue-50/30">
                                {{ $detail->stearin_pv }}
                            </td>
                        </tr>
                    @endforeach
                @else
                    {{-- FALLBACK: IF NO DETAILS EXIST (Just show header info with empty detail cells) --}}
                    <tr>
                        <td class="border border-gray-400 p-1">{{ $header->crystallizer }}</td>
                        <td class="border border-gray-400 p-1">{{ $header->filling_start_time }}</td>
                        <td class="border border-gray-400 p-1">{{ $header->filling_end_time }}</td>
                        <td class="border border-gray-400 p-1">{{ $header->cooling_start_time }}</td>
                        <td class="border border-gray-400 p-1">{{ $header->initial_oil_level }}</td>
                        <td class="border border-gray-400 p-1">{{ $header->agitator_speed }}</td>
                        <td class="border border-gray-400 p-1">{{ $header->water_pump_pres }}</td>

                        {{-- Empty Detail Cells --}}
                        <td class="border border-gray-400 p-1 bg-gray-50 text-gray-400 italic" colspan="6">
                            No Filtration Data
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div>
