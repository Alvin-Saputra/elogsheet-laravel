@extends('layouts.app')

@section('page_title', 'Laporan Daily Storage Tank Analytical')

@section('content')
<div class="bg-white p-6 rounded shadow-md">

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path d="M3 3v18h18M16 8l-4 4-4-4M16 16l-4 4-4-4" />
            </svg>
            <div>
                <h2 class="text-lg font-semibold text-gray-800">
                    Daily Storage Tank Analytical
                </h2>
                <div class="text-sm text-gray-600 mt-1">
                    <span class="font-medium text-gray-700">Kode Logsheet:</span>
                    <span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded">
                        F/QCO-001
                    </span>
                </div>
            </div>
        </div>

        {{-- ACTION --}}
        <div class="flex gap-2">
            <a href="{{ route('daily-storage-tank-analytical.export.view', ['filter_tanggal' => $tanggal]) }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg">
                View Layout
            </a>
            <a href="{{ route('daily-storage-tank-analytical.export.pdf', ['filter_tanggal' => $tanggal]) }}"
                class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm rounded-lg">
                Download PDF
            </a>
        </div>
    </div>

    {{-- FILTER --}}
    <div class="bg-gray-50 p-4 rounded mb-6">
        <form method="GET" action="{{ route('daily-storage-tank-analytical.index') }}"
            class="flex items-end gap-4 flex-wrap">
            <div>
                <label class="block text-sm font-medium">Tanggal</label>
                <input type="date" name="filter_tanggal" value="{{ $tanggal }}"
                    class="mt-1 rounded border-gray-300">
            </div>
            <button type="submit"
                class="px-4 py-2 bg-gray-800 text-white rounded">
                Filter
            </button>
            <a href="{{ route('daily-storage-tank-analytical.index') }}"
                class="px-4 py-2 bg-gray-300 rounded text-gray-800">
                Reset
            </a>
        </form>
    </div>

    {{-- TABLE --}}
    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-200 text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-3 py-2">No</th>
                    <th class="px-3 py-2">Report ID</th>
                    <th class="px-3 py-2">Plant</th>
                    <th class="px-3 py-2">Tanggal</th>
                    <th class="px-3 py-2">Tank No</th>
                    <th class="px-3 py-2">Oil Type</th>
                    <th class="px-3 py-2">Prepared</th>
                    <th class="px-3 py-2">Approved</th>
                    <th class="px-3 py-2">Action</th>
                    <th class="px-3 py-2">Detail</th>
                </tr>
            </thead>

            <tbody>
            @forelse ($data as $item)
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-3 py-2 text-center">{{ $loop->iteration }}</td>

                    <td class="px-3 py-2 text-center">
                        {{ $item->report_id ?? '-' }}
                    </td>

                    <td class="px-3 py-2 text-center">
                        {{ $item->plant ?? '-' }}
                    </td>

                    {{-- TANGGAL --}}
                    <td class="px-3 py-2 text-center">
                        {{ $item->transaction_date
                            ? \Carbon\Carbon::parse($item->transaction_date)->format('d-m-Y H:i')
                            : '-' }}
                    </td>

                    <td class="px-3 py-2 text-center">
                        {{ $item->tank_no }}
                    </td>

                    <td class="px-3 py-2 text-center">
                        {{ $item->oil_type ?? '-' }}
                    </td>

                    <td class="px-3 py-2 text-center">
                        @include('partials.status-badge', ['status' => $item->prepared_status])
                    </td>

                    <td class="px-3 py-2 text-center">
                        @include('partials.status-badge', ['status' => $item->approved_status])
                    </td>

                    {{-- ACTION --}}
                    <td class="px-3 py-2 text-center">
                        @include('rpt_daily_storage_tank_analytical._action', ['item' => $item])
                    </td>

                    {{-- DETAIL --}}
                    <td class="px-3 py-2 text-center">
                        @if ($item->report_id)
                            <a href="{{ route('daily-storage-tank-analytical.show', $item->report_id) }}"
                               class="text-blue-600 hover:underline">
                                Detail
                            </a>
                        @else
                            <span class="text-gray-400 italic text-xs">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="px-4 py-6 text-center text-gray-500">
                        No data available.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
