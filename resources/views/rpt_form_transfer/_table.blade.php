@php
    if (!function_exists('formatDecimal')) {
        function formatDecimal($value) {
            if ($value === null || $value === '') {
                return '-';
            }
            if (!is_numeric($value)) {
                return $value;
            }
            $formatted = number_format((float) $value, 3, '.', '');
            $formatted = rtrim(rtrim($formatted, '0'), '.');
            return $formatted === '' ? '0' : $formatted;
        }
    }
@endphp

<table class="min-w-full bg-white border border-gray-400">
    <thead class="bg-gray-200 text-gray-800 text-sm">
        <tr>
            <th rowspan="3" class="px-3 py-2 border border-gray-400 text-center">No</th>
            <th rowspan="3" class="px-3 py-2 border border-gray-400 text-center">Oil Type</th>
            <th rowspan="3" class="px-3 py-2 border border-gray-400 text-center">Quantity</th>
            <th colspan="3" class="px-3 py-2 border border-gray-400 text-center">From</th>
            <th colspan="3" class="px-3 py-2 border border-gray-400 text-center">To</th>
            <th colspan="8" class="px-3 py-2 border border-gray-400 text-center">Quality Parameters</th>
            <th rowspan="3" class="px-3 py-2 border border-gray-400 text-center">Remark</th>
        </tr>
        <tr>
            <th rowspan="2" class="px-3 py-2 border border-gray-400 text-center">Storage Tank</th>
            <th rowspan="2" class="px-3 py-2 border border-gray-400 text-center">Refinery / Fracination</th>
            <th rowspan="2" class="px-3 py-2 border border-gray-400 text-center">Other</th>
            <th rowspan="2" class="px-3 py-2 border border-gray-400 text-center">Storage Tank</th>
            <th rowspan="2" class="px-3 py-2 border border-gray-400 text-center">Refinery / Fracination</th>
            <th rowspan="2" class="px-3 py-2 border border-gray-400 text-center">Auto Filling Tank</th>
            <th rowspan="2" class="px-3 py-2 border border-gray-400 text-center">M&amp;I (%)</th>
            <th rowspan="2" class="px-3 py-2 border border-gray-400 text-center">FFA (%)</th>
            <th colspan="2" class="px-3 py-2 border border-gray-400 text-center">Lov. Color</th>
            <th class="px-3 py-2 border border-gray-400 text-center">CP / TEMP</th>
            <th class="px-3 py-2 border border-gray-400 text-center">SMP</th>
            <th class="px-3 py-2 border border-gray-400 text-center">PV</th>
            <th class="px-3 py-2 border border-gray-400 text-center">IV</th>
        </tr>
        <tr>
            <th class="px-3 py-2 border border-gray-400 text-center">R</th>
            <th class="px-3 py-2 border border-gray-400 text-center">Y</th>
            <th class="px-3 py-2 border border-gray-400 text-center">oC</th>
            <th class="px-3 py-2 border border-gray-400 text-center">oC</th>
            <th class="px-3 py-2 border border-gray-400 text-center">Me/O2</th>
            <th class="px-3 py-2 border border-gray-400 text-center">gr12/100gr</th>
        </tr>
    </thead>
    <tbody class="text-gray-700 text-sm">
        @forelse ($details as $index => $detail)
            <tr>
                <td class="px-3 py-1 border border-gray-400 text-center">{{ $index + 1 }}</td>
                <td class="px-3 py-1 border border-gray-400 text-center">{{ $detail->oil_type ?? '-' }}</td>
                <td class="px-3 py-1 border border-gray-400 text-center">{{ formatDecimal($detail->quantity) }}</td>
                <td class="px-3 py-1 border border-gray-400 text-center">{{ $detail->from_storage_tank_no ?? '-' }}</td>
                <td class="px-3 py-1 border border-gray-400 text-center">{{ $detail->from_refinery_fractionation ?? '-' }}</td>
                <td class="px-3 py-1 border border-gray-400 text-center">{{ $detail->from_other ?? '-' }}</td>
                <td class="px-3 py-1 border border-gray-400 text-center">{{ $detail->to_storage_tank_no ?? '-' }}</td>
                <td class="px-3 py-1 border border-gray-400 text-center">{{ $detail->to_refinery_fractionation ?? '-' }}</td>
                <td class="px-3 py-1 border border-gray-400 text-center">{{ $detail->to_auto_filling_tank ?? '-' }}</td>
                <td class="px-3 py-1 border border-gray-400 text-center">{{ formatDecimal($detail->quality_m_and_i) }}</td>
                <td class="px-3 py-1 border border-gray-400 text-center">{{ formatDecimal($detail->quality_ffa) }}</td>
                <td class="px-3 py-1 border border-gray-400 text-center">{{ formatDecimal($detail->quality_lov_color_r) }}</td>
                <td class="px-3 py-1 border border-gray-400 text-center">{{ formatDecimal($detail->quality_lov_color_y) }}</td>
                <td class="px-3 py-1 border border-gray-400 text-center">{{ formatDecimal($detail->quality_cp_temp) }}</td>
                <td class="px-3 py-1 border border-gray-400 text-center">{{ formatDecimal($detail->quality_smp) }}</td>
                <td class="px-3 py-1 border border-gray-400 text-center">{{ formatDecimal($detail->quality_pv) }}</td>
                <td class="px-3 py-1 border border-gray-400 text-center">{{ formatDecimal($detail->quality_iv) }}</td>
                <td class="px-3 py-1 border border-gray-400 text-center">{{ $detail->remark ?? '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="19" class="px-3 py-2 text-center text-gray-500 italic">
                    No detail data available.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
