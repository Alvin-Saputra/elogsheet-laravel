@extends('layouts.app')

@section('page_title', 'Report Daily Production Fractionation')

@section('content')
    <div x-data="{
        rejectModalOpen: false,
        rejectModalOpenPerDate: false,
        selectedShift: '',
        selectedDate: '{{ $tanggal }}',
        selectedWorkCenter: '',
        approveStatus: '',
    
        openRejectModalPerShift(shift, wc, approve_status) {
            this.selectedShift = shift;
            this.selectedWorkCenter = wc;
            this.approveStatus = approve_status;
            this.rejectModalOpen = true;
        },
    
        openRejectModalPerDate(approve_status) {
            this.approveStatus = approve_status;
            this.rejectModalOpenPerDate = true;
        }
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
                    <h2 class="text-lg font-semibold text-gray-800">Daily Production Fractionation Section</h2>
                    <div class="text-sm text-gray-600 mt-1">
                        <span class="font-medium text-gray-700">Report Code:</span>
                        <span
                            class="inline-block px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded">F/RFA-004
                            (B)</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                {{-- Export Excel --}}
                <a href="{{ route('report-daily-production.fractionation.export.excel', ['filter_tanggal' => $tanggal]) }}"
                    target="_blank"
                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg shadow transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24">
                        <path d="M3 10h18M3 6h18M3 14h18M3 18h18" />
                    </svg>Export Excel
                </a>
                {{-- View Layout --}}
                <a href="{{ route('report-daily-production.fractionation.export.view', ['filter_tanggal' => $tanggal, 'filter_work_center' => request('filter_work_center')]) }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z" />
                    </svg>View Layout
                </a>
                {{-- Download PDF --}}
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

        {{-- FILTER SECTION --}}
        <div class="bg-gray-50 p-4 rounded-md shadow-sm mb-6">
            <form method="GET" action="{{ route('report-daily-production.fractionation.index') }}"
                class="flex flex-wrap items-end gap-4">

                {{-- Input Tanggal --}}
                <div class="w-full sm:w-44">
                    <label for="filter_tanggal" class="block text-sm font-medium text-gray-700">Date</label>
                    <input type="date" id="filter_tanggal" name="filter_tanggal" value="{{ $tanggal }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                </div>

                {{-- Input Work Center --}}
                <div class="w-full sm:w-48">
                    <label for="filter_work_center" class="block text-sm font-medium text-gray-700">Work Center</label>
                    <select id="filter_work_center" name="filter_work_center"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                        <option value="">Semua Work Center</option>
                        @foreach ($refineryMachines as $wc)
                            <option value="{{ $wc->work_center }}"
                                {{ request('filter_work_center') == $wc->work_center ? 'selected' : '' }}>
                                {{ $wc->work_center }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tombol Filter & Reset --}}
                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold rounded-lg shadow transition">
                        Filter
                    </button>
                    @if (request()->has('filter_work_center') || request('filter_tanggal') != \Carbon\Carbon::today()->format('Y-m-d'))
                        <a href="{{ route('report-daily-production.fractionation.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 text-sm font-semibold rounded-lg shadow transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- APPROVE / REJECT PER DATE --}}
        <div class="bg-gray-50 p-4 rounded-md shadow-sm mb-6">
            <div class="flex flex-wrap items-center gap-3">

                {{-- APPROVE PER DATE --}}
                <form action="{{ route('report-daily-production.fractionation.approvalPerDate') }}" method="POST">
                    @csrf
                    <input type="hidden" name="transaction_date" value="{{ $tanggal }}">
                    <input type="hidden" name="approve_status" value="Approved">

                    <button type="submit"
                        class="px-4 py-2 text-sm font-semibold rounded-lg
                {{ $approvalStatus['canApproveReject']
                    ? 'bg-green-600 hover:bg-green-700 text-white'
                    : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}"
                        {{ !$approvalStatus['canApproveReject'] ? 'disabled' : '' }}>
                        Approve Semua Shift Hari Ini
                    </button>
                </form>

                {{-- REJECT PER DATE --}}
                <button type="button" @click="openRejectModalPerDate('{{ 'Rejected' }}')"
                    class="px-4 py-2 text-sm font-semibold rounded-lg
            {{ $approvalStatus['canApproveReject']
                ? 'bg-red-600 hover:bg-red-700 text-white'
                : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}"
                    {{ !$approvalStatus['canApproveReject'] ? 'disabled' : '' }}>
                    Reject Semua Shift Hari Ini
                </button>

            </div>

            {{-- STATUS MESSAGE --}}
            <p class="text-sm text-gray-600 mt-4">
                {{ $approvalStatus['statusMessage'] }}
            </p>
        </div>


        {{-- DATA TABLE SECTION --}}
        <div class="overflow-x-auto">
            {{-- Loop Grouping Work Center --}}
            @forelse ($groupedReports as $workCenter => $shifts)

                <div class="mb-8 border rounded-lg shadow-sm overflow-hidden">

                    {{-- HEADER: WORK CENTER --}}
                    <div class="bg-gray-500 text-white p-3 flex justify-between items-center">
                        <h3 class="font-bold text-lg flex items-center gap-2">
                            {{ $workCenter }}
                        </h3>
                        <span class="text-xs bg-gray-700 px-2 py-1 rounded">{{ $tanggal }}</span>
                    </div>

                    {{-- Loop Grouping Shift --}}
                    @foreach ($shifts as $shift => $items)
                        @php
                            $firstItem = $items->first();
                            $isLeaderApproved = $firstItem->prepared_status === 'Approved';
                            $isLeaderRejected = $firstItem->prepared_status === 'Rejected';
                            $isManagerApproved = $firstItem->checked_status === 'Approved';
                            $isManagerRejected = $firstItem->checked_status === 'Rejected';

                            // Tentukan warna background header shift
                            $headerClass = 'bg-gray-50 border-l-4 border-gray-300'; // Default Pending
                            if ($isManagerApproved) {
                                $headerClass = 'bg-green-50 border-l-4 border-green-500';
                            } elseif ($isLeaderApproved) {
                                $headerClass = 'bg-yellow-50 border-l-4 border-yellow-500';
                            } elseif ($isLeaderRejected || $isManagerRejected) {
                                $headerClass = 'bg-red-50 border-l-4 border-red-500';
                            }
                        @endphp
                        <div class="{{ $headerClass }}">
                            {{-- HEADER: SHIFT --}}
                            <div class="mb-2">
                                <h4 class="font-bold text-gray-800 text-md">SHIFT {{ $shift }}</h4>
                                <span class="text-xs text-gray-500">Total Data: {{ $items->count() }}</span>
                                <span class="border-l pl-3">
                                    Leader:
                                    @if ($isLeaderApproved)
                                        <span class="text-green-600 font-bold">APPROVED</span>
                                    @elseif($isLeaderRejected)
                                        <span class="text-red-600 font-bold">REJECTED</span>
                                    @else
                                        <span class="text-gray-500 italic">Pending</span>
                                    @endif
                                </span>
                                <span class="border-l pl-3">
                                    Manager:
                                    @if ($isManagerApproved)
                                        <span class="text-green-600 font-bold">APPROVED</span>
                                    @elseif($isManagerRejected)
                                        <span class="text-red-600 font-bold">REJECTED</span>
                                    @else
                                        <span class="text-gray-500 italic">Pending</span>
                                    @endif
                                </span>
                            </div>


                            {{-- ACTION BUTTONS --}}
                            <div class="flex gap-2">
                                {{-- 1. BUTTON LEADER (Muncul jika Leader Belum Approve) --}}
                                @if ((auth()->user()->roles === 'LEAD_PROD' || auth()->user()->roles === 'LEAD') && is_null($firstItem->prepared_status))
                                    <form action="{{ route('report-daily-production.fractionation.approvalPerShift') }}"
                                        method="POST">
                                        @csrf
                                        <input type="hidden" name="posting_date" value="{{ $tanggal }}">
                                        <input type="hidden" name="shift" value="{{ $shift }}">
                                        <input type="hidden" name="work_center" value="{{ $workCenter }}">
                                        <input type="hidden" name="approve_status" value="Approved">

                                        <button type="submit"
                                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-xs font-bold shadow flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                            Approve Shift
                                        </button>
                                    </form>
                                    {{-- Reject Button (Simple) --}}
                                    <button type="button"
                                        @click="openRejectModalPerShift('{{ $shift }}', '{{ $workCenter }}', '{{ 'Rejected' }}')"
                                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-xs font-bold shadow">
                                        Reject
                                    </button>
                                @endif

                                {{-- 2. BUTTON MANAGER (Muncul jika Leader Approved & Manager Belum) --}}
                                @if (
                                    (auth()->user()->roles === 'MGR_PROD' || auth()->user()->roles === 'MGR') &&
                                        $isLeaderApproved &&
                                        is_null($firstItem->checked_status))
                                    <form action="{{ route('report-daily-production.fractionation.approvalPerShift') }}"
                                        method="POST">
                                        @csrf
                                        <input type="hidden" name="posting_date" value="{{ $tanggal }}">
                                        <input type="hidden" name="shift" value="{{ $shift }}">
                                        <input type="hidden" name="work_center" value="{{ $workCenter }}">
                                        <input type="hidden" name="approve_status" value="Approved">
                                        <button type="submit"
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-xs font-bold shadow flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Approve Shift (MGR)
                                        </button>


                                    </form>
                                    <button type="button"
                                        @click="openRejectModalPerShift('{{ $shift }}', '{{ $workCenter }}', '{{ 'Rejected' }}')"
                                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-xs font-bold shadow">
                                        Reject
                                    </button>
                                @endif
                            </div>
                        </div>

                        {{-- TABEL DATA --}}
                        <div class="overflow-x-auto bg-white rounded border border-gray-200">
                            <table class="min-w-full text-sm text-gray-700">
                                <thead class="bg-gray-100 text-gray-700 text-xs font-semibold uppercase">
                                    <tr>
                                        <th class="px-3 py-2 border-b text-left">Action</th> {{-- TAMBAHKAN KOLOM INI --}}
                                        <th class="px-3 py-2 border-b text-left">No</th>
                                        <th class="px-3 py-2 border-b text-left">RM Type</th>
                                        <th class="px-3 py-2 border-b text-right">RM (Total)</th>
                                        <th class="px-3 py-2 border-b text-right">FGS (Total)</th>
                                        <th class="px-3 py-2 border-b text-right">FGH (Total)</th>
                                        <th class="px-3 py-2 border-b text-left">Entry By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $report)
                                        <tr class="hover:bg-gray-50 transition border-b last:border-0">
                                            <td class="px-3 py-2 whitespace-nowrap">
                                                <a href="{{ route('report-daily-production.fractionation.show', $report->id) }}"
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
                                            </td>
                                            <td class="px-3 py-2">{{ $report->no ?? '-' }}</td>
                                            <td class="px-3 py-2">{{ $report->oil_type_rm }}</td>
                                            <td class="px-3 py-2 text-right font-mono">
                                                {{ number_format($report->oil_type_rm_total) }}</td>
                                            <td class="px-3 py-2 text-right font-mono">
                                                {{ number_format($report->oil_type_fgs_total) }}</td>
                                            <td class="px-3 py-2 text-right font-mono">
                                                {{ number_format($report->oil_type_fgh_total) }}</td>
                                            <td class="px-3 py-2">{{ $report->entry_by }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                </div>

            @empty
                <div class="bg-white p-8 rounded shadow text-center border border-gray-200">
                    <p class="text-gray-500 text-lg">Tidak ada data untuk tanggal / filter yang dipilih.</p>
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

                <form action="{{ route('report-daily-production.fractionation.approvalPerShift') }}" method="POST"
                    class="p-6">
                    @csrf

                    {{-- Hidden Inputs --}}
                    <input type="hidden" name="posting_date" :value="selectedDate">
                    <input type="hidden" name="shift" :value="selectedShift">
                    <input type="hidden" name="work_center" :value="selectedWorkCenter">

                    {{-- PERBAIKAN: Tutup tag input dengan benar --}}
                    <input type="hidden" name="approve_status" :value="approveStatus">

                    <div class="mb-4">
                        <p class="text-gray-700 text-sm mb-2">
                            Anda akan me-reject laporan produksi untuk: <br>
                            <strong>Work Center:</strong> <span x-text="selectedWorkCenter"></span> <br>
                            <strong>Shift:</strong> <span x-text="selectedShift"></span>
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


        <div x-show="rejectModalOpenPerDate" x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm"
            style="display: none;">

            <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 overflow-hidden"
                @click.away="rejectModalOpenPerDate = false">

                <div class="bg-red-600 px-4 py-3 flex justify-between items-center">
                    <h3 class="text-white font-bold text-lg">Konfirmasi Reject Shift</h3>
                    <button @click="rejectModalOpen = false" class="text-white hover:text-gray-200">
                        <span class="text-2xl">&times;</span>
                    </button>
                </div>

                <form action="{{ route('report-daily-production.fractionation.approvalPerDate') }}" method="POST"
                    class="p-6">
                    @csrf

                    {{-- Hidden Inputs --}}
                    <input type="hidden" name="transaction_date" :value="selectedDate">
                    <input type="hidden" name="approve_status" :value="approveStatus">

                    <div class="mb-4">
                        <p class="text-gray-700 text-sm mb-2">
                            Anda akan me-reject semua laporan produksi <br>

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
