<div class="overflow-x-auto mt-4">
    <table class="min-w-full border border-gray-300 text-xs">
        <thead class="bg-gray-100">
            <tr>
                <th rowspan="3" class="border p-1">Shift</th>
                <th rowspan="3" class="border p-1 bg-yellow-100">Oil Type</th>
                <th colspan="6" class="border p-1 bg-yellow-100">Raw Material</th>
                <th rowspan="3" class="border p-1 bg-green-100">Oil Type</th>
                <th colspan="6" class="border p-1 bg-green-100">Finished Goods</th>
                <th colspan="6" class="border p-1 bg-red-100">By Product</th>
            </tr>
            <tr>
                <th rowspan="2" class="border p-1 bg-yellow-100">From Tank</th>
                <th colspan="2" class="border p-1 bg-yellow-100">Awal</th>
                <th colspan="2" class="border p-1 bg-yellow-100">Akhir</th>
                <th rowspan="2" class="border p-1 bg-yellow-100">Total</th>

                <th colspan="2" class="border p-1 bg-green-100">Awal</th>
                <th colspan="2" class="border p-1 bg-green-100">Akhir</th>
                <th rowspan="2" class="border p-1 bg-green-100">Total</th>
                <th rowspan="2" class="border p-1 bg-green-100">To Tank</th>

                <th colspan="2" class="border p-1 bg-red-100">Awal</th>
                <th colspan="2" class="border p-1 bg-red-100">Akhir</th>
                <th rowspan="2" class="border p-1 bg-red-100">Total</th>
                <th rowspan="2" class="border p-1 bg-red-100">To Tank</th>

            </tr>
            <tr>
                <th class="border p-1 bg-yellow-100">Jam</th>
                <th class="border p-1 bg-yellow-100">Flowmeter</th>
                <th class="border p-1 bg-yellow-100">Jam</th>
                <th class="border p-1 bg-yellow-100">Flowmeter</th>

                <th class="border p-1 bg-green-100">Jam</th>
                <th class="border p-1 bg-green-100">Flowmeter</th>
                <th class="border p-1 bg-green-100">Jam</th>
                <th class="border p-1 bg-green-100">Flowmeter</th>

                <th class="border p-1 bg-red-100">Jam</th>
                <th class="border p-1 bg-red-100">Flowmeter</th>
                <th class="border p-1 bg-red-100">Jam</th>
                <th class="border p-1 bg-red-100">Flowmeter</th>

            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr class="hover:bg-gray-50">
                    <td class="border p-1 text-center font-bold">{{ $row->shift }}</td>

                    {{-- Raw Material --}}
                    <td class="border p-1 text-center font-bold">{{ $row->oil_type_rm ?? '-' }}</td>
                    <td class="border p-1 text-center">{{ $row->cpo_tank ?? '-' }}</td>
                    <td class="border p-1 text-center">{{ $row->oil_type_rm_awal_jam ? \Carbon\Carbon::parse($row->oil_type_rm_awal_jam)->format('H:i') : '-' }}</td>
                    <td class="border p-1 text-right">{{ is_null($row->oil_type_rm_awal_flowmeter) ? '-' : number_format((float) $row->oil_type_rm_awal_flowmeter, 0) }}</td>
                    <td class="border p-1 text-center">{{ $row->oil_type_rm_akhir_jam ? \Carbon\Carbon::parse($row->oil_type_rm_akhir_jam)->format('H:i') : '-' }}</td>
                    <td class="border p-1 text-right">{{ is_null($row->oil_type_rm_akhir_flowmeter) ? '-' : number_format((float) $row->oil_type_rm_akhir_flowmeter, 0) }}</td>
                    <td class="border p-1 text-right font-semibold">{{ is_null($row->oil_type_rm_total) ? '-' : number_format((float) $row->oil_type_rm_total, 0) }}</td>
                    {{-- Finished Goods --}}
                    <td class="border p-1 text-center font-bold">{{ $row->oil_type_fg ?? '-' }}</td>
                    <td class="border p-1 text-center">{{ $row->oil_type_fg_awal_jam ? \Carbon\Carbon::parse($row->oil_type_fg_awal_jam)->format('H:i') : '-' }}</td>
                    <td class="border p-1 text-right">{{ is_null($row->oil_type_fg_awal_flowmeter) ? '-' : number_format((float) $row->oil_type_fg_awal_flowmeter, 0) }}</td>
                    <td class="border p-1 text-center">{{ $row->oil_type_fg_akhir_jam ? \Carbon\Carbon::parse($row->oil_type_fg_akhir_jam)->format('H:i') : '-' }}</td>
                    <td class="border p-1 text-right">{{ is_null($row->oil_type_fg_akhir_flowmeter) ? '-' : number_format((float) $row->oil_type_fg_akhir_flowmeter, 0) }}</td>
                    <td class="border p-1 text-right font-semibold">{{ is_null($row->oil_type_fg_total) ? '-' : number_format((float) $row->oil_type_fg_total, 0) }}</td>
                    <td class="border p-1 text-right font-semibold">{{ $row->oil_type_fg_to_tank ?? '-' }}</td>

                    {{-- By Product --}}
                    <td class="border p-1 text-center">{{ $row->bp_awal_jam ? \Carbon\Carbon::parse($row->bp_awal_jam)->format('H:i') : '-' }}</td>
                    <td class="border p-1 text-right">{{ is_null($row->bp_awal_flowmeter) ? '-' : number_format((float) $row->bp_awal_flowmeter, 0) }}</td>
                    <td class="border p-1 text-center">{{ $row->bp_akhir_jam ? \Carbon\Carbon::parse($row->bp_akhir_jam)->format('H:i') : '-' }}</td>
                    <td class="border p-1 text-right">{{ is_null($row->bp_akhir_flowmeter) ? '-' : number_format((float) $row->bp_akhir_flowmeter, 0) }}</td>
                    <td class="border p-1 text-right font-semibold">{{ is_null($row->bp_total) ? '-' : number_format((float) $row->bp_total, 0) }}</td>
                    <td class="border p-1 text-right font-semibold">{{ $row->bp_to_tank ?? '-' }}</td>

                </tr>
            @empty
                <tr>
                    <td colspan="21" class="border p-4 text-center text-gray-500">
                        Tidak ada data Raw Material, Finished Goods, atau By Product untuk shift ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
