{{-- File: _table_chemicals.blade.php (Corrected Complex Header) --}}
<h5 class="text-sm font-bold mt-4 mb-2">Pemakaian Bahan Penolong</h5>
<div class="overflow-x-auto">
    <table class="min-w-full border border-gray-300 text-xs">
        <thead class="bg-gray-100">
            <tr>
                {{-- Shift is the primary column, spanning three header rows --}}
                <th rowspan="3" class="border p-1">Shift</th>

                {{-- Group 1: Bleaching Earth (BE) --}}
                <th colspan="4" class="border p-1 bg-blue-100">BLEACHING EARTH (BE)</th>

                {{-- Group 2: Phosphoric Acid (PA) --}}
                <th colspan="3" class="border p-1 bg-purple-100">PHOSPHORIC ACID (PA)</th>
            </tr>
            <tr>
                {{-- Sub-Headers for BE --}}
                <th colspan="2" class="border p-1 bg-blue-100">TOTAL</th>
                <th rowspan="2" class="border p-1 bg-blue-100">LOT BATCH NUMBER</th>
                <th rowspan="2" class="border p-1 bg-blue-100">Yield (%)</th>

                {{-- Sub-Headers for PA --}}
                <th rowspan="2" class="border p-1 bg-purple-100">TOTAL</th>
                <th rowspan="2" class="border p-1 bg-purple-100">LOT BATCH NUMBER</th>
                <th rowspan="2" class="border p-1 bg-purple-100">Yield (%)</th>
            </tr>
            <tr>
                {{-- Unit Headers --}}
                <th class="border p-1 bg-blue-100">BAG</th>
                <th class="border p-1 bg-blue-100">Jenis</th>

            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr class="hover:bg-gray-50">
                    <td class="border p-1 text-center font-bold">{{ $row->shift }}</td>

                    {{-- Bleaching Earth Data (Total Bag) --}}
                    <td class="border p-1 text-right">{{ number_format((float) ($row->be_total_bag ?? 0), 0) }}</td>
                    <td class="border p-1 text-right">{{ $row->be_total_jenis ?? '-' }}</td>
                    <td class="border p-1 text-center">{{ $row->be_lot_batch_number ?? 'N/A' }}</td>
                    <td class="border p-1 text-center">{{ number_format((float) ($row->be_yield_percent ?? 0), 2) }}</td>

                    {{-- Phosphoric Acid Data (Total KG) --}}
                    <td class="border p-1 text-right">{{ number_format((float) ($row->pa_total ?? 0), 0) }}</td>
                    <td class="border p-1 text-center">{{ $row->pa_lot_batch_number ?? 'N/A' }}</td>
                    <td class="border p-1 text-right">{{ number_format((float) ($row->pa_yield_percent ?? 0), 2) }}</td>
                </tr>
            @empty
                <tr>
                    {{-- 8 columns (1 Shift + 4 BE + 3 PA) --}}
                    <td colspan="8" class="border p-4 text-center text-gray-500">
                        Tidak ada data Pemakaian Bahan Penolong untuk hari ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
