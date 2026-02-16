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
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('report.form-transfer.export.view', ['filter_tanggal' => $tanggal]) }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z" />
                    </svg>
                    View Layout
                </a>
                <a href="{{ route('report.form-transfer.export.pdf', ['filter_tanggal' => $tanggal]) }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg shadow transition"
                    target="_blank">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 10l5 5 5-5M12 4v12" />
                    </svg>
                    Download PDF
                </a>
            </div>
        </div>

        {{-- Filter --}}
        <div class="bg-gray-50 p-4 rounded-md shadow-sm mb-6">
            <form method="GET" action="{{ route('report.form-transfer.index') }}"
                class="flex flex-wrap items-end gap-4">
                <div class="w-full sm:w-44">
                    <label for="filter_tanggal" class="block text-sm font-medium text-gray-700">Tanggal</label>
                    <input type="date" id="filter_tanggal" name="filter_tanggal" value="{{ $tanggal }}"
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

        {{-- Bulk Action Buttons --}}
        <div class="mb-4" x-data="{ bulkApproveAll: false, bulkRejectAll: false, bulkRemark: '' }">
            @if (auth()->user()->roles === 'LEAD' || auth()->user()->roles === 'LEAD_QC')
                <div class="flex gap-2">
                    <button type="button" @click="bulkApproveAll = true"
                        class="px-4 py-2 text-sm font-semibold rounded-lg {{ $transfers->whereNull('prepared_status')->count() > 0 ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}"
                        {{ $transfers->whereNull('prepared_status')->count() > 0 ? '' : 'disabled' }}>
                        Approve All
                    </button>
                    <button type="button" @click="bulkRejectAll = true"
                        class="px-4 py-2 text-sm font-semibold rounded-lg {{ $transfers->whereNull('prepared_status')->count() > 0 ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}"
                        {{ $transfers->whereNull('prepared_status')->count() > 0 ? '' : 'disabled' }}>
                        Reject All
                    </button>
                </div>
                @if ($transfers->whereNull('prepared_status')->count() === 0)
                    @if ($transfers->count() === 0)
                        <small class="text-gray-500">*tidak ada data pada tanggal ini</small>
                    @else
                        <small class="text-gray-500">*semua data sudah di-approve</small>
                    @endif
                @endif
            @elseif (auth()->user()->roles === 'MGR' || auth()->user()->roles === 'MGR_QC' || auth()->user()->roles === 'ADM')
                <div class="flex gap-2">
                    <button type="button" @click="bulkApproveAll = true"
                        class="px-4 py-2 text-sm font-semibold rounded-lg {{ $transfers->where('prepared_status', 'Approved')->whereNull('approved_status')->count() > 0 ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}"
                        {{ $transfers->where('prepared_status', 'Approved')->whereNull('approved_status')->count() > 0 ? '' : 'disabled' }}>
                        Approve All
                    </button>
                    <button type="button" @click="bulkRejectAll = true"
                        class="px-4 py-2 text-sm font-semibold rounded-lg {{ $transfers->where('prepared_status', 'Approved')->whereNull('approved_status')->count() > 0 ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}"
                        {{ $transfers->where('prepared_status', 'Approved')->whereNull('approved_status')->count() > 0 ? '' : 'disabled' }}>
                        Reject All
                    </button>
                </div>
                @if ($transfers->where('prepared_status', 'Approved')->whereNull('approved_status')->count() === 0)
                    @if ($transfers->where('prepared_status', 'Approved')->count() === 0)
                        <small class="text-gray-500">*tidak ada data pada tanggal ini</small>
                    @else
                        <small class="text-gray-500">*semua data sudah di-approve</small>
                    @endif
                @endif
            @endif

            {{-- Bulk Approve Modal --}}
            <div x-show="bulkApproveAll" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" x-cloak>
                <div class="bg-white p-6 rounded-lg shadow-xl">
                    <h2 class="text-lg font-bold mb-4">Approve Semua</h2>
                    <p>Apakah Anda yakin ingin approve semua laporan?</p>
                    <div class="mt-6 flex justify-end gap-2">
                        <button @click="bulkApproveAll = false" class="px-4 py-2 bg-gray-300 rounded">Batal</button>
                        <form method="POST" action="{{ route('report.form-transfer.bulk-approve') }}" class="inline">
                            @csrf
                            <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Approve Semua</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Bulk Reject Modal --}}
            <div x-show="bulkRejectAll" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" x-cloak>
                <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md">
                    <h2 class="text-lg font-bold mb-4">Reject Semua</h2>
                    <form method="POST" action="{{ route('report.form-transfer.bulk-reject') }}">
                        @csrf
                        <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                        <label for="bulk-remark" class="block mb-2">Alasan Reject:</label>
                        <textarea id="bulk-remark" name="remark" class="w-full border rounded p-2" rows="3" required x-model="bulkRemark"></textarea>
                        <div class="mt-6 flex justify-end gap-2">
                            <button type="button" @click="bulkRejectAll = false" class="px-4 py-2 bg-gray-300 rounded">Batal</button>
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded">Reject Semua</button>
                        </div>
                    </form>
                </div>
            </div>
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
                        <th class="px-4 py-2 border-b text-center">Verified Status</th>
                        <th class="px-4 py-2 border-b text-center">Approved Status</th>
                        <th class="px-4 py-2 border-b text-center">Action</th>
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
                            {{-- Verified Status (Prepared) --}}
                            <td class="px-4 py-2 border-b text-center">
                                <div class="flex items-center justify-center gap-1">
                                    @if ($transfer->prepared_status == 'Approved')
                                        <span class="px-2 py-0.5 text-xs rounded bg-blue-100 text-blue-700">Approved</span>
                                    @elseif ($transfer->prepared_status == 'Rejected')
                                        <span class="px-2 py-0.5 text-xs rounded bg-red-100 text-red-700">Rejected</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs rounded bg-gray-100 text-gray-600">Pending</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Approved Status --}}
                            <td class="px-4 py-2 border-b text-center">
                                <div class="flex items-center justify-center gap-1 mt-1">
                                    @if ($transfer->approved_status == 'Approved')
                                        <span class="px-2 py-0.5 text-xs rounded bg-green-100 text-green-700">Approved</span>
                                    @elseif ($transfer->approved_status == 'Rejected')
                                        <span class="px-2 py-0.5 text-xs rounded bg-red-100 text-red-700">Rejected</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs rounded bg-gray-100 text-gray-600">Pending</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Action Column with Approval Buttons --}}
                            <td class="px-4 py-2 border-b text-center">
                                <div class="flex justify-center gap-2" x-data="{ showApprove: false, showReject: false }">

                                    {{-- LEAD (Verify) ACTIONS --}}
                                    @if (!$transfer->prepared_status)
                                        @if (auth()->user()->roles === 'LEAD' || auth()->user()->roles === 'LEAD_QC')
                                            <button @click="showApprove = true"
                                                class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 shadow"
                                                title="Shift Leader Approve">
                                                Approve
                                            </button>
                                            <button @click="showReject = true"
                                                class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 shadow"
                                                title="Shift Leader Reject">
                                                Reject
                                            </button>

                                            {{-- Approve Modal --}}
                                            <div x-show="showApprove"
                                                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                                                x-cloak>
                                                <div class="bg-white p-6 rounded-lg shadow-xl">
                                                    <h2 class="text-lg font-bold mb-4">Confirm Verification</h2>
                                                    <p>Approve ticket #{{ $transfer->id }}?</p>
                                                    <div class="mt-6 flex justify-end gap-2">
                                                        <button @click="showApprove = false"
                                                            class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                                                        <form method="POST"
                                                            action="{{ route('report.form-transfer.approve', $transfer->id) }}?status=Approved"
                                                            class="inline">
                                                            @csrf
                                                            <button type="submit"
                                                                class="px-4 py-2 bg-green-600 text-white rounded">Approve</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Reject Modal --}}
                                            <div x-show="showReject"
                                                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                                                x-cloak>
                                                <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md">
                                                    <h2 class="text-lg font-bold mb-4">Confirm Rejection</h2>
                                                    <form method="POST"
                                                        action="{{ route('report.form-transfer.approve', $transfer->id) }}?status=Rejected">
                                                        @csrf
                                                        <label for="remark-{{ $transfer->id }}" class="block mb-2">Reason for rejection:</label>
                                                        <textarea id="remark-{{ $transfer->id }}" name="remark" class="w-full border rounded p-2" rows="3" required></textarea>
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
                                            {{-- Disabled for other roles --}}
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

                                    {{-- MANAGER (Approve) ACTIONS --}}
                                    @elseif ($transfer->prepared_status == 'Approved' && !$transfer->approved_status)
                                        @if (auth()->user()->roles === 'MGR' || auth()->user()->roles === 'MGR_QC' || auth()->user()->roles === 'ADM')
                                            <button @click="showApprove = true"
                                                class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 shadow"
                                                title="Manager Approve">
                                                Approve
                                            </button>
                                            <button @click="showReject = true"
                                                class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 shadow"
                                                title="Manager Reject">
                                                Reject
                                            </button>

                                            {{-- Approve Modal --}}
                                            <div x-show="showApprove"
                                                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                                                x-cloak>
                                                <div class="bg-white p-6 rounded-lg shadow-xl">
                                                    <h2 class="text-lg font-bold mb-4">Confirm Approval</h2>
                                                    <p>Approve ticket #{{ $transfer->id }}?</p>
                                                    <div class="mt-6 flex justify-end gap-2">
                                                        <button @click="showApprove = false"
                                                            class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                                                        <form method="POST"
                                                            action="{{ route('report.form-transfer.approve', $transfer->id) }}?status=Approved"
                                                            class="inline">
                                                            @csrf
                                                            <button type="submit"
                                                                class="px-4 py-2 bg-green-600 text-white rounded">Approve</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Reject Modal --}}
                                            <div x-show="showReject"
                                                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                                                x-cloak>
                                                <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md">
                                                    <h2 class="text-lg font-bold mb-4">Confirm Rejection</h2>
                                                    <form method="POST"
                                                        action="{{ route('report.form-transfer.approve', $transfer->id) }}?status=Rejected">
                                                        @csrf
                                                        <label for="remark-{{ $transfer->id }}" class="block mb-2">Reason for rejection:</label>
                                                        <textarea id="remark-{{ $transfer->id }}" name="remark" class="w-full border rounded p-2" rows="3" required></textarea>
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
                                            {{-- Disabled for other roles --}}
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

                                    {{-- FINAL STATUS (NO ACTIONS) --}}
                                    @else
                                        <span class="text-xs text-gray-500">
                                            @if ($transfer->prepared_status == 'Rejected')
                                                Rejected
                                            @elseif ($transfer->approved_status == 'Approved')
                                                Approved
                                            @elseif ($transfer->approved_status == 'Rejected')
                                                Rejected
                                            @endif
                                        </span>
                                    @endif
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
                            <td colspan="10" class="px-4 py-6 border-b text-center text-gray-500">
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
