<h5 class="text-sm font-bold mt-4 mb-2">Utility Usage</h5>
<div class="overflow-x-auto">
    <table class="min-w-full border border-gray-300 text-xs">
        <thead class="bg-gray-100">
            <tr>
                <th rowspan="2" class="border p-1">Shift</th>
                <th colspan="4" class="border p-1 bg-purple-100">Flowmeter</th>
                <th colspan="2" class="border p-1 bg-gray-200">Usage</th>
            </tr>
            <tr>
                <th class="border p-1 bg-purple-100">Before</th>
                <th class="border p-1 bg-purple-100">After</th>
                <th class="border p-1 bg-purple-100">Total</th>
                <th class="border p-1 bg-purple-100">Yield (%)</th>
                <th class="border p-1 bg-gray-200">Listrik</th>
                <th class="border p-1 bg-gray-200">Air</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr class="hover:bg-gray-50">
                    <td class="border p-1 text-center font-bold">{{ $row->shift ?? '-' }}</td>
                    <td class="border p-1 text-right">
                        {{ is_null($row->uu_flowmeter_before) ? '-' : number_format((float) $row->uu_flowmeter_before, 0) }}
                    </td>
                    <td class="border p-1 text-right">
                        {{ is_null($row->uu_flowmeter_after) ? '-' : number_format((float) $row->uu_flowmeter_after, 0) }}
                    </td>
                    <td class="border p-1 text-right font-medium">
                        {{ is_null($row->uu_flowmeter_total) ? '-' : number_format((float) $row->uu_flowmeter_total, 0) }}
                    </td>
                    <td class="border p-1 text-right font-medium">
                        {{ is_null($row->uu_yield_percent) ? '-' : number_format((float) $row->uu_yield_percent, 2) }}
                    </td>
                    <td class="border p-1 text-right">
                        {{ is_null($row->uu_listrik) ? '-' : number_format((float) $row->uu_listrik, 0) }}
                    </td>
                    <td class="border p-1 text-right">
                        {{ is_null($row->uu_air) ? '-' : number_format((float) $row->uu_air, 0) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="border p-4 text-center text-gray-500">
                        Tidak ada data Utility Usage untuk tanggal ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
