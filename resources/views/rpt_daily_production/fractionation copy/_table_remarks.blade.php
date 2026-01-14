{{-- File: rpt_daily_production/fractionation/_table_remarks.blade.php --}}
<h5 class="text-sm font-bold mt-4 mb-2">Remarks</h5>
<div class="overflow-x-auto">
    <table class="min-w-full border border-gray-300 text-xs">
        <thead class="bg-gray-100">
            <tr>
                <th class="border p-1 w-1/12">Shift</th>
                <th class="border p-1 w-11/12">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr class="hover:bg-gray-50">
                    <td class="border p-1 text-center font-bold">{{ $row->shift }}</td>
                    <td class="border p-1">{{ $row->remarks }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="border p-4 text-center text-gray-500">
                        Tidak ada catatan/remarks untuk shift ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
