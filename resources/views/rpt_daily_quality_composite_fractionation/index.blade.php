@extends('layouts.app')

@section('page_title', 'Laporan Daily Quality Composite Fractionation')

@section('content')
    <div class="bg-white p-6 rounded shadow-md">
        {{-- Judul Modern --}}

        <div class="flex items-center justify-between mb-6">
            <div>
                <div class="flex items-center space-x-3 mb-1">
                    <!-- Ikon Dry Fractionation -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 3v18h18M16 8l-4 4-4-4M16 16l-4 4-4-4" />
                    </svg>

                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">Daily Quality Composite Fractionation
                        </h2>
                        <div class="text-sm text-gray-600 mt-1">
                            <span class="font-medium text-gray-700">Kode Logsheet:</span>
                            <span class="inline-block px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded">
                                F/QCO-003
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('daily-storage-tank-analytical.export.view', ['filter_tanggal' => $tanggal]) }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z" />
                    </svg>
                    View Layout
                </a>
                <a href="{{ route('daily-storage-tank-analytical.export.pdf', ['filter_tanggal' => $tanggal]) }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg shadow transition">
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
            <form method="GET" action="{{ route('daily-quality-composite-fractionation.index') }}"
                class="flex flex-wrap items-end gap-4">
                <div class="w-full sm:w-44">
                    <label for="filter_tanggal" class="block text-sm font-medium text-gray-700">Tanggal</label>
                    <input type="date" id="filter_tanggal" name="filter_tanggal" value="{{ $tanggal }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                </div>

                <div class="w-full sm:w-32">
                    <label for="filter_jam" class="block text-sm font-medium text-gray-700">Time</label>
                    <select id="filter_jam" name="filter_jam"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                        <option value="">Pilih Jam</option>
                        @for ($i = 0; $i < 24; $i++)
                            @php $jam = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00'; @endphp
                            <option value="{{ $jam }}" {{ request('filter_jam') == $jam ? 'selected' : '' }}>
                                {{ $jam }}
                            </option>
                        @endfor
                    </select>
                </div>

                 <div class="w-full sm:w-48">
                    <label for="filter_work_center" class="block text-sm font-medium text-gray-700">Work Center</label>
                    <select id="filter_work_center" name="filter_work_center"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                        <option value="">Pilih Work Center</option>
                        @foreach ($workCenters as $wc)
                            <option value="{{ $wc->work_center }}"
                                {{ request('filter_work_center') == $wc->work_center ? 'selected' : '' }}>
                                {{ $wc->work_center }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold rounded-lg shadow transition">Filter</button>
                    @if (request()->has('filter_tanggal') || request()->has('filter_jam'))
                        <a href="{{ route('report-monitoring-dry-fractionation.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 text-sm font-semibold rounded-lg shadow transition">Reset</a>
                    @endif
                </div>

                
            </form>
        </div>
        {{-- Approval Day --}}
        {{-- <div x-data="{ openRejectModal: false }">
            <div class="flex gap-2 mb-4">
                <form action="{{ route('report-monitoring-dry-fractionation.approve-date') }}" method="POST"> @csrf <input
                        type="hidden" name="posting_date" value="{{ $tanggal }}">
                    <button type="submit"
                        class="px-4 py-2 text-sm font-semibold rounded-lg {{ $canApproveReject ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}"
                        {{ !$canApproveReject ? 'disabled' : '' }}>Approve Hari Ini</button>
                </form>
                <button type="button" @click="openRejectModal = true"
                    class="px-4 py-2 text-sm font-semibold rounded-lg {{ $canApproveReject ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}"
                    {{ !$canApproveReject ? 'disabled' : '' }}>Reject Hari Ini</button>
            </div>
            <div x-show="openRejectModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
                x-cloak>
                <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Reject Laporan</h2>
                    <form action="{{ route('report-monitoring-dry-fractionation.reject-date') }}" method="POST"> @csrf
                        <input type="hidden" name="posting_date" value="{{ $tanggal }}">
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
        </div> --}}

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                <thead class="bg-gray-100 text-gray-700 text-sm">
                    <tr>
                        <th class="px-4 py-2 border-b text-left">No</th>
                        <th class="px-4 py-2 border-b text-left">Report ID</th>
                        <th class="px-4 py-2 border-b text-left">Tanggal</th>
                        <th class="px-4 py-2 border-b text-left">Crystalizer</th>
                        <th class="px-4 py-2 border-b text-left">Prepared Status</th>
                        <th class="px-4 py-2 border-b text-left">Checked Status</th>
                        <th class="px-4 py-2 border-b text-left">Action</th>
                        <th class="px-4 py-2 border-b text-left">Detail</th>

                    </tr>
                </thead>
                <tbody class="text-sm text-gray-700">
                    @forelse ($data as $item)
                        <tr class="hover:bg-gray-50">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->transaction_date }}</td>
                            <td>{{ $item->crystalizer }}</td>

                            <td class="px-2 py-2 border-b text-center">
                                @if ($item->prepared_status == 'Approved')
                                    <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Approved</span>
                                @elseif ($item->prepared_status == 'Rejected')
                                    <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">Rejected</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-600">Pending</span>
                                @endif
                            </td>
                            <td class="px-2 py-2 border-b text-center">
                                @if ($item->checked_status == 'Approved')
                                    <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Approved</span>
                                @elseif ($item->checked_status == 'Rejected')
                                    <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">Rejected</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-600">Pending</span>
                                @endif
                            </td>
                            <td class="px-2 py-2 border-b text-center">
                                <div class="flex justify-center gap-2" x-data="{ showApprove: false, showReject: false }">
                                    @if ((auth()->user()->roles === 'LEAD_QC' || auth()->user()->roles === 'LEAD') && is_null($item->prepared_status))
                                        <button @click="showApprove = true"
                                            class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 shadow">Approve</button>
                                        <button @click="showReject = true"
                                            class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 shadow">Reject</button>
                                    @endif
                                    @if (
                                        (auth()->user()->roles === 'MGR_QC' || auth()->user()->roles === 'MGR' || auth()->user()->roles === 'ADM') &&
                                            is_null($item->checked_status) &&
                                            $item->prepared_status === 'Approved' &&
                                            $item->prepared_status != 'Rejected')
                                        <button @click="showApprove = true"
                                            class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 shadow">Approve</button>
                                        <button @click="showReject = true"
                                            class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 shadow">Reject</button>
                                    @endif
                                    <div x-show="showApprove"
                                        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                                        x-cloak>
                                        <div class="bg-white p-6 rounded-lg shadow-xl">
                                            <h2 class="text-lg font-bold mb-4">Confirm Approval</h2>
                                            <p>Approve ticket #{{ $item->id }}?</p>
                                            <div class="mt-6 flex justify-end gap-2">
                                                <button @click="showApprove = false"
                                                    class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                                                <form method="POST"
                                                    action="{{ route('daily-quality-composite-fractionation.approveReport', $item->id) }}">
                                                    @csrf
                                                    <button type="submit"
                                                        class="px-4 py-2 bg-green-600 text-white rounded">Approve</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div x-show="showReject"
                                        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                                        x-cloak>
                                        <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md">
                                            <h2 class="text-lg font-bold mb-4">Confirm Rejection</h2>
                                            <form method="POST"
                                                action="{{ route('daily-quality-composite-fractionation.rejectReport', $item->id) }}">
                                                @csrf
                                                <label for="remark-{{ $item->id }}" class="block mb-2">Reason for
                                                    rejection:</label>
                                                <textarea id="remark-{{ $item->id }}" name="remark" class="w-full border rounded p-2" rows="3" required></textarea>
                                                <div class="mt-6 flex justify-end gap-2">
                                                    <button type="button" @click="showReject = false"
                                                        class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                                                    <button type="submit"
                                                        class="px-4 py-2 bg-red-600 text-white rounded">Reject</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>


                            <td class="px-2 py-2 border-b text-center">
                                <a href="{{ route('daily-quality-composite-fractionation.show', $item->id) }}"
                                    class="text-blue-600 hover:text-blue-800"><svg xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 512 512" class="w-5 h-5 inline-block">
                                        <path fill="currentColor"
                                            d="M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zM224 160a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zm-8 64l48 0c13.3 0 24 10.7 24 24l0 88 8 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-80 0c-13.3 0-24-10.7-24-24s10.7-24 24-24l24 0 0-64-24 0c-13.3 0-24-10.7-24-24s10.7-24 24-24z" />
                                    </svg></a>
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
        {{-- @if ($reports->hasPages())
            <div class="mt-4">{{ $reports->links() }}</div>
        @endif --}}

        {{-- Footer info --}}
        {{-- <div class="mt-6 text-sm text-gray-500 italic">
            Menampilkan {{ $reports->count() }} data ticket.
        </div> --}}
    </div>
@endsection
