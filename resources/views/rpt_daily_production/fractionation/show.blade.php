@extends('layouts.app')

@section('page_title', 'Detail Daily Production Fractionation Report')

@section('content')
    @php
        $formInfoSource =
            $rows->sortByDesc(function ($row) {
                return $row->revision_date ?? $row->entry_date;
            })->first() ?? $firstReport;

        $shiftSummaryRows = collect($rowsByShift)
            ->map(function ($shiftRows, $shift) {
                $summary = $shiftRows->sortBy('no')->first();

                return (object) [
                    'shift' => $shift,
                    'uu_budget_ref_qty' => $summary->uu_budget_ref_qty ?? null,
                    'uu_flowmeter_before' => $summary->uu_flowmeter_before ?? null,
                    'uu_flowmeter_after' => $summary->uu_flowmeter_after ?? null,
                    'uu_flowmeter_total' => $summary->uu_flowmeter_total ?? null,
                    'uu_listrik' => $summary->uu_listrik ?? null,
                    'uu_air' => $summary->uu_air ?? null,
                    'uu_yield_percent' => $summary->uu_yield_percent ?? null,
                    'remarks' => $shiftRows->pluck('remarks')->filter()->unique()->implode(PHP_EOL),
                ];
            })
            ->sortBy(function ($item) {
                return (int) $item->shift;
            })
            ->values();
    @endphp

    <div class="bg-white p-6 rounded-2xl shadow-md max-w-7xl mx-auto text-sm text-gray-700">
        <div class="flex items-center justify-between gap-4 mb-6">
            <div>
                <h3 class="text-2xl font-bold text-gray-700">Detail Daily Production Fractionation</h3>
                @if (!empty($selectedShift))
                    <p class="text-sm text-gray-500 mt-1">Filtered Shift: {{ $selectedShift }}</p>
                @endif
                @if (!empty($ticketId))
                    <p class="text-sm text-gray-500 mt-1">Ticket: {{ $ticketId }}</p>
                @endif
            </div>
            <a href="{{ url()->previous() }}"
                class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg">
                Kembali
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">
            <div class="rounded-xl bg-gray-50 border border-gray-200 p-3">
                <div class="text-xs text-gray-500">Transaction Date</div>
                <div class="text-lg font-semibold">
                    {{ $firstReport->transaction_date ? \Carbon\Carbon::parse($firstReport->transaction_date)->format('d M Y H:i') : '-' }}
                </div>
            </div>
            <div class="rounded-xl bg-gray-50 border border-gray-200 p-3">
                <div class="text-xs text-gray-500">Posting Date</div>
                <div class="text-lg font-semibold">
                    {{ $firstReport->posting_date ? \Carbon\Carbon::parse($firstReport->posting_date)->format('d M Y H:i') : '-' }}
                </div>
            </div>
            <div class="rounded-xl bg-gray-50 border border-gray-200 p-3">
                <div class="text-xs text-gray-500">Work Center</div>
                <div class="text-lg font-semibold">{{ $firstReport->work_center ?? '-' }}</div>
            </div>
        </div>

        <div class="mb-8">
            <h4 class="font-bold text-gray-800 mb-3">Form Info</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="rounded-xl bg-gray-50 border border-gray-200 p-3">
                    <div class="text-xs text-gray-500">Form No</div>
                    <div class="text-lg font-semibold">{{ $formInfoSource->form_no ?? '-' }}</div>
                </div>
                <div class="rounded-xl bg-gray-50 border border-gray-200 p-3">
                    <div class="text-xs text-gray-500">Date Issued</div>
                    <div class="text-lg font-semibold">
                        {{ $formInfoSource->date_issued ? \Carbon\Carbon::parse($formInfoSource->date_issued)->format('d M Y') : '-' }}
                    </div>
                </div>
                <div class="rounded-xl bg-gray-50 border border-gray-200 p-3">
                    <div class="text-xs text-gray-500">Revision No</div>
                    <div class="text-lg font-semibold">{{ $formInfoSource->revision_no ?? '-' }}</div>
                </div>
                <div class="rounded-xl bg-gray-50 border border-gray-200 p-3">
                    <div class="text-xs text-gray-500">Revision Date</div>
                    <div class="text-lg font-semibold">
                        {{ $formInfoSource->revision_date ? \Carbon\Carbon::parse($formInfoSource->revision_date)->format('d M Y H:i') : '-' }}
                    </div>
                </div>
                <div class="rounded-xl bg-gray-50 border border-gray-200 p-3">
                    <div class="text-xs text-gray-500">Entry By</div>
                    <div class="text-lg font-semibold">{{ $formInfoSource->entry_by ?? '-' }}</div>
                </div>
                <div class="rounded-xl bg-gray-50 border border-gray-200 p-3">
                    <div class="text-xs text-gray-500">Entry Date</div>
                    <div class="text-lg font-semibold">
                        {{ $formInfoSource->entry_date ? \Carbon\Carbon::parse($formInfoSource->entry_date)->format('d M Y H:i') : '-' }}
                    </div>
                </div>
            </div>
        </div>

        @foreach ($rowsByShift as $shift => $shiftRows)
            @php
                $summary = $shiftRows->sortBy('no')->first();
            @endphp

            <div class="mb-8 border border-gray-200 rounded-xl overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b flex items-center justify-between">
                    <h4 class="font-black text-red-700 tracking-wide uppercase text-base">
                        Shift <span class="inline-block px-2 py-1 rounded bg-red-700 text-white">{{ $shift }}</span>
                    </h4>
                    <span class="text-xs text-gray-500">{{ $shiftRows->count() }} row(s)</span>
                </div>

                <div class="p-4 overflow-x-auto">
                    <table class="min-w-full text-xs border border-gray-200">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="border p-2 text-left">No</th>
                                <th class="border p-2 text-left">RM Type</th>
                                <th class="border p-2 text-right">RM Total</th>
                                <th class="border p-2 text-left">RM From Tank</th>
                                <th class="border p-2 text-left">FGS Type</th>
                                <th class="border p-2 text-right">FGS Total</th>
                                <th class="border p-2 text-left">FGS To Tank</th>
                                <th class="border p-2 text-left">FGH Type</th>
                                <th class="border p-2 text-right">FGH Total</th>
                                <th class="border p-2 text-left">FGH To Tank</th>
                                <th class="border p-2 text-left">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($shiftRows as $row)
                                <tr class="hover:bg-gray-50">
                                    <td class="border p-2 text-center">{{ $row->no ?? '-' }}</td>
                                    <td class="border p-2">{{ $row->oil_type_rm_name ?? $row->oil_type_rm ?? '-' }}</td>
                                    <td class="border p-2 text-right">
                                        {{ is_null($row->oil_type_rm_total) ? '-' : number_format((float) $row->oil_type_rm_total, 0) }}
                                    </td>
                                    <td class="border p-2">{{ $row->oil_type_rm_from_tank ?? '-' }}</td>
                                    <td class="border p-2">{{ $row->oil_type_fgs_name ?? $row->oil_type_fgs ?? '-' }}</td>
                                    <td class="border p-2 text-right">
                                        {{ is_null($row->oil_type_fgs_total) ? '-' : number_format((float) $row->oil_type_fgs_total, 0) }}
                                    </td>
                                    <td class="border p-2">{{ $row->oil_type_fgs_to_tank ?? '-' }}</td>
                                    <td class="border p-2">{{ $row->oil_type_fgh_name ?? $row->oil_type_fgh ?? '-' }}</td>
                                    <td class="border p-2 text-right">
                                        {{ is_null($row->oil_type_fgh_total) ? '-' : number_format((float) $row->oil_type_fgh_total, 0) }}
                                    </td>
                                    <td class="border p-2">{{ $row->oil_type_fgh_to_tank ?? '-' }}</td>
                                    <td class="border p-2">{{ $row->remarks ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 px-4 pb-4">
                    <div class="rounded-lg border border-gray-200 p-3">
                        <h5 class="font-semibold mb-2">Approval</h5>
                        <div class="grid grid-cols-1 gap-1">
                            <div><span class="text-gray-500">Prepared By:</span> {{ $summary->prepared_by ?? '-' }}</div>
                            <div><span class="text-gray-500">Prepared Date:</span>
                                {{ $summary->prepared_date ? \Carbon\Carbon::parse($summary->prepared_date)->format('d M Y H:i') : '-' }}
                            </div>
                            <div><span class="text-gray-500">Prepared Status:</span> {{ $summary->prepared_status ?? '-' }}
                            </div>
                            <div><span class="text-gray-500">Prepared Remark:</span>
                                {{ $summary->prepared_status_remarks ?? '-' }}</div>
                            <div><span class="text-gray-500">Checked By:</span> {{ $summary->checked_by ?? '-' }}</div>
                            <div><span class="text-gray-500">Checked Date:</span>
                                {{ $summary->checked_date ? \Carbon\Carbon::parse($summary->checked_date)->format('d M Y H:i') : '-' }}
                            </div>
                            <div><span class="text-gray-500">Checked Status:</span> {{ $summary->checked_status ?? '-' }}
                            </div>
                            <div><span class="text-gray-500">Checked Remark:</span>
                                {{ $summary->checked_status_remarks ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="mb-8 border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b">
                <h4 class="font-bold text-gray-800">Utility Usage per Shift</h4>
            </div>
            <div class="p-4 overflow-x-auto">
                <table class="min-w-full text-xs border border-gray-200">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="border p-2 text-left">Shift</th>
                            <th class="border p-2 text-right">Budget Ref Qty</th>
                            <th class="border p-2 text-right">Flowmeter Before</th>
                            <th class="border p-2 text-right">Flowmeter After</th>
                            <th class="border p-2 text-right">Flowmeter Total</th>
                            <th class="border p-2 text-right">Listrik</th>
                            <th class="border p-2 text-right">Air</th>
                            <th class="border p-2 text-right">Yield (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($shiftSummaryRows as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="border p-2">
                                    <span class="inline-block px-2 py-1 rounded bg-red-700 text-white font-bold">Shift
                                        {{ $row->shift }}</span>
                                </td>
                                <td class="border p-2 text-right">
                                    {{ is_null($row->uu_budget_ref_qty) ? '-' : number_format((float) $row->uu_budget_ref_qty, 2) }}
                                </td>
                                <td class="border p-2 text-right">
                                    {{ is_null($row->uu_flowmeter_before) ? '-' : number_format((float) $row->uu_flowmeter_before, 0) }}
                                </td>
                                <td class="border p-2 text-right">
                                    {{ is_null($row->uu_flowmeter_after) ? '-' : number_format((float) $row->uu_flowmeter_after, 0) }}
                                </td>
                                <td class="border p-2 text-right">
                                    {{ is_null($row->uu_flowmeter_total) ? '-' : number_format((float) $row->uu_flowmeter_total, 0) }}
                                </td>
                                <td class="border p-2 text-right">
                                    {{ is_null($row->uu_listrik) ? '-' : number_format((float) $row->uu_listrik, 0) }}
                                </td>
                                <td class="border p-2 text-right">
                                    {{ is_null($row->uu_air) ? '-' : number_format((float) $row->uu_air, 0) }}
                                </td>
                                <td class="border p-2 text-right">
                                    {{ is_null($row->uu_yield_percent) ? '-' : number_format((float) $row->uu_yield_percent, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="border p-4 text-center text-gray-500">Tidak ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mb-2 border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b">
                <h4 class="font-bold text-gray-800">Remarks per Shift</h4>
            </div>
            <div class="p-4 overflow-x-auto">
                <table class="min-w-full text-xs border border-gray-200">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="border p-2 text-left">Shift</th>
                            <th class="border p-2 text-left">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($shiftSummaryRows as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="border p-2">
                                    <span class="inline-block px-2 py-1 rounded bg-red-700 text-white font-bold">Shift
                                        {{ $row->shift }}</span>
                                </td>
                                <td class="border p-2 whitespace-pre-line">{{ $row->remarks ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="border p-4 text-center text-gray-500">Tidak ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
