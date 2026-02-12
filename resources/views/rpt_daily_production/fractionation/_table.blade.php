<div class="overflow-x-auto mt-4">
    <table class="min-w-full border border-gray-300 text-xs">
        <thead class="bg-gray-100">
            <tr>
                <th rowspan="2" class="border p-1">Shift</th>
                <th colspan="5" class="border p-1 bg-yellow-100">Raw Material Flow</th>
                <th colspan="4" class="border p-1 bg-green-100">Finished Goods (Stearin)</th>
                <th colspan="4" class="border p-1 bg-blue-100">Finished Goods (Olein)</th>
                <th colspan="3" class="border p-1 bg-purple-100">Utilities (Flowmeter)</th>
                <th colspan="2" class="border p-1 bg-gray-200">Utilities (Usage)</th>
                <th rowspan="2" class="border p-1">Remarks</th>
            </tr>
            <tr>
                <th class="border p-1 bg-yellow-100">Oil Type</th>
                <th class="border p-1 bg-yellow-100">From Tank</th>
                <th class="border p-1 bg-yellow-100">Start</th>
                <th class="border p-1 bg-yellow-100">End</th>
                <th class="border p-1 bg-yellow-100">Total (KG)</th>

                <th class="border p-1 bg-green-100">Oil Type</th>
                <th class="border p-1 bg-green-100">To Tank</th>
                <th class="border p-1 bg-green-100">Start</th>
                <th class="border p-1 bg-green-100">Total (KG)</th>

                <th class="border p-1 bg-blue-100">Oil Type</th>
                <th class="border p-1 bg-blue-100">To Tank</th>
                <th class="border p-1 bg-blue-100">Start</th>
                <th class="border p-1 bg-blue-100">Total (KG)</th>

                <th class="border p-1 bg-purple-100">Before</th>
                <th class="border p-1 bg-purple-100">After</th>
                <th class="border p-1 bg-purple-100">Total</th>

                <th class="border p-1 bg-gray-200">Listrik</th>
                <th class="border p-1 bg-gray-200">Air</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr class="hover:bg-gray-50">
                    <td class="border p-1 text-center font-bold">{{ $row->shift ?? '-' }}</td>

                    <td class="border p-1">{{ $row->oil_type_rm ?? '-' }}</td>
                    <td class="border p-1 text-center">{{ $row->oil_type_rm_from_tank ?? '-' }}</td>
                    <td class="border p-1 text-right">
                        {{ is_null($row->oil_type_rm_awal_flowmeter) ? '-' : number_format((float) $row->oil_type_rm_awal_flowmeter, 0) }}
                    </td>
                    <td class="border p-1 text-right">
                        {{ is_null($row->oil_type_rm_akhir_flowmeter) ? '-' : number_format((float) $row->oil_type_rm_akhir_flowmeter, 0) }}
                    </td>
                    <td class="border p-1 text-right font-semibold">
                        {{ is_null($row->oil_type_rm_total) ? '-' : number_format((float) $row->oil_type_rm_total, 0) }}
                    </td>

                    <td class="border p-1">{{ $row->oil_type_fgs ?? '-' }}</td>
                    <td class="border p-1 text-center">{{ $row->oil_type_fgs_to_tank ?? '-' }}</td>
                    <td class="border p-1 text-right">
                        {{ is_null($row->oil_type_fgs_awal_flowmeter) ? '-' : number_format((float) $row->oil_type_fgs_awal_flowmeter, 0) }}
                    </td>
                    <td class="border p-1 text-right font-semibold">
                        {{ is_null($row->oil_type_fgs_total) ? '-' : number_format((float) $row->oil_type_fgs_total, 0) }}
                    </td>

                    <td class="border p-1">{{ $row->oil_type_fgh ?? '-' }}</td>
                    <td class="border p-1 text-center">{{ $row->oil_type_fgh_to_tank ?? '-' }}</td>
                    <td class="border p-1 text-right">
                        {{ is_null($row->oil_type_fgh_awal_flowmeter) ? '-' : number_format((float) $row->oil_type_fgh_awal_flowmeter, 0) }}
                    </td>
                    <td class="border p-1 text-right font-semibold">
                        {{ is_null($row->oil_type_fgh_total) ? '-' : number_format((float) $row->oil_type_fgh_total, 0) }}
                    </td>

                    <td class="border p-1 text-right">
                        {{ is_null($row->uu_flowmeter_before) ? '-' : number_format((float) $row->uu_flowmeter_before, 0) }}
                    </td>
                    <td class="border p-1 text-right">
                        {{ is_null($row->uu_flowmeter_after) ? '-' : number_format((float) $row->uu_flowmeter_after, 0) }}
                    </td>
                    <td class="border p-1 text-right font-medium">
                        {{ is_null($row->uu_flowmeter_total) ? '-' : number_format((float) $row->uu_flowmeter_total, 0) }}
                    </td>

                    <td class="border p-1 text-right">
                        {{ is_null($row->uu_listrik) ? '-' : number_format((float) $row->uu_listrik, 0) }}
                    </td>
                    <td class="border p-1 text-right">
                        {{ is_null($row->uu_air) ? '-' : number_format((float) $row->uu_air, 0) }}
                    </td>

                    <td class="border p-1">{{ $row->remarks ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="20" class="border p-4 text-center text-gray-500">
                        Tidak ada data Daily Production Fractionation untuk tanggal ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
