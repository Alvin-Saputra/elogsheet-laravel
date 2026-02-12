<h5 class="text-sm font-bold mt-4 mb-2">Utility Usage</h5>
<div class="overflow-x-auto">
    <table class="min-w-full border border-gray-300 text-xs">
        <thead class="bg-gray-100">
            <tr>
                <th class="border p-1">Shift</th>
                <th class="border p-1 bg-gray-200">Item Utility</th>
                <th class="border p-1 bg-gray-200">Total</th>
                <th class="border p-1 bg-gray-200">Total Steam</th>
                <th class="border p-1 bg-gray-200">Steam</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr class="hover:bg-gray-50">
                    <td class="border p-1 text-center font-bold">{{ $row->shift }}</td>
                    <td class="border p-1">{{ $row->uu_item ?? '-' }}</td>
                    <td class="border p-1 text-right">{{ number_format((float) ($row->uu_total_cpo ?? 0), 0) }}</td>
                    <td class="border p-1 text-right">{{ number_format((float) ($row->uu_total_steam ?? 0), 0) }}</td>
                    <td class="border p-1 text-right">{{ number_format((float) ($row->uu_steam_cpo ?? 0), 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="border p-4 text-center text-gray-500">
                        Tidak ada data Utilities Usage untuk shift ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
