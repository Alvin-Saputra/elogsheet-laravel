{{-- This file is included by rpt_daily_production.fractionation.preview.blade.php --}}
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
                <th class="border p-1 bg-yellow-100">Tank Asal</th>
                <th class="border p-1 bg-yellow-100">Awal (Flowmeter)</th>
                <th class="border p-1 bg-yellow-100">Akhir (Flowmeter)</th>
                <th class="border p-1 bg-yellow-100">Total (KG)</th>

                <th class="border p-1 bg-green-100">Oil Type</th>
                <th class="border p-1 bg-green-100">Tank Tujuan</th>
                <th class="border p-1 bg-green-100">Awal (Flowmeter)</th>
                <th class="border p-1 bg-green-100">Total (KG)</th>

                <th class="border p-1 bg-blue-100">Oil Type</th>
                <th class="border p-1 bg-blue-100">Tank Tujuan</th>
                <th class="border p-1 bg-blue-100">Awal (Flowmeter)</th>
                <th class="border p-1 bg-blue-100">Total (KG)</th>

                <th class="border p-1 bg-purple-100">Awal</th>
                <th class="border p-1 bg-purple-100">Akhir</th>
                <th class="border p-1 bg-purple-100">Total</th>

                <th class="border p-1 bg-gray-200">Listrik</th>
                <th class="border p-1 bg-gray-200">Air</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr class="hover:bg-gray-50">
                    <td class="border p-1 text-center font-bold">{{ $row->shift }}</td>

                    {{-- Raw Material --}}
                    <td class="border p-1">{{ $row->oil_type_rm }}</td>
                    <td class="border p-1 text-center">{{ $row->oil_type_rm_from_tank }}</td>
                    <td class="border p-1 text-right">{{ number_format($row->oil_type_rm_awal_flowmeter) }}</td>
                    <td class="border p-1 text-right">{{ number_format($row->oil_type_rm_akhir_flowmeter) }}</td>
                    <td class="border p-1 text-right font-semibold">{{ number_format($row->oil_type_rm_total) }}</td>

                    {{-- Finished Goods Stearin --}}
                    <td class="border p-1">{{ $row->oil_type_fgs }}</td>
                    <td class="border p-1 text-center">{{ $row->oil_type_fgs_to_tank }}</td>
                    <td class="border p-1 text-right">{{ number_format($row->oil_type_fgs_awal_flowmeter) }}</td>
                    <td class="border p-1 text-right font-semibold">{{ number_format($row->oil_type_fgs_total) }}</td>

                    {{-- Finished Goods Olein --}}
                    <td class="border p-1">{{ $row->oil_type_fgh }}</td>
                    <td class="border p-1 text-center">{{ $row->oil_type_fgh_to_tank }}</td>
                    <td class="border p-1 text-right">{{ number_format($row->oil_type_fgh_awal_flowmeter, 0) }}</td>
                    <td class="border p-1 text-right font-semibold">{{ number_format($row->oil_type_fgh_total, 0) }}
                    </td>

                    {{-- Utilities Flowmeter --}}
                    <td class="border p-1 text-right">{{ number_format($row->uu_flowmeter_before) }}</td>
                    <td class="border p-1 text-right">{{ number_format($row->uu_flowmeter_after) }}</td>
                    <td class="border p-1 text-right font-medium">{{ number_format($row->uu_flowmeter_total) }}</td>

                    {{-- Utilities Usage --}}
                    <td class="border p-1 text-right">{{ number_format($row->uu_listrik) }}</td>
                    <td class="border p-1 text-right">{{ number_format($row->uu_air) }}</td>

                    {{-- Remarks --}}
                    <td class="border p-1">{{ $row->remarks }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="17" class="border p-4 text-center text-gray-500">
                        Tidak ada data Daily Production Fractionation untuk tanggal ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
