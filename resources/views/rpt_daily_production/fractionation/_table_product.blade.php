<h5 class="text-sm font-bold mt-4 mb-2">Detail Produksi Per Ticket</h5>
<div class="overflow-x-auto">
    <table class="min-w-full border border-gray-300 text-xs">
        <thead class="bg-gray-100">
            <tr>
                <th rowspan="3" class="border p-1">Shift</th>
                <th colspan="9" class="border p-1 bg-yellow-100">Raw Material</th>
                <th colspan="9" class="border p-1 bg-green-100">Finished Goods (Stearin)</th>
                <th colspan="8" class="border p-1 bg-blue-100">By Product (FGH/Olein)</th>
            </tr>
            <tr>
                <th rowspan="2" class="border p-1 bg-yellow-100">NO</th>
                <th rowspan="2" class="border p-1 bg-yellow-100">CR</th>
                <th rowspan="2" class="border p-1 bg-yellow-100">Oil Type</th>
                <th rowspan="2" class="border p-1 bg-yellow-100">From Tank</th>
                <th colspan="2" class="border p-1 bg-yellow-100">Awal</th>
                <th colspan="2" class="border p-1 bg-yellow-100">Akhir</th>
                <th rowspan="2" class="border p-1 bg-yellow-100">Total (KG)</th>

                <th rowspan="2" class="border p-1 bg-green-100">NO</th>
                <th rowspan="2" class="border p-1 bg-green-100">CR</th>
                <th rowspan="2" class="border p-1 bg-green-100">Oil Type</th>
                <th colspan="2" class="border p-1 bg-green-100">Awal</th>
                <th colspan="2" class="border p-1 bg-green-100">Akhir</th>
                <th rowspan="2" class="border p-1 bg-green-100">Total (KG)</th>
                <th rowspan="2" class="border p-1 bg-green-100">To Tank</th>

                <th rowspan="2" class="border p-1 bg-blue-100">NO</th>
                <th rowspan="2" class="border p-1 bg-blue-100">Oil Type</th>
                <th colspan="2" class="border p-1 bg-blue-100">Awal</th>
                <th colspan="2" class="border p-1 bg-blue-100">Akhir</th>
                <th rowspan="2" class="border p-1 bg-blue-100">Total (KG)</th>
                <th rowspan="2" class="border p-1 bg-blue-100">To Tank</th>
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

                <th class="border p-1 bg-blue-100">Jam</th>
                <th class="border p-1 bg-blue-100">Flowmeter</th>
                <th class="border p-1 bg-blue-100">Jam</th>
                <th class="border p-1 bg-blue-100">Flowmeter</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr class="hover:bg-gray-50">
                    <td class="border p-1 text-center font-bold">{{ $row->shift ?? '-' }}</td>

                    <td class="border p-1 text-center">{{ $row->oil_type_rm_no ?? '-' }}</td>
                    <td class="border p-1 text-center">{{ $row->oil_type_rm_cr ?? '-' }}</td>
                    <td class="border p-1">{{ $row->oil_type_rm ?? '-' }}</td>
                    <td class="border p-1 text-center">{{ $row->oil_type_rm_from_tank ?? '-' }}</td>
                    <td class="border p-1 text-center">
                        {{ $row->oil_type_rm_awal_jam ? \Carbon\Carbon::parse($row->oil_type_rm_awal_jam)->format('H:i') : '-' }}
                    </td>
                    <td class="border p-1 text-right">
                        {{ is_null($row->oil_type_rm_awal_flowmeter) ? '-' : number_format((float) $row->oil_type_rm_awal_flowmeter, 0) }}
                    </td>
                    <td class="border p-1 text-center">
                        {{ $row->oil_type_rm_akhir_jam ? \Carbon\Carbon::parse($row->oil_type_rm_akhir_jam)->format('H:i') : '-' }}
                    </td>
                    <td class="border p-1 text-right">
                        {{ is_null($row->oil_type_rm_akhir_flowmeter) ? '-' : number_format((float) $row->oil_type_rm_akhir_flowmeter, 0) }}
                    </td>
                    <td class="border p-1 text-right font-semibold">
                        {{ is_null($row->oil_type_rm_total) ? '-' : number_format((float) $row->oil_type_rm_total, 0) }}
                    </td>

                    <td class="border p-1 text-center">{{ $row->oil_type_fgs_no ?? '-' }}</td>
                    <td class="border p-1 text-center">{{ $row->oil_type_fgs_cr ?? '-' }}</td>
                    <td class="border p-1">{{ $row->oil_type_fgs ?? '-' }}</td>
                    <td class="border p-1 text-center">
                        {{ $row->oil_type_fgs_awal_jam ? \Carbon\Carbon::parse($row->oil_type_fgs_awal_jam)->format('H:i') : '-' }}
                    </td>
                    <td class="border p-1 text-right">
                        {{ is_null($row->oil_type_fgs_awal_flowmeter) ? '-' : number_format((float) $row->oil_type_fgs_awal_flowmeter, 0) }}
                    </td>
                    <td class="border p-1 text-center">
                        {{ $row->oil_type_fgs_akhir_jam ? \Carbon\Carbon::parse($row->oil_type_fgs_akhir_jam)->format('H:i') : '-' }}
                    </td>
                    <td class="border p-1 text-right">
                        {{ is_null($row->oil_type_fgs_akhir_flowmeter) ? '-' : number_format((float) $row->oil_type_fgs_akhir_flowmeter, 0) }}
                    </td>
                    <td class="border p-1 text-right font-semibold">
                        {{ is_null($row->oil_type_fgs_total) ? '-' : number_format((float) $row->oil_type_fgs_total, 0) }}
                    </td>
                    <td class="border p-1 text-center">{{ $row->oil_type_fgs_to_tank ?? '-' }}</td>

                    <td class="border p-1 text-center">{{ $row->oil_type_fgh_no ?? '-' }}</td>
                    <td class="border p-1">{{ $row->oil_type_fgh ?? '-' }}</td>
                    <td class="border p-1 text-center">
                        {{ $row->oil_type_fgh_awal_jam ? \Carbon\Carbon::parse($row->oil_type_fgh_awal_jam)->format('H:i') : '-' }}
                    </td>
                    <td class="border p-1 text-right">
                        {{ is_null($row->oil_type_fgh_awal_flowmeter) ? '-' : number_format((float) $row->oil_type_fgh_awal_flowmeter, 0) }}
                    </td>
                    <td class="border p-1 text-center">
                        {{ $row->oil_type_fgh_akhir_jam ? \Carbon\Carbon::parse($row->oil_type_fgh_akhir_jam)->format('H:i') : '-' }}
                    </td>
                    <td class="border p-1 text-right">
                        {{ is_null($row->oil_type_fgh_akhir_flowmeter) ? '-' : number_format((float) $row->oil_type_fgh_akhir_flowmeter, 0) }}
                    </td>
                    <td class="border p-1 text-right font-semibold">
                        {{ is_null($row->oil_type_fgh_total) ? '-' : number_format((float) $row->oil_type_fgh_total, 0) }}
                    </td>
                    <td class="border p-1 text-center">{{ $row->oil_type_fgh_to_tank ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="27" class="border p-4 text-center text-gray-500">
                        Tidak ada data Raw Material, Finished Goods, atau By Product untuk tanggal ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
