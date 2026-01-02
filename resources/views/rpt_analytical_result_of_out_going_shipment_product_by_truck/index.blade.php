@extends('layouts.app')

@section('page_title', 'Analytical Result of Outgoing Shipment Product By Truck')

@section('content')

    @php
        use Carbon\Carbon;
        $selectedDate = request('filter_tanggal', Carbon::today()->format('Y-m-d'));
    @endphp

    <div class="bg-white p-6 rounded shadow-md">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 7v10a2 2 0 0 0 2 2h3l3 3 3-3h3a2 2 0 0 0 2-2V7M16 3H8v4h8V3z" />
                </svg>

                <div>
                    <h2 class="text-lg font-semibold text-gray-800">
                        Analytical Result of Outgoing Shipment Product by Truck
                    </h2>
                    <div class="text-sm text-gray-600 mt-1">
                        <span class="font-medium text-gray-700">Logsheet Code:</span>
                        <span class="inline-block px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded">
                            F-QOC-13
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter --}}
        <div class="bg-gray-50 p-4 rounded-md shadow-sm mb-6">
            <form method="GET" action="{{ route('analytical-result-outgoing-shipment-product-by-truck.index') }}"
                  class="flex flex-wrap items-end gap-4">

                <div class="w-full sm:w-44">
                    <label class="block text-sm font-medium text-gray-700">Tanggal</label>
                    <input type="date" name="filter_tanggal" value="{{ $selectedDate }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm font-semibold rounded-lg">
                        Filter
                    </button>

                    @if(request()->has('filter_tanggal'))
                        <a href="{{ route('analytical-result-outgoing-shipment-product-by-truck.index') }}"
                           class="px-4 py-2 bg-gray-300 text-sm rounded-lg">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                <thead class="bg-gray-100 text-gray-700 text-sm">
                <tr>
                    <th class="px-4 py-2 border-b text-left">No</th>
                    <th class="px-4 py-2 border-b text-left">Report ID</th>
                    <th class="px-4 py-2 border-b text-left">Product</th>
                    <th class="px-4 py-2 border-b text-left">Quantity</th>
                    <th class="px-4 py-2 border-b text-left">Ship / Destination</th>
                    <th class="px-4 py-2 border-b text-center">Verified Status</th>
                    <th class="px-4 py-2 border-b text-center">Approved Status</th>
                    <th class="px-4 py-2 border-b text-center">Action</th>
                    <th class="px-4 py-2 border-b text-center">Report</th>
                    <th class="px-4 py-2 border-b text-center">Detail</th>
                </tr>
                </thead>

                <tbody class="text-sm">
                @forelse($headers as $index => $doc)
                    <tr class="{{ $index % 2 ? 'bg-gray-50' : 'bg-white' }}">
                        <td class="px-4 py-2 border-b">{{ $index + 1 }}</td>
                        <td class="px-4 py-2 border-b">{{ $doc->id }}</td>
                        <td class="px-4 py-2 border-b">{{ $doc->product_name }}</td>
                        <td class="px-4 py-2 border-b">{{ $doc->quantity }}</td>
                        <td class="px-4 py-2 border-b">
                            <div class="text-xs text-gray-600">{{ $doc->ships_name ?? '-' }}</div>
                            <div class="text-xs text-gray-400">{{ $doc->destination ?? $doc->load_port ?? '-' }}</div>
                        </td>

                        {{-- Prepared / Verified --}}
                        <td class="px-4 py-2 border-b text-center">
                            @if(isset($doc->corrected_status) && $doc->corrected_status === 'Approved')
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs rounded">Approved</span>
                            @elseif(isset($doc->corrected_status) && $doc->corrected_status === 'Rejected')
                                <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs rounded">Rejected</span>
                            @else
                                <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded">Pending</span>
                            @endif
                        </td>

                        {{-- Approved --}}
                        <td class="px-4 py-2 border-b text-center">
                            @if(isset($doc->approved_status) && $doc->approved_status === 'Approved')
                                <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs rounded">Approved</span>
                            @elseif(isset($doc->approved_status) && $doc->approved_status === 'Rejected')
                                <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs rounded">Rejected</span>
                            @else
                                <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded">Pending</span>
                            @endif
                        </td>

                        {{-- Action --}}
                        <td class="px-4 py-2 border-b text-center">
                            <div class="flex justify-center gap-2">

                                {{-- SHIFT LEADER (prepare) --}}
                                @if (empty($doc->corrected_status))
                                    @if (auth()->user()->roles === 'LEAD' || auth()->user()->roles === 'LEAD_QC')
                                        <form method="POST"
                                              action="{{ route('analytical-result-outgoing-shipment-product-by-truck.approveReject', $doc->id) }}?status=Approved">
                                            @csrf
                                            <button class="px-3 py-1 bg-green-600 text-white text-xs rounded shadow">
                                                Approve
                                            </button>
                                        </form>

                                        <div x-data="{ open: false }">
                                            <button type="button" @click="open = true"
                                                    class="px-3 py-1 bg-red-600 text-white text-xs rounded shadow hover:bg-red-700">
                                                Reject
                                            </button>

                                            {{-- Modal --}}
                                            <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                                                <div @click.outside="open = false" class="bg-white w-full max-w-md rounded-lg shadow-lg p-6">
                                                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                                                        Reject Report #{{ $doc->id }}
                                                    </h3>

                                                    <form method="POST"
                                                          action="{{ route('analytical-result-outgoing-shipment-product-by-truck.approveReject', $doc->id) }}?status=Rejected">
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
                                                            <button type="button" @click="open = false"
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
                                        <button class="px-3 py-1 bg-gray-400 text-white text-xs rounded opacity-50" disabled>Approve</button>
                                        <button class="px-3 py-1 bg-gray-400 text-white text-xs rounded opacity-50" disabled>Reject</button>
                                    @endif

                                {{-- MANAGER --}}
                                @elseif ($doc->corrected_status === 'Approved' && empty($doc->approved_status))
                                    @if (auth()->user()->roles === 'MGR' || auth()->user()->roles === 'MGR_QC' || auth()->user()->roles === 'ADM')
                                        <form method="POST"
                                              action="{{ route('analytical-result-outgoing-shipment-product-by-truck.approveReject', $doc->id) }}?status=Approved">
                                            @csrf
                                            <button class="px-3 py-1 bg-green-600 text-white text-xs rounded shadow">
                                                Approve
                                            </button>
                                        </form>

                                        <div x-data="{ open: false }">
                                            <button type="button" @click="open = true"
                                                    class="px-3 py-1 bg-red-600 text-white text-xs rounded shadow hover:bg-red-700">
                                                Reject
                                            </button>

                                            {{-- Modal --}}
                                            <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                                                <div @click.outside="open = false" class="bg-white w-full max-w-md rounded-lg shadow-lg p-6">
                                                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                                                        Reject Report #{{ $doc->id }}
                                                    </h3>

                                                    <form method="POST"
                                                          action="{{ route('analytical-result-outgoing-shipment-product-by-truck.approveReject', $doc->id) }}?status=Rejected">
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
                                                            <button type="button" @click="open = false"
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
                                        <button class="px-3 py-1 bg-gray-400 text-white text-xs rounded opacity-50" disabled>Approve</button>
                                        <button class="px-3 py-1 bg-gray-400 text-white text-xs rounded opacity-50" disabled>Reject</button>
                                    @endif

                                {{-- Final --}}
                                @else
                                    <span class="text-xs text-gray-500">
                                        {{ $doc->approved_status ?? $doc->corrected_status }}
                                    </span>
                                @endif
                            </div>
                        </td>

                        {{-- Report: Preview / Download --}}
                        <td class="px-4 py-2 border-b">
                          <div class="flex items-center gap-2 justify-between">
                            <a target="_blank"
                              href="{{ route('analytical-result-outgoing-shipment-product-by-truck.preview', $doc->id) }}?intention=preview"
                              class="inline-flex items-center px-2 py-1 bg-blue-600 text-white text-xs rounded">
                              Preview
                            </a>

                            <a href="{{ route('analytical-result-outgoing-shipment-product-by-truck.export', $doc->id) }}?intention=export"
                              class="inline-flex items-center px-2 py-1 bg-red-600 text-white text-xs rounded">
                              Download
                            </a>
                          </div>
                        </td>

                        {{-- Detail --}}
                        <td class="px-4 py-2 border-b text-center">
                            <a href="{{ route('analytical-result-outgoing-shipment-product-by-truck.show', $doc->id) }}?intention=show"
                               class="inline-flex items-center justify-center text-blue-600 hover:text-blue-800 transition-colors duration-200"
                               title="View Detail">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="w-5 h-5">
                                    <path fill="currentColor"
                                          d="M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zM224 160a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zm-8 64l48 0c13.3 0 24 10.7 24 24l0 88 8 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-80 0c-13.3 0-24-10.7-24-24s10.7-24 24-24l24 0 0-64-24 0c-13.3 0-24-10.7-24-24s10.7-24 24-24z" />
                                </svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center py-6 text-gray-500">
                            No data available
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
