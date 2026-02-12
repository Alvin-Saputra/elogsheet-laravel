@extends('layouts.app')

@section('page_title', 'Report Daily Production Fractionation')

@section('content')
    @php
        $allItems = collect();
        foreach ($groupedReports as $wc => $shifts) {
            foreach ($shifts as $shift => $items) {
                $allItems = $allItems->merge($items);
            }
        }
        $flatReports = $allItems->groupBy('id')->map(function ($rows) {
            $first = $rows->first();
            $shiftList = $rows->pluck('shift')->unique()->sort()->values();
            return (object) [
                'id' => $first->id,
                'work_center' => $first->work_center,
                'shift' => $shiftList->implode(', '),
                'shift_single' => $shiftList->count() === 1 ? $shiftList->first() : null,
                'transaction_date' => $first->transaction_date,
                'entry_by' => $first->entry_by,
                'prepared_status' => $first->prepared_status,
                'checked_status' => $first->checked_status,
            ];
        })->values();
    @endphp

    <div class="bg-white p-6 rounded shadow-md">
        <div class="flex items-center justify-between mb-6">
            <div>
                <div class="flex items-center space-x-3 mb-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2l4 -4M12 20h8a2 2 0 0 0 2-2V8l-6-6H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h4z" />
                    </svg>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">Daily Production Fractionation Section</h2>
                        <div class="text-sm text-gray-600 mt-1">
                            <span class="font-medium text-gray-700">Report Code:</span>
                            <span
                                class="inline-block px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded">F/RFA-004
                                (B)</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('report-daily-production.fractionation.export.excel', ['filter_tanggal' => $tanggal]) }}"
                    target="_blank"
                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg shadow transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24">
                        <path d="M3 10h18M3 6h18M3 14h18M3 18h18" />
                    </svg>Export Excel
                </a>
                <a href="{{ route('report-daily-production.fractionation.export.view', ['filter_tanggal' => $tanggal, 'filter_work_center' => request('filter_work_center')]) }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z" />
                    </svg>View Layout
                </a>
                <a href="{{ route('report-daily-production.fractionation.export.pdf', ['filter_tanggal' => $tanggal, 'filter_work_center' => request('filter_work_center')]) }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg shadow transition"
                    target="_blank">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 10l5 5 5-5M12 4v12" />
                    </svg>Download PDF
                </a>
            </div>
        </div>

        {{-- Filter Section --}}
        <div class="bg-gray-50 p-4 rounded-md shadow-sm mb-6">
            <form method="GET" action="{{ route('report-daily-production.fractionation.index') }}"
                class="flex flex-wrap items-end gap-4">
                <div class="w-full sm:w-44">
                    <label for="filter_tanggal" class="block text-sm font-medium text-gray-700">Date</label>
                    <input type="date" id="filter_tanggal" name="filter_tanggal" value="{{ $tanggal }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                </div>
                <div class="w-full sm:w-48">
                    <label for="filter_work_center" class="block text-sm font-medium text-gray-700">Work Center</label>
                    <select id="filter_work_center" name="filter_work_center"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                        <option value="">Pilih Work Center</option>
                        @foreach ($refineryMachines as $wc)
                            <option value="{{ $wc->work_center }}"
                                {{ request('filter_work_center') == $wc->work_center ? 'selected' : '' }}>
                                {{ $wc->work_center }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold rounded-lg shadow transition">Filter</button>
                    @if (request()->has('filter_tanggal') || request()->has('filter_work_center'))
                        <a href="{{ route('report-daily-production.fractionation.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 text-sm font-semibold rounded-lg shadow transition">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Approval Buttons and Modal --}}
        <div x-data="{ openRejectModal: false }">
            <div class="flex gap-2 mb-4">
                <form action="{{ route('report-daily-production.fractionation.approvalPerDate') }}" method="POST">
                    @csrf
                    <input type="hidden" name="transaction_date" value="{{ $tanggal }}">
                    <input type="hidden" name="approve_status" value="Approved">
                    <button type="submit"
                        class="px-4 py-2 text-sm font-semibold rounded-lg {{ $approvalStatus['canApproveReject'] ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}"
                        {{ !$approvalStatus['canApproveReject'] ? 'disabled' : '' }}>Approve Hari Ini</button>
                </form>
                <button type="button" @click="openRejectModal = true"
                    class="px-4 py-2 text-sm font-semibold rounded-lg {{ $approvalStatus['canApproveReject'] ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}"
                    {{ !$approvalStatus['canApproveReject'] ? 'disabled' : '' }}>Reject Hari Ini</button>
            </div>
            <div x-show="openRejectModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
                x-cloak>
                <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Reject Laporan</h2>
                    <form action="{{ route('report-daily-production.fractionation.approvalPerDate') }}" method="POST">
                        @csrf
                        <input type="hidden" name="transaction_date" value="{{ $tanggal }}">
                        <input type="hidden" name="approve_status" value="Rejected">
                        <div class="mb-4">
                            <label for="remark" class="block text-sm font-medium text-gray-700">Alasan Reject</label>
                            <textarea id="remark" name="remark" rows="3" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"></textarea>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" @click="openRejectModal = false"
                                class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded-lg text-gray-700">Batal</button>
                            <button type="submit"
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">Reject</button>
                        </div>
                    </form>
                </div>
            </div>
            @if ($approvalStatus['statusMessage'])
                <div class="p-3 mb-4 text-sm font-medium rounded-lg text-gray-800 bg-yellow-100 shadow-sm">
                    {{ $approvalStatus['statusMessage'] }}
                </div>
            @endif
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                <thead class="bg-gray-100 text-gray-700 text-xs sticky top-0 z-10">
                    <tr>
                        <th class="px-2 py-2 border-b text-left">No</th>
                        <th class="px-2 py-2 border-b text-left">Ticket No</th>
                        <th class="px-2 py-2 border-b text-left">Date</th>
                        <th class="px-2 py-2 border-b text-left">Shift(s)</th>
                        <th class="px-2 py-2 border-b text-left">Work Center</th>
                        <th class="px-2 py-2 border-b text-left">Entry By</th>
                        <th class="px-2 py-2 border-b text-center">Leader Status</th>
                        <th class="px-2 py-2 border-b text-center">Manager Status</th>
                        <th class="px-2 py-2 border-b text-center">Action</th>
                        <th class="px-2 py-2 border-b text-left">Detail</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-700">
                    @forelse ($flatReports as $index => $report)
                        <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-gray-100">
                            <td class="px-2 py-2 border-b">{{ $index + 1 }}</td>
                            <td class="px-2 py-2 border-b">{{ $report->id }}</td>
                            <td class="px-2 py-2 border-b">
                                {{ $report->transaction_date ? \Carbon\Carbon::parse($report->transaction_date)->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-2 py-2 border-b">{{ $report->shift ?: '-' }}</td>
                            <td class="px-2 py-2 border-b">
                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded bg-green-100 text-green-800">
                                    {{ $report->work_center ?: '-' }}
                                </span>
                            </td>
                            <td class="px-2 py-2 border-b">{{ $report->entry_by }}</td>
                            <td class="px-2 py-2 border-b text-center">
                                @if ($report->prepared_status == 'Approved')
                                    <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Approved</span>
                                @elseif ($report->prepared_status == 'Rejected')
                                    <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">Rejected</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-600">Pending</span>
                                @endif
                            </td>
                            <td class="px-2 py-2 border-b text-center">
                                @if ($report->checked_status == 'Approved')
                                    <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Approved</span>
                                @elseif ($report->checked_status == 'Rejected')
                                    <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">Rejected</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-600">Pending</span>
                                @endif
                            </td>
                            <td class="px-2 py-2 border-b text-center">
                                <div class="flex justify-center gap-2" x-data="{ showReject: false }">
                                    @if ($report->shift_single !== null)
                                        @if ((auth()->user()->roles === 'LEAD_PROD' || auth()->user()->roles === 'LEAD') && is_null($report->prepared_status))
                                            <form action="{{ route('report-daily-production.fractionation.approvalPerShift') }}"
                                                method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="posting_date" value="{{ $tanggal }}">
                                                <input type="hidden" name="shift" value="{{ $report->shift_single }}">
                                                <input type="hidden" name="work_center" value="{{ $report->work_center }}">
                                                <input type="hidden" name="approve_status" value="Approved">
                                                <button type="submit"
                                                    class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 shadow">Approve</button>
                                            </form>
                                            <button type="button" @click="showReject = true"
                                                class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 shadow">Reject</button>
                                        @endif
                                        @if (
                                            (auth()->user()->roles === 'MGR_PROD' || auth()->user()->roles === 'MGR') &&
                                                is_null($report->checked_status) &&
                                                $report->prepared_status === 'Approved')
                                            <form action="{{ route('report-daily-production.fractionation.approvalPerShift') }}"
                                                method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="posting_date" value="{{ $tanggal }}">
                                                <input type="hidden" name="shift" value="{{ $report->shift_single }}">
                                                <input type="hidden" name="work_center" value="{{ $report->work_center }}">
                                                <input type="hidden" name="approve_status" value="Approved">
                                                <button type="submit"
                                                    class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 shadow">Approve</button>
                                            </form>
                                            <button type="button" @click="showReject = true"
                                                class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 shadow">Reject</button>
                                        @endif
                                        {{-- Reject Modal (per shift) --}}
                                        <div x-show="showReject"
                                            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                                            x-cloak>
                                            <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md">
                                                <h2 class="text-lg font-bold mb-4">Confirm Rejection</h2>
                                                <p class="text-sm text-gray-600 mb-2">Reject laporan Work Center
                                                    <strong>{{ $report->work_center }}</strong> Shift
                                                    <strong>{{ $report->shift_single }}</strong>?
                                                </p>
                                                <form method="POST"
                                                    action="{{ route('report-daily-production.fractionation.approvalPerShift') }}">
                                                    @csrf
                                                    <input type="hidden" name="posting_date" value="{{ $tanggal }}">
                                                    <input type="hidden" name="shift" value="{{ $report->shift_single }}">
                                                    <input type="hidden" name="work_center" value="{{ $report->work_center }}">
                                                    <input type="hidden" name="approve_status" value="Rejected">
                                                    <label for="remark-{{ $report->id }}" class="block mb-2">Reason for
                                                        rejection:</label>
                                                    <textarea id="remark-{{ $report->id }}" name="remark" class="w-full border rounded p-2" rows="3" required></textarea>
                                                    <div class="mt-6 flex justify-end gap-2">
                                                        <button type="button" @click="showReject = false"
                                                            class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                                                        <button type="submit"
                                                            class="px-4 py-2 bg-red-600 text-white rounded">Reject</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-500">Multiple shifts</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-2 py-2 border-b text-center">
                                <a href="{{ route('report-daily-production.fractionation.show', ['id' => $report->id]) }}"
                                    class="text-blue-600 hover:text-blue-800"><svg xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 512 512" class="w-5 h-5 inline-block">
                                        <path fill="currentColor"
                                            d="M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zM224 160a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zm-8 64l48 0c13.3 0 24 10.7 24 24l0 88 8 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-80 0c-13.3 0-24-10.7-24-24s10.7-24 24-24l24 0 0-64-24 0c-13.3 0-24-10.7-24-24s10.7-24 24-24z" />
                                    </svg></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-4 text-center text-gray-500">Data tidak tersedia.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
