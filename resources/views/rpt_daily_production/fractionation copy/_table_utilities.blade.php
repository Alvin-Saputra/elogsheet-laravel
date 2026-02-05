{{-- File: rpt_daily_production/fractionation/_table_utilities.blade.php --}}
<h5 class="text-sm font-bold mt-4 mb-2">Utilities Usage</h5>
<div class="overflow-x-auto">
    <table class="min-w-full border border-gray-300 text-xs">
        <thead class="bg-gray-100">
            <tr>
                <th rowspan="2" class="border p-1">Shift</th>
                <th colspan="4" class="border p-1 bg-purple-100">Flowmeter</th>
                <th colspan="2" class="border p-1 bg-gray-200">Usage</th>
            </tr>
            <tr>
                <th class="border p-1 bg-purple-100">Awal</th>
                <th class="border p-1 bg-purple-100">Akhir</th>
                <th class="border p-1 bg-purple-100">Total</th>
                <th class="border p-1 bg-purple-100">Yield(%)</th>
                <th class="border p-1 bg-gray-200">Listrik</th>
                <th class="border p-1 bg-gray-200">Air</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr class="hover:bg-gray-50">
                    <td class="border p-1 text-center font-bold">{{ $row->shift }}</td>

                    {{-- Utilities Flowmeter --}}
                    <td class="border p-1 text-right">{{ number_format($row->uu_flowmeter_before) }}</td>
                    <td class="border p-1 text-right">{{ number_format($row->uu_flowmeter_after) }}</td>
                    <td class="border p-1 text-right font-medium">{{ number_format($row->uu_flowmeter_total) }}</td>
                    <td class="border p-1 text-right font-medium">{{ number_format($row->uu_yield_percent) }}</td>

                    {{-- Utilities Usage --}}
                    <td class="border p-1 text-right">{{ number_format($row->uu_listrik) }}</td>
                    <td class="border p-1 text-right">{{ number_format($row->uu_air) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="border p-4 text-center text-gray-500">
                        Tidak ada data Utilities Usage untuk tanggal ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
