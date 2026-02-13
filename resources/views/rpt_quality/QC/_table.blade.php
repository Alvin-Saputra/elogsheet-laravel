{{-- Tabel --}}
@php
    $isRef01 = $workCenter === 'REF-01';
@endphp

<div class="overflow-x-auto">
    <table class="min-w-full border border-gray-400 text-center text-xs">
        <thead class="bg-gray-100">
            <tr>
                <th rowspan="2" class="border px-2 py-1">Time</th>
                <th rowspan="2" class="border px-2 py-1 bg-blue-100">Oil Type</th>
                <th rowspan="2" class="border px-2 py-1 bg-indigo-100">Finish Good</th>
                <th rowspan="2" class="border px-2 py-1 bg-indigo-100">By Product</th>
                <th rowspan="2" class="border px-2 py-1 bg-yellow-300">From Tank</th>
                <th rowspan="2" class="border px-2 py-1 bg-orange-300">Flow Rate T/H</th>

                <th colspan="10" class="border px-2 py-1 bg-green-100">Raw Material</th>
                <th colspan="4" class="border px-2 py-1 bg-teal-100">Bleaching Oil</th>
                @if ($isRef01)
                    <th colspan="9" class="border px-2 py-1 bg-purple-100">Finish Good</th>
                @else
                    <th colspan="10" class="border px-2 py-1 bg-purple-100">Finish Good</th>
                @endif
                <th colspan="3" class="border px-2 py-1 bg-yellow-100">By Product</th>
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
                <th class="border px-1 py-1">{{ $isRef01 ? 'M&I' : 'Moist' }}</th>
                @if ($isRef01 == false)
                    <th class="border px-1 py-1">IMP</th>
                @endif
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
                    {{-- Time: Pastikan menggunakan optional() karena dummy object punya property time, tapi untuk jaga-jaga --}}
                    <td class="border px-1 py-1">{{ optional($row->time)->format('H:i') }}</td>

                    {{-- Oil Type & FG: Gunakan ?? '-' untuk default value --}}
                    <td class="border px-1 py-1 font-semibold">
                        {{ $row->oil_type ?? '-' }}
                    </td>
                    <td class="border px-1 py-1 font-semibold">
                        {{ $row->oil_type_fg ?? '-' }}
                    </td>
                    <td class="border px-1 py-1 font-semibold">
                        {{ $row->oil_type_bp ?? '-' }}
                    </td>

                    {{-- From Tank & Flow Rate --}}
                    {{-- ERROR SEBELUMNYA DISINI: $row->rm_tank_source --}}
                    {{-- SOLUSI: $row->rm_tank_source ?? '' --}}
                    <td class="border px-1 py-1">{{ $row->rm_tank_source ?? '' }}</td>
                    <td class="border px-1 py-1">{{ $row->rm_flowrate ?? '' }}</td>

                    {{-- CPO --}}
                    <td class="border px-1 py-1">{{ $row->rm_ffa ?? '' }}</td>
                    {{-- Perhatikan penulisan property dengan karakter khusus & --}}
                    <td class="border px-1 py-1">{{ $row->{'rm_m&i'} ?? '' }}</td>
                    <td class="border px-1 py-1">{{ $row->rm_dobi ?? '' }}</td>
                    <td class="border px-1 py-1">{{ $row->rm_iv ?? '' }}</td>
                    <td class="border px-1 py-1">{{ $row->rm_pv ?? '' }}</td>
                    <td class="border px-1 py-1">{{ $row->rm_av ?? '' }}</td>
                    <td class="border px-1 py-1">{{ $row->rm_totox ?? '' }}</td>
                    <td class="border px-1 py-1">{{ $row->rm_color_r ?? '' }}</td>
                    <td class="border px-1 py-1">{{ $row->rm_color_y ?? '' }}</td>
                    <td class="border px-1 py-1">{{ $row->rm_color_b ?? '' }}</td>

                    {{-- BPO --}}
                    <td class="border px-1 py-1">{{ $row->bo_color_r ?? '' }}</td>
                    <td class="border px-1 py-1">{{ $row->bo_color_y ?? '' }}</td>
                    <td class="border px-1 py-1">{{ $row->bo_color_b ?? '' }}</td>
                    <td class="border px-1 py-1">{{ $row->bo_break_test ?? '' }}</td>

                    {{-- RBDPO --}}
                    <td class="border px-1 py-1">{{ $row->fg_ffa ?? '' }}</td>
                    <td class="border px-1 py-1">{{ $row->fg_moisture ?? '' }}</td>

                    @if (!$isRef01)
                        <td class="border px-1 py-1">{{ $row->fg_impurities ?? '' }}</td>
                    @endif

                    <td class="border px-1 py-1">{{ $row->fg_iv ?? '' }}</td>
                    <td class="border px-1 py-1">{{ $row->fg_pv ?? '' }}</td>
                    <td class="border px-1 py-1">{{ $row->fg_color_r ?? '' }}</td>
                    <td class="border px-1 py-1">{{ $row->fg_color_y ?? '' }}</td>
                    <td class="border px-1 py-1">{{ $row->fg_color_b ?? '' }}</td>
                    <td class="border px-1 py-1">{{ $row->fg_tank_to ?? '' }}</td>
                    <td class="border px-1 py-1">{{ $row->fg_tank_to_others_remarks ?? '' }}</td>

                    {{-- PFAD --}}
                    <td class="border px-1 py-1">{{ $row->bp_ffa ?? '' }}</td>
                    <td class="border px-1 py-1">{{ $row->{'bp_m&i'} ?? '' }}</td>
                    <td class="border px-1 py-1">{{ $row->bp_to_tank ?? '' }}</td>

                    {{-- SBE --}}
                    <td class="border px-1 py-1">{{ $row->{'w_sbe_m&i'} ?? '' }}</td>
                    <td class="border px-1 py-1">{{ $row->w_sbe_qc ?? '' }}</td>

                    {{-- Remarks --}}
                    <td class="border px-1 py-1">{{ $row->remarks ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>

        {{-- Baris Average --}}
        <tfoot class="bg-gray-200 font-bold">
            <tr>
                {{-- Gabungkan 5 kolom pertama (Time, Oil Types, From Tank) menjadi label "AVERAGE" --}}
                <td colspan="5" class="border px-2 py-1 text-center">AVERAGE</td>

                {{-- Flow Rate --}}
                <td class="border px-1 py-1">{{ number_format($rows->avg('rm_flowrate'), 2) }}</td>

                {{-- CPO (10 Kolom) --}}
                <td class="border px-1 py-1">{{ number_format($rows->avg('rm_ffa'), 3) }}</td>
                <td class="border px-1 py-1">{{ number_format($rows->avg('rm_m&i'), 3) }}</td> {{-- Perhatikan nama kolom dengan simbol & --}}
                <td class="border px-1 py-1">{{ number_format($rows->avg('rm_dobi'), 2) }}</td>
                <td class="border px-1 py-1">{{ number_format($rows->avg('rm_iv'), 2) }}</td>
                <td class="border px-1 py-1">{{ number_format($rows->avg('rm_pv'), 2) }}</td>
                <td class="border px-1 py-1">{{ number_format($rows->avg('rm_av'), 2) }}</td>
                <td class="border px-1 py-1">{{ number_format($rows->avg('rm_totox'), 2) }}</td>
                <td class="border px-1 py-1">{{ number_format($rows->avg('rm_color_r'), 1) }}</td>
                <td class="border px-1 py-1">{{ number_format($rows->avg('rm_color_y'), 1) }}</td>
                <td class="border px-1 py-1">{{ number_format($rows->avg('rm_color_b'), 1) }}</td>

                {{-- BPO (4 Kolom) --}}
                <td class="border px-1 py-1">{{ number_format($rows->avg('bo_color_r'), 1) }}</td>
                <td class="border px-1 py-1">{{ number_format($rows->avg('bo_color_y'), 1) }}</td>
                <td class="border px-1 py-1">{{ number_format($rows->avg('bo_color_b'), 1) }}</td>
                <td class="border px-1 py-1">-</td> {{-- Break Test biasanya kualitatif/jarang dirata-rata, atau gunakan avg jika angka --}}

                {{-- RBDPO / Finish Good --}}
                <td class="border px-1 py-1">{{ number_format($rows->avg('fg_ffa'), 3) }}</td>

                {{-- Moisture --}}
                <td class="border px-1 py-1">{{ number_format($rows->avg('fg_moisture'), 3) }}</td>

                {{-- Logic Kondisional Impurities (Sesuai header Anda) --}}
                @if (!$isRef01)
                    <td class="border px-1 py-1">{{ number_format($rows->avg('fg_impurities'), 3) }}</td>
                @endif

                <td class="border px-1 py-1">{{ number_format($rows->avg('fg_iv'), 2) }}</td>
                <td class="border px-1 py-1">{{ number_format($rows->avg('fg_pv'), 2) }}</td>
                <td class="border px-1 py-1">{{ number_format($rows->avg('fg_color_r'), 1) }}</td>
                <td class="border px-1 py-1">{{ number_format($rows->avg('fg_color_y'), 1) }}</td>
                <td class="border px-1 py-1">{{ number_format($rows->avg('fg_color_b'), 1) }}</td>

                {{-- To Tank & Remarks (Tidak ada rata-rata) --}}
                <td class="border px-1 py-1 bg-gray-300"></td>
                <td class="border px-1 py-1 bg-gray-300"></td>

                {{-- PFAD --}}
                <td class="border px-1 py-1">{{ number_format($rows->avg('bp_ffa'), 3) }}</td>
                <td class="border px-1 py-1">{{ number_format($rows->avg('bp_m&i'), 3) }}</td>
                <td class="border px-1 py-1 bg-gray-300"></td> {{-- To Tank --}}

                {{-- SBE --}}
                <td class="border px-1 py-1">{{ number_format($rows->avg('w_sbe_m&i'), 2) }}</td>
                <td class="border px-1 py-1">{{ number_format($rows->avg('w_sbe_qc'), 2) }}</td>

                {{-- Remarks Akhir --}}
                <td class="border px-1 py-1 bg-gray-300"></td>
            </tr>
        </tfoot>

    </table>
</div>
