{{-- Tabel --}}
{{-- @php
    $isRef01 = $workCenter === 'REF-01';
@endphp --}}

<div class="overflow-x-auto">
    <table class="min-w-full border border-gray-400 text-center text-xs">
        <thead class="bg-gray-100">
            <tr>
                <th rowspan="2" class="border px-2 py-1">Time</th>
                <th rowspan="2" class="border px-2 py-1 bg-yellow-300">From Tank</th>
                <th rowspan="2" class="border px-2 py-1 bg-orange-300">Flow Rate T/H</th>

                <th colspan="10" class="border px-2 py-1 bg-green-100">CPO</th>
                <th colspan="4" class="border px-2 py-1 bg-teal-100">BPO</th>
                {{-- @if ($isRef01) --}}
                    {{-- <th colspan="9" class="border px-2 py-1 bg-purple-100">RBDPO</th> --}}
                {{-- @else --}}
                    <th colspan="10" class="border px-2 py-1 bg-purple-100">RBDPO</th>
                {{-- @endif --}}
                <th colspan="3" class="border px-2 py-1 bg-yellow-100">PFAD</th>
                <th colspan="2" class="border px-2 py-1 bg-orange-100">Spent Earth</th>
                <th rowspan="2" class="border px-2 py-1">Remarks</th>
            </tr>
            <tr>
                {{-- CPO --}}
                <th class="border px-1 py-1">FFA %</th>
                <th class="border px-1 py-1">M&I %</th>
                <th class="border px-1 py-1">DOBI</th>
                <th class="border px-1 py-1">IV</th>
                <th class="border px-1 py-1">PV</th>
                <th class="border px-1 py-1">AV</th>
                <th class="border px-1 py-1">Totox</th>
                <th class="border px-1 py-1">R</th>
                <th class="border px-1 py-1">Y</th>
                <th class="border px-1 py-1">B</th>

                {{-- BPO --}}
                <th class="border px-1 py-1">R</th>
                <th class="border px-1 py-1">Y</th>
                <th class="border px-1 py-1">W/B</th>
                <th class="border px-1 py-1">Break Test</th>

                {{-- RBDPO --}}
                <th class="border px-1 py-1">FFA</th>
                <th class="border px-1 py-1">Moist</th>
                {{-- @if ($isRef01) --}}
                    {{-- <th class="border px-1 py-1 bg-gray-50 text-gray-400"></th> --}}
                {{-- @else --}}
                    <th class="border px-1 py-1">IMP</th>
                {{-- @endif --}}
                <th class="border px-1 py-1">IV</th>
                <th class="border px-1 py-1">PV</th>
                <th class="border px-1 py-1">R</th>
                <th class="border px-1 py-1">Y</th>
                <th class="border px-1 py-1">W/B</th>
                <th class="border px-1 py-1">To Tank</th>
                <th class="border px-1 py-1">Remarks</th>

                {{-- PFAD --}}
                <th class="border px-1 py-1">FFA %</th>
                <th class="border px-1 py-1">M&I %</th>
                <th class="border px-1 py-1">To Tank</th>

                {{-- SBE --}}
                <th class="border px-1 py-1">M&I %</th>
                <th class="border px-1 py-1">OC %</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td class="border px-1 py-1">{{ optional($row->time)->format('H:i') }}</td>
                    <td class="border px-1 py-1">{{ $row->rm_tank_source }}</td>
                    <td class="border px-1 py-1">{{ $row->rm_flowrate }}</td>

                    {{-- CPO --}}
                    <td class="border px-1 py-1">{{ $row->rm_ffa }}</td>
                    <td class="border px-1 py-1">{{ $row->{'rm_m&i'} }}</td>
                    <td class="border px-1 py-1">{{ $row->rm_dobi }}</td>
                    <td class="border px-1 py-1">{{ $row->rm_iv }}</td>
                    <td class="border px-1 py-1">{{ $row->rm_pv }}</td>
                    <td class="border px-1 py-1">{{ $row->rm_av }}</td>
                    <td class="border px-1 py-1">{{ $row->rm_totox }}</td>
                    <td class="border px-1 py-1">{{ $row->rm_color_r }}</td>
                    <td class="border px-1 py-1">{{ $row->rm_color_y }}</td>
                    <td class="border px-1 py-1">{{ $row->rm_color_b }}</td>

                    {{-- BPO --}}
                    <td class="border px-1 py-1">{{ $row->bo_color_r }}</td>
                    <td class="border px-1 py-1">{{ $row->bo_color_y }}</td>
                    <td class="border px-1 py-1">{{ $row->bo_color_b }}</td>
                    <td class="border px-1 py-1">{{ $row->bo_break_test }}</td>

                    {{-- RBDPO --}}
                    <td class="border px-1 py-1">{{ $row->fg_ffa }}</td>
                    {{-- ini --}}
                    <td class="border px-1 py-1">{{ $row->fg_moisture }}</td>
                    {{-- @if ($isRef01) --}}
                        {{-- <td class="border px-1 py-1 bg-gray-50 text-gray-400"></td> --}}
                    {{-- @else --}}
                        <td class="border px-1 py-1">{{ $row->fg_impurities }}</td>
                    {{-- @endif --}}
                    <td class="border px-1 py-1">{{ $row->fg_iv }}</td>
                    <td class="border px-1 py-1">{{ $row->fg_pv }}</td>
                    <td class="border px-1 py-1">{{ $row->fg_color_r }}</td>
                    <td class="border px-1 py-1">{{ $row->fg_color_y }}</td>
                    <td class="border px-1 py-1">{{ $row->fg_color_b }}</td>
                    <td class="border px-1 py-1">{{ $row->fg_tank_to }}</td>
                    <td class="border px-1 py-1">{{ $row->fg_tank_to_others_remarks }}</td>


                    {{-- PFAD --}}
                    <td class="border px-1 py-1">{{ $row->bp_ffa }}</td>
                    <td class="border px-1 py-1">{{ $row->{'bp_m&i'} }}</td>
                    <td class="border px-1 py-1">{{ $row->bp_to_tank }}</td>

                    {{-- SBE --}}
                    <td class="border px-1 py-1">{{ $row->{'w_sbe_m&i'} }}</td>
                    <td class="border px-1 py-1">{{ $row->w_sbe_qc }}</td>

                    <td class="border px-1 py-1">{{ $row->remarks }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
