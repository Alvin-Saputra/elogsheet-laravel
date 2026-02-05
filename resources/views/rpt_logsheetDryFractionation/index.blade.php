@extends('layouts.app')

@section('page_title', 'Logsheet Dry Fractionation')

@section('content')
    <div x-data="{
        rejectModalOpen: false,
        rejectModalPerDate: false,
        selectedId: '',
        approveStatus: '',
        selectedDate: '',
    
        openRejectModal(id, approve_status) {
            this.selectedId = id;
            this.approveStatus = approve_status;
            this.rejectModalOpen = true;
        },
    
        openRejectModalPerDate(approve_status, selected_date) {
            this.approveStatus = approve_status;
            this.selectedDate = selected_date;
            this.rejectModalPerDate = true;
        },
    
    }" class="bg-white p-6 rounded shadow-md relative">

        {{-- HEADER SECTION --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-3 mb-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2l4 -4M12 20h8a2 2 0 0 0 2-2V8l-6-6H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h4z" />
                </svg>
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Logsheet Dry Fractionation</h2>
                    <div class="text-sm text-gray-600 mt-1">
                        <span class="font-medium text-gray-700">Report Code:</span>
                        <span
                            class="inline-block px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded">F/RFA-010</span>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('logsheet-monitoring-dry-fractionation.preview', [
                    'start_date' => request('start_date', $startDate),
                    'end_date' => request('end_date', $endDate),
                ]) }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow transition">
                    {{-- SVG Icon --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z" />
                    </svg>
                    View Layout
                </a>
                <a href="{{ route('logsheet-monitoring-dry-fractionation.export', [
                    'start_date' => request('start_date', $startDate),
                    'end_date' => request('end_date', $endDate),
                ]) }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg shadow transition">
                    {{-- SVG Icon --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z" />
                    </svg>
                    Export
                </a>
            </div>

        </div>

        {{-- FILTER SECTION --}}
        <div class="bg-gray-50 p-4 rounded-md shadow-sm mb-6">
            <form method="GET" action="{{ route('logsheet-monitoring-dry-fractionation.index') }}"
                class="flex flex-wrap items-end gap-4">

                {{-- Input Tanggal Awal --}}
                <div class="w-full sm:w-44">
                    <label for="filter_tanggal_awal" class="block text-sm font-medium text-gray-700">Tanggal Awal</label>
                    <input type="date" id="filter_tanggal_awal" name="start_date" value="{{ $startDate }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                </div>


                <div class="w-full sm:w-44">
                    <label for="filter_tanggal_akhir" class="block text-sm font-medium text-gray-700">Tanggal Akhir</label>
                    <input type="date" id="filter_tanggal_akhir" name="end_date" value="{{ $endDate }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                </div>

                {{-- Tombol Filter & Reset --}}
                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold rounded-lg shadow transition">
                        Filter
                    </button>
                    @if (request()->has('filter_tanggal_awal') != \Carbon\Carbon::today()->format('Y-m-d') ||
                            request('filter_tanggal_akhir') != \Carbon\Carbon::today()->format('Y-m-d'))
                        <a href="{{ route('logsheet-monitoring-dry-fractionation.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 text-sm font-semibold rounded-lg shadow transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>





        {{-- DATA TABLE SECTION --}}
        <div class="space-y-6">
            {{-- Loop Grouping: $groupedData key-nya adalah TANGGAL --}}
            @forelse ($groupedData as $dateKey => $items)

                <div class="border rounded-lg shadow-sm overflow-hidden bg-white">

                    {{-- HEADER PER TANGGAL --}}
                    <div class="bg-gray-100 px-4 py-3 border-b flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="font-bold text-gray-700">
                                {{ \Carbon\Carbon::parse($dateKey)->format('d F Y') }}
                            </span>
                            <span class="text-xs bg-gray-200 text-gray-600 px-2 py-1 rounded-full">
                                {{ $items->count() }} Data
                            </span>

                        </div>

                        @php
                            // Ambil status calculated dari controller
                            $dailyStatus = $approvalStatusPerDate[$dateKey] ?? [
                                'canApproveReject' => false,
                                'statusMessage' => '',
                            ];
                            $canApprove = $dailyStatus['canApproveReject'];
                        @endphp


                        <div class="flex flex-wrap items-center gap-2">

                            {{-- Pesan Status (Opsional, agar user tahu kenapa tombol mati) --}}
                            @if (!$canApprove && !empty($dailyStatus['statusMessage']))
                                <span class="text-xs text-red-500 italic hidden md:inline-block mr-2">
                                    *{{ $dailyStatus['statusMessage'] }}
                                </span>
                            @endif

                            {{-- TOMBOL APPROVE ALL --}}
                            <form action="{{ route('logsheet-monitoring-dry-fractionation.approvalPerDate') }}"
                                method="POST">
                                @csrf
                                <input type="hidden" name="transaction_date" value="{{ $dateKey }}">
                                <input type="hidden" name="approve_status" value="Approved">

                                <button type="submit" @if (!$canApprove) disabled @endif
                                    class="px-4 py-2 rounded text-xs font-bold shadow flex items-center transition
                {{ $canApprove
                    ? 'bg-green-600 hover:bg-green-700 text-white cursor-pointer'
                    : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}">

                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    Approve All
                                </button>
                            </form>

                            {{-- TOMBOL REJECT ALL --}}
                            {{-- Menggunakan Modal yang sudah ada --}}
                            <button type="button" @if (!$canApprove) disabled @endif
                                @click="openRejectModalPerDate('Rejected', '{{ $dateKey }}')"
                                class="px-4 py-2 rounded text-xs font-bold shadow flex items-center transition
            {{ $canApprove
                ? 'bg-red-600 hover:bg-red-700 text-white cursor-pointer'
                : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}">

                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Reject All
                            </button>

                        </div>

                    </div>



                    {{-- TABEL DATA --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-gray-700">
                            <thead class="bg-gray-50 text-gray-600 text-xs font-semibold uppercase tracking-wider">
                                <tr>

                                    <th class="px-4 py-3 border-b text-left">Plant</th>
                                    <th class="px-4 py-3 border-b text-left">Crystallizer</th>
                                    <th class="px-4 py-3 border-b text-center">Filling Time</th>
                                    <th class="px-4 py-3 border-b text-center">Cooling Start</th>
                                    <th class="px-4 py-3 border-b text-right">Feed Oil IV</th>
                                    <th class="px-4 py-3 border-b text-center">Details</th>
                                    <th class="px-4 py-3 border-b text-center">Leader Approve</th>
                                    <th class="px-4 py-3 border-b text-center">Manager Approve</th>
                                    <th class="px-4 py-3 border-b text-left w-20">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($items as $row)
                                    <tr class="hover:bg-blue-50 transition">
                                        {{-- ACTION COLUMN --}}


                                        <td class="px-4 py-3 font-medium">{{ $row->plant }}</td>
                                        <td class="px-4 py-3">{{ $row->crystallizer }}</td>

                                        {{-- Jam Filling (Gabung Start & End) --}}
                                        <td class="px-4 py-3 text-center">
                                            {{ \Carbon\Carbon::parse($row->filling_start_time)->format('H:i') }} -
                                            {{ \Carbon\Carbon::parse($row->filling_end_time)->format('H:i') }}
                                        </td>

                                        <td class="px-4 py-3 text-center">
                                            {{ \Carbon\Carbon::parse($row->cooling_start_time)->format('H:i') }}
                                            <span class="text-xs text-gray-500 block">
                                                ({{ $row->cooling_start_temp }}°C)
                                            </span>
                                        </td>

                                        <td class="px-4 py-3 text-right font-mono">
                                            {{ $row->feed_oil_iv }}
                                        </td>

                                        {{-- Jumlah Detail (Filtration) --}}
                                        <td class="px-4 py-3 text-center">
                                            <span
                                                class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-xs font-bold border border-gray-300">
                                                {{ $row->details->count() }} Cycle
                                            </span>
                                        </td>

                                        {{-- Status Column --}}
                                        <td class="px-4 py-3 text-center">
                                            @if ($row->prepared_status == 'Approved')
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Approved
                                                </span>
                                            @elseif($row->prepared_status == 'Rejected')
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Rejected
                                                </span>
                                            @else
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    Pending
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3 text-center">
                                            @if ($row->checked_status == 'Approved')
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Approved
                                                </span>
                                            @elseif($row->checked_status == 'Rejected')
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Rejected
                                                </span>
                                            @else
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    Pending
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="flex gap-2">
                                                {{-- 1. BUTTON LEADER (Muncul jika Leader Belum Approve) --}}
                                                @if ((auth()->user()->roles === 'LEAD_PROD' || auth()->user()->roles === 'LEAD') && is_null($row->prepared_status))
                                                    <form
                                                        action="{{ route('logsheet-monitoring-dry-fractionation.approvalPerCrystallizer') }}"
                                                        method="POST">
                                                        @csrf

                                                        <input type="hidden" name="approve_status" value="Approved">
                                                        <input type="hidden" name="id"
                                                            value="{{ $row->id }}">

                                                        <button type="submit"
                                                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-xs font-bold shadow flex items-center">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1"
                                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M5 13l4 4L19 7" />
                                                            </svg>
                                                            Approve
                                                        </button>
                                                    </form>
                                                    {{-- Reject Button (Simple) --}}
                                                    <button type="button"
                                                        @click="openRejectModal('{{ $row->id }}', '{{ 'Rejected' }}')"
                                                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-xs font-bold shadow">
                                                        Reject
                                                    </button>
                                                @endif

                                                {{-- 2. BUTTON MANAGER (Muncul jika Leader Approved & Manager Belum) --}}
                                                @if (
                                                    (auth()->user()->roles === 'MGR_PROD' || auth()->user()->roles === 'MGR') &&
                                                        $row->prepared_status == 'Approved' &&
                                                        is_null($row->checked_status))
                                                    <form
                                                        action="{{ route('logsheet-monitoring-dry-fractionation.approvalPerCrystallizer') }}"
                                                        method="POST">
                                                        @csrf
                                                        <input type="hidden" name="approve_status" value="Approved">
                                                        <input type="hidden" name="id"
                                                            value="{{ $row->id }}">
                                                        <button type="submit"
                                                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-xs font-bold shadow flex items-center">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1"
                                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                            Approve (MGR)
                                                        </button>


                                                    </form>

                                                    <button type="button"
                                                        @click="openRejectModal('{{ $row->id }}', '{{ 'Rejected' }}')"
                                                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-xs font-bold shadow">
                                                        Reject
                                                    </button>
                                                @endif
                                                  <a href="{{ route('logsheet-monitoring-dry-fractionation.show', $row->id) }}"
                                                    class="text-blue-600 hover:text-blue-900 flex items-center gap-1 font-bold text-xs border border-blue-200 bg-blue-50 px-2 py-1 rounded hover:bg-blue-100 transition">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z" />
                                                    </svg>
                                                    Detail
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>


                </div>
                <br>

            @empty
                {{-- TAMPILAN JIKA KOSONG --}}
                <div
                    class="flex flex-col items-center justify-center p-12 bg-white rounded shadow-sm border border-dashed border-gray-300 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-4" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900">Belum ada data logsheet</h3>
                    <p class="text-gray-500 mt-1">Silakan pilih rentang tanggal lain atau input data baru.</p>
                </div>
            @endforelse
        </div>



        {{-- MODAL REJECT SECTION --}}
        <div x-show="rejectModalOpen" style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm"
            x-transition.opacity>

            <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 overflow-hidden"
                @click.away="rejectModalOpen = false">

                <div class="bg-red-600 px-4 py-3 flex justify-between items-center">
                    <h3 class="text-white font-bold text-lg">Konfirmasi Reject Shift</h3>
                    <button @click="rejectModalOpen = false" class="text-white hover:text-gray-200">
                        <span class="text-2xl">&times;</span>
                    </button>
                </div>

                <form action="{{ route('logsheet-monitoring-dry-fractionation.approvalPerCrystallizer') }}"
                    method="POST" class="p-6">
                    @csrf

                    {{-- Hidden Inputs --}}
                    <input type="hidden" name="id" :value="selectedId">
                    <input type="hidden" name="approve_status" :value="approveStatus">

                    <div class="mb-4">
                        <p class="text-gray-700 text-sm mb-2">
                            Anda akan me-reject laporan produksi untuk: <br>
                            {{-- <strong>Work Center:</strong> <span x-text="selectedWorkCenter"></span> <br> --}}
                            {{-- <strong>Shift:</strong> <span x-text="selectedShift"></span> --}}
                        </p>

                        <label for="remark" class="block text-sm font-medium text-gray-700 mb-1">
                            Alasan Reject (Remark):
                        </label>
                        <textarea name="remark" id="remark" rows="3" required
                            class="w-full rounded border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm p-2 border"
                            placeholder="Tulis alasan penolakan di sini..."></textarea>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" @click="rejectModalOpen = false"
                            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded text-sm font-semibold">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded text-sm font-semibold shadow">
                            Simpan & Reject
                        </button>
                    </div>
                </form>
            </div>
        </div>



        <div x-show="rejectModalPerDate" style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm"
            x-transition.opacity>

            <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 overflow-hidden"
                @click.away="rejectModalPerDate = false">

                <div class="bg-red-600 px-4 py-3 flex justify-between items-center">
                    <h3 class="text-white font-bold text-lg">Konfirmasi Reject Shift</h3>
                    <button @click="rejectModalPerDate = false" class="text-white hover:text-gray-200">
                        <span class="text-2xl">&times;</span>
                    </button>
                </div>

                <form action="{{ route('logsheet-monitoring-dry-fractionation.approvalPerDate') }}" method="POST"
                    class="p-6">
                    @csrf

                    {{-- Hidden Inputs --}}
                    <input type="hidden" name="transaction_date" :value="selectedDate">
                    <input type="hidden" name="approve_status" :value="approveStatus">

                    <div class="mb-4">
                        <p class="text-gray-700 text-sm mb-2">
                            Anda akan me-reject laporan produksi untuk: <br>
                            {{-- <strong>Work Center:</strong> <span x-text="selectedWorkCenter"></span> <br> --}}
                            {{-- <strong>Shift:</strong> <span x-text="selectedShift"></span> --}}
                        </p>

                        <label for="remark" class="block text-sm font-medium text-gray-700 mb-1">
                            Alasan Reject (Remark):
                        </label>
                        <textarea name="remark" id="remark" rows="3" required
                            class="w-full rounded border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm p-2 border"
                            placeholder="Tulis alasan penolakan di sini..."></textarea>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" @click="rejectModalOpen = false"
                            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded text-sm font-semibold">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded text-sm font-semibold shadow">
                            Simpan & Reject
                        </button>
                    </div>
                </form>
            </div>
        </div>




        {{-- Tutup Div x-data utama --}}
    </div>


@endsection
