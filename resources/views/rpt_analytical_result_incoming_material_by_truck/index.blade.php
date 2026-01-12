@extends('layouts.app')

@section('page_title', 'Analytical Result Incoming Material By Truck')

@section('content')

    <div class="bg-white p-6 rounded shadow-md">
        <div class="flex items-center justify-between mb-6">
            <div>
                <div class="flex items-center space-x-3 mb-1">

                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 2a7 7 0 0 0-7 7c0 2.5 1.5 4.7 3.5 6a3 3 0 0 1 1.5 2.6V20h4v-2.4a3 3 0 0 1 1.5-2.6c2-1.3 3.5-3.5 3.5-6a7 7 0 0 0-7-7z" />
                    </svg>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">Analytical Result Incoming Material By Truck</h2>
                        <div class="text-sm text-gray-600 mt-1">
                            <span class="font-medium text-gray-700">Logsheet Code:</span>
                            <span class="inline-block px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded">
                                F-QOC-10
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Flash messages --}}
        @if(session('error'))
            <div class="mb-4 px-4 py-2 bg-red-100 text-red-700 rounded">
                {{ session('error') }}
            </div>
        @endif

        @if(session('success-approve'))
            <div class="mb-4 px-4 py-2 bg-green-100 text-green-700 rounded">
                {{ session('success-approve') }}
            </div>
        @endif

        @if(session('success-reject'))
            <div class="mb-4 px-4 py-2 bg-green-100 text-green-700 rounded">
                {{ session('success-reject') }}
            </div>
        @endif

        {{-- Filter --}}
        @php
            use Carbon\Carbon;
            $selectedDate = request('filter_tanggal', Carbon::today()->format('Y-m-d'));
        @endphp
        <div class="bg-gray-50 p-4 rounded-md shadow-sm mb-6">
            <form method="GET" action="{{ route('analytical-result-incoming-material-by-truck.index') }}"
                class="flex flex-wrap items-end gap-4">
                <div class="w-full sm:w-44">
                    <label for="filter_tanggal" class="block text-sm font-medium text-gray-700">Tanggal</label>
                    <input type="date" id="filter_tanggal" name="filter_tanggal" value="{{ $selectedDate }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                </div>

                {{-- Tombol Filter --}}
                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold rounded-lg shadow transition">
                        Filter
                    </button>

                    @if (request()->has('filter_tanggal'))
                        <a href="{{ route('analytical-result-incoming-material-by-truck.index') }}"
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
                        <th class="px-4 py-2 border-b text-left">Report ID</th>
                        <th class="px-4 py-2 border-b text-left">Arrival</th>
                        <th class="px-4 py-2 border-b text-left">Plant</th>
                        <th class="px-4 py-2 border-b text-left">Material</th>
                        <th class="px-4 py-2 border-b text-center">Verified Status</th>
                        <th class="px-4 py-2 border-b text-center">Approved Status</th>
                        <th class="px-4 py-2 border-b text-center">Action</th>
                        <th class="px-4 py-2 border-b text-center">Report</th>
                        <th class="px-4 py-2 border-b text-center">Detail</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-700">
                    @forelse ($headers as $index => $doc)
                        <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-gray-100">
                            <td class="px-4 py-2 border-b">{{ $index + 1 }}</td>
                            <td class="px-4 py-2 border-b">{{ $doc->id }}</td>
                            <td class="px-4 py-2 border-b">
                                {{ \Carbon\Carbon::parse($doc->arrival_date)->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-4 py-2 border-b">{{ $doc->plant }}</td>
                            <td class="px-4 py-2 border-b">{{ $doc->material }}</td>

                            {{-- Verified Status --}}
                            <td class="px-4 py-2 border-b text-center">
                                <div class="flex items-center justify-center gap-1">
                                    @if ($doc->prepared_status == 'Approved')
                                        <span class="px-2 py-0.5 text-xs rounded bg-blue-100 text-blue-700">Approved</span>
                                    @elseif ($doc->prepared_status == 'Rejected')
                                        <span class="px-2 py-0.5 text-xs rounded bg-red-100 text-red-700">Rejected</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs rounded bg-gray-100 text-gray-600">Pending</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Approved Status --}}
                            <td class="px-4 py-2 border-b text-center">
                                <div class="flex items-center justify-center gap-1 mt-1">
                                    @if ($doc->approved_status == 'Approved')
                                        <span class="px-2 py-0.5 text-xs rounded bg-green-100 text-green-700">Approved</span>
                                    @elseif ($doc->approved_status == 'Rejected')
                                        <span class="px-2 py-0.5 text-xs rounded bg-red-100 text-red-700">Rejected</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs rounded bg-gray-100 text-gray-600">Pending</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Action --}}
                            <td class="px-4 py-2 border-b text-center">
                                <div class="flex justify-center gap-2">

                                    {{-- SHIFT LEADER (Verify) ACTIONS --}}
                                    @if (!$doc->prepared_status)
                                        @if (auth()->user()->roles === 'LEAD' || auth()->user()->roles === 'LEAD_QC')
                                            {{-- Approve form (unchanged) --}}
                                            <form
                                                action="{{ route('analytical-result-incoming-material-by-truck.approveReject', $doc->id) }}?status=Approved"
                                                method="POST">
                                                @csrf
                                                <button type="submit"
                                                    class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-blue-700 shadow"
                                                    title="Shift Leader Approve">
                                                    Approve
                                                </button>
                                            </form>

                                            {{-- Reject: modal with remark --}}
                                            <div x-data="{ open{{ $doc->id }}: false }" class="relative">
                                                <button type="button" @click="open{{ $doc->id }} = true"
                                                    class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 shadow"
                                                    title="Shift Leader Reject">
                                                    Reject
                                                </button>

                                                {{-- Modal --}}
                                                <div x-show="open{{ $doc->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                                                    <div @click.outside="open{{ $doc->id }} = false" class="bg-white w-full max-w-md rounded-lg shadow-lg p-6">
                                                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                                                            Reject Report #{{ $doc->id }}
                                                        </h3>

                                                        <form method="POST"
                                                              action="{{ route('analytical-result-incoming-material-by-truck.approveReject', $doc->id) }}?status=Rejected">
                                                            @csrf

                                                            <div class="mb-4">
                                                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                                                    Reject Reason <span class="text-red-500">*</span>
                                                                </label>
                                                                <textarea name="remark" required rows="3"
                                                                          class="w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-red-200 text-sm"
                                                                          placeholder="Enter rejection reason..."></textarea>
                                                            </div>

                                                            <div class="flex justify-end gap-2">
                                                                <button type="button" @click="open{{ $doc->id }} = false"
                                                                        class="px-4 py-2 text-sm bg-gray-300 rounded">
                                                                    Cancel
                                                                </button>

                                                                <button type="submit"
                                                                        class="px-4 py-2 text-sm bg-red-600 text-white rounded hover:bg-red-700">
                                                                    Confirm Reject
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                        @else
                                            <button type="button"
                                                class="px-3 py-1 bg-gray-400 text-white text-xs rounded shadow opacity-50 cursor-not-allowed"
                                                disabled>
                                                Approve
                                            </button>
                                            <button type="button"
                                                class="px-3 py-1 bg-gray-400 text-white text-xs rounded shadow opacity-50 cursor-not-allowed"
                                                disabled>
                                                Reject
                                            </button>
                                        @endif

                                    {{-- MANAGER ACTIONS --}}
                                    @elseif ($doc->prepared_status == 'Approved' && !$doc->approved_status)
                                        @if (auth()->user()->roles == 'MGR' || auth()->user()->roles == 'MGR_QC')
                                            {{-- Approve --}}
                                            <form
                                                action="{{ route('analytical-result-incoming-material-by-truck.approveReject', $doc->id) }}?status=Approved"
                                                method="POST">
                                                @csrf
                                                <button type="submit"
                                                    class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 shadow"
                                                    title="Manager Approve">
                                                    Approve
                                                </button>
                                            </form>

                                            {{-- Reject: modal with remark --}}
                                            <div x-data="{ open{{ $doc->id }}: false }" class="relative">
                                                <button type="button" @click="open{{ $doc->id }} = true"
                                                    class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 shadow"
                                                    title="Manager Reject">
                                                    Reject
                                                </button>

                                                {{-- Modal --}}
                                                <div x-show="open{{ $doc->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                                                    <div @click.outside="open{{ $doc->id }} = false" class="bg-white w-full max-w-md rounded-lg shadow-lg p-6">
                                                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                                                            Reject Report #{{ $doc->id }}
                                                        </h3>

                                                        <form method="POST"
                                                              action="{{ route('analytical-result-incoming-material-by-truck.approveReject', $doc->id) }}?status=Rejected">
                                                            @csrf

                                                            <div class="mb-4">
                                                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                                                    Reject Reason <span class="text-red-500">*</span>
                                                                </label>
                                                                <textarea name="remark" required rows="3"
                                                                          class="w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-red-200 text-sm"
                                                                          placeholder="Enter rejection reason..."></textarea>
                                                            </div>

                                                            <div class="flex justify-end gap-2">
                                                                <button type="button" @click="open{{ $doc->id }} = false"
                                                                        class="px-4 py-2 text-sm bg-gray-300 rounded">
                                                                    Cancel
                                                                </button>

                                                                <button type="submit"
                                                                        class="px-4 py-2 text-sm bg-red-600 text-white rounded hover:bg-red-700">
                                                                    Confirm Reject
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                        @else
                                            <button type="button"
                                                class="px-3 py-1 bg-gray-400 text-white text-xs rounded shadow opacity-50 cursor-not-allowed"
                                                disabled>
                                                Approve
                                            </button>
                                            <button type="button"
                                                class="px-3 py-1 bg-gray-400 text-white text-xs rounded shadow opacity-50 cursor-not-allowed"
                                                disabled>
                                                Reject
                                            </button>
                                        @endif

                                    {{-- Final / no actions --}}
                                    @else
                                        <span class="text-xs text-gray-500">
                                            @if ($doc->prepared_status == 'Rejected')
                                                Rejected
                                            @elseif ($doc->approved_status == 'Approved')
                                                Approved
                                            @elseif ($doc->approved_status == 'Rejected')
                                                Rejected
                                            @endif
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Report --}}
                            <td class="px-4 py-2 border-b text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('analytical-result-incoming-material-by-truck.preview', $doc->id) }}?intention=preview"
                                        target="_blank"
                                        class="px-2 py-1 bg-blue-600 text-white text-xs rounded hover:bg-gray-700 shadow"
                                        title="Preview PDF">
                                        Preview
                                    </a>
                                    <a href="{{ route('analytical-result-incoming-material-by-truck.export', $doc->id) }}?intention=export"
                                        class="px-2 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 shadow"
                                        title="Download PDF">
                                        Download
                                    </a>
                                </div>
                            </td>

                            {{-- Detail --}}
                            <td class="px-4 py-2 border-b text-center">
                                <a href="{{ route('analytical-result-incoming-material-by-truck.show', $doc->id) }}?intention=show"
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

            {{-- transient flash notifications (auto-hide handled by Alpine) --}}
            <div x-data="{ show: {{ session('success-approve') ? 'true' : 'false' }} }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                class="fixed bottom-5 right-5 bg-green-500 text-white px-4 py-2 rounded shadow-lg">
                {{ session('success-approve') }}
            </div>

            <div x-data="{ show: {{ session('success-reject') ? 'true' : 'false' }} }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                class="fixed bottom-5 right-5 bg-red-500 text-white px-4 py-2 rounded shadow-lg">
                {{ session('success-reject') }}
            </div>
        </div>
    </div>
@endsection
