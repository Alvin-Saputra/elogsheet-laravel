@extends('layouts.app')

@section('page_title', 'Form Transfer')

@section('content')
    <div class="bg-white p-6 rounded shadow-md">
        <div class="flex items-center justify-between mb-6">
            <div>
                <div class="flex items-center space-x-3 mb-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M7 7h10M7 12h10M7 17h6M3 5a2 2 0 012-2h12a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5z" />
                    </svg>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">Form Transfer</h2>
                        <div class="text-sm text-gray-600 mt-1">
                            <span class="font-medium text-gray-700">Logsheet Code:</span>
                            <span class="inline-block px-2 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded">
                                F/QCO-018
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter --}}
        @php
            use Carbon\Carbon;
            $selectedDate = request('filter_tanggal', Carbon::today()->format('Y-m-d'));
        @endphp
        <div class="bg-gray-50 p-4 rounded-md shadow-sm mb-6">
            <form method="GET" action="{{ route('report.form-transfer.index') }}"
                class="flex flex-wrap items-end gap-4">
                <div class="w-full sm:w-44">
                    <label for="filter_tanggal" class="block text-sm font-medium text-gray-700">Tanggal</label>
                    <input type="date" id="filter_tanggal" name="filter_tanggal" value="{{ $selectedDate }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold rounded-lg shadow transition">
                        Filter
                    </button>

                    @if (request()->has('filter_tanggal'))
                        <a href="{{ route('report.form-transfer.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 text-sm font-semibold rounded-lg shadow transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                <thead class="bg-gray-100 text-gray-700 text-sm sticky top-0 z-10">
                    <tr>
                        <th class="px-4 py-2 border-b text-left">No</th>
                        <th class="px-4 py-2 border-b text-left">Transfer ID</th>
                        <th class="px-4 py-2 border-b text-left">Plant</th>
                        <th class="px-4 py-2 border-b text-left">Tanggal</th>
                        <th class="px-4 py-2 border-b text-left">From Dept</th>
                        <th class="px-4 py-2 border-b text-left">To Dept</th>
                        <th class="px-4 py-2 border-b text-center">Prepared</th>
                        <th class="px-4 py-2 border-b text-center">Checked</th>
                        <th class="px-4 py-2 border-b text-center">Approved</th>
                        <th class="px-4 py-2 border-b text-center">Acknowledged</th>
                        <th class="px-4 py-2 border-b text-center">Report</th>
                        <th class="px-4 py-2 border-b text-center">Detail</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-700">
                    @forelse ($transfers as $index => $transfer)
                        <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-gray-100">
                            <td class="px-4 py-2 border-b">
                                {{ ($transfers->currentPage() - 1) * $transfers->perPage() + $index + 1 }}
                            </td>
                            <td class="px-4 py-2 border-b">{{ $transfer->id }}</td>
                            <td class="px-4 py-2 border-b">{{ $transfer->plant }}</td>
                            <td class="px-4 py-2 border-b">
                                {{ $transfer->transaction_date ? \Carbon\Carbon::parse($transfer->transaction_date)->format('Y-m-d') : '-' }}
                            </td>
                            <td class="px-4 py-2 border-b">{{ $transfer->from_dept ?? '-' }}</td>
                            <td class="px-4 py-2 border-b">{{ $transfer->to_dept ?? '-' }}</td>
                            <td class="px-4 py-2 border-b text-center">
                                @include('partials.status-badge', ['status' => $transfer->prepared_status])
                            </td>
                            <td class="px-4 py-2 border-b text-center">
                                @include('partials.status-badge', ['status' => $transfer->checked_status])
                            </td>
                            <td class="px-4 py-2 border-b text-center">
                                @include('partials.status-badge', ['status' => $transfer->approved_status])
                            </td>
                            <td class="px-4 py-2 border-b text-center">
                                @include('partials.status-badge', ['status' => $transfer->acknowledged_status])
                            </td>
                            <td class="px-4 py-2 border-b text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('report.form-transfer.preview', $transfer->id) }}?intention=preview"
                                        target="_blank"
                                        class="px-2 py-1 bg-blue-600 text-white text-xs rounded hover:bg-gray-700 shadow"
                                        title="Preview PDF">
                                        Preview
                                    </a>
                                    <a href="{{ route('report.form-transfer.export', $transfer->id) }}?intention=export"
                                        class="px-2 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 shadow"
                                        title="Download PDF">
                                        Download
                                    </a>
                                </div>
                            </td>
                            <td class="px-4 py-2 border-b text-center">
                                <a href="{{ route('report.form-transfer.show', $transfer->id) }}?intention=show"
                                    class="text-blue-600 hover:text-blue-800 inline-flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                                        class="w-5 h-5 text-blue-600 hover:text-blue-800 transition-colors duration-200">
                                        <path fill="currentColor"
                                            d="M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zM224 160a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zm-8 64l48 0c13.3 0 24 10.7 24 24l0 88 8 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-80 0c-13.3 0-24-10.7-24-24s10.7-24 24-24l24 0 0-64-24 0c-13.3 0-24-10.7-24-24s10.7-24 24-24z" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="px-4 py-6 border-b text-center text-gray-500">
                                No data available for this date.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $transfers->links() }}
        </div>
    </div>
@endsection
