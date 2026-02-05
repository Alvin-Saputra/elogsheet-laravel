{{-- File: rpt_daily_production/fractionation/_table_product.blade.php --}}
<div class="overflow-x-auto">
    <table class="min-w-full border border-gray-300 text-xs">
        <thead class="bg-gray-100">
            <tr>
                <th rowspan="3" class="border p-1">Shift</th>
                <th colspan="9" class="border p-1 bg-yellow-100">Raw Material</th>
                <th colspan="9" class="border p-1 bg-green-100">Finished Goods</th>
                <th colspan="8" class="border p-1 bg-blue-100">By Product</th>
            </tr>
            <tr>
                <th rowspan="2" class="border p-1 bg-yellow-100">NO</th>
                <th rowspan="2" class="border p-1 bg-yellow-100">CR</th>
                <th rowspan="2" class="border p-1 bg-yellow-100">Oil Type</th>
                <th rowspan="2" class="border p-1 bg-yellow-100">Tank Awal</th>
                <th colspan="2" class="border p-1 bg-yellow-100">Awal</th>
                <th colspan="2" class="border p-1 bg-yellow-100">Akhir</th>
                <th rowspan="2" class="border p-1 bg-yellow-100">Total (KG)</th>

                <th rowspan="2" class="border p-1 bg-green-100">NO</th>
                <th rowspan="2" class="border p-1 bg-green-100">CR</th>
                <th rowspan="2" class="border p-1 bg-green-100">Oil Type</th>
                <th colspan="2" class="border p-1 bg-green-100">Awal</th>
                <th colspan="2" class="border p-1 bg-green-100">Akhir</th>
                <th rowspan="2" class="border p-1 bg-green-100">Total (KG)</th>
                <th rowspan="2" class="border p-1 bg-green-100">Tank Tujuan</th>

                <th rowspan="2" class="border p-1 bg-blue-100">NO</th>
                <th rowspan="2" class="border p-1 bg-blue-100">Oil Type</th>
                <th colspan="2" class="border p-1 bg-blue-100">Awal</th>
                <th colspan="2" class="border p-1 bg-blue-100">Akhir</th>
                <th rowspan="2" class="border p-1 bg-blue-100">Total</th>
                <th rowspan="2" class="border p-1 bg-blue-100">Tank Tujuan</th>
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
                    <td class="border p-1 text-center font-bold">{{ $row->shift }}</td>

                    {{-- Raw Material --}}
                    <td class="border p-1">{{ $row->oil_type_rm_no }}</td>
                    <td class="border p-1">{{ $row->oil_type_rm_cr }}</td>
                    <td class="border p-1">{{ $row->oil_type_rm }}</td>
                    <td class="border p-1 text-center">{{ $row->oil_type_rm_from_tank }}</td>
                    <td class="border p-1 text-right">{{ $row->oil_type_rm_awal_jam }}</td>
                    <td class="border p-1 text-right">{{ number_format($row->oil_type_rm_awal_flowmeter) }}</td>
                    <td class="border p-1 text-right">{{ $row->oil_type_rm_akhir_jam }}</td>
                    <td class="border p-1 text-right">{{ number_format($row->oil_type_rm_akhir_flowmeter) }}</td>
                    <td class="border p-1 text-right font-semibold">{{ number_format($row->oil_type_rm_total) }}</td>

                    {{-- Finished Goods Stearin --}}
                    <td class="border p-1">{{ $row->oil_type_fgs_no }}</td>
                    <td class="border p-1">{{ $row->oil_type_fgs_cr }}</td>
                    <td class="border p-1">{{ $row->oil_type_fgs }}</td>
                    <td class="border p-1 text-right">{{ $row->oil_type_fgs_awal_jam }}</td>
                    <td class="border p-1 text-right">{{ $row->oil_type_fgs_awal_flowmeter }}</td>
                    <td class="border p-1 text-right">{{ $row->oil_type_fgs_akhir_jam }}</td>
                    <td class="border p-1 text-right">{{ $row->oil_type_fgs_akhir_flowmeter }}</td>
                    <td class="border p-1 text-right">{{ $row->oil_type_fgs_total }}</td>
                    <td class="border p-1 text-center">{{ $row->oil_type_fgs_to_tank }}</td>

                    <td class="border p-1 text-right">{{ number_format($row->oil_type_fgh_no) }}</td>
                    <td class="border p-1 text-right">{{ $row->oil_type_fgh }}</td>
                    <td class="border p-1 text-right">{{ $row->oil_type_fgh_awal_jam }}</td>
                    <td class="border p-1 text-right">{{ $row->oil_type_fgh_awal_flowmeter }}</td>
                    <td class="border p-1 text-right">{{ $row->oil_type_fgh_akhir_jam }}</td>
                    <td class="border p-1 text-right">{{ $row->oil_type_fgh_akhir_flowmeter }}</td>
                    <td class="border p-1 text-right font-semibold">{{ number_format($row->oil_type_fgs_total) }}</td>
                    <td class="border p-1 text-center">{{ $row->oil_type_fgh_to_tank }}</td>

                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="14" class="border p-4 text-center text-gray-500">
                        Tidak ada data Raw Material atau Finished Goods untuk tanggal ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
