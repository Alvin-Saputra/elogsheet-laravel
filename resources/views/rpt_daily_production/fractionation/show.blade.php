@extends('layouts.app')

@section('page_title', 'Detail Daily Production Fractionation Report')

@section('content')
    <div class="max-w-6xl mx-auto">

        {{-- Header & Back Button --}}
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-3">
                <div class="bg-yellow-100 p-2 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-800">Detail Laporan #{{ $report->id }}</h3>
                    <p class="text-sm text-gray-500">Daily Production Fractionation</p>
                </div>
            </div>

            <a href="{{ route('report-daily-production.fractionation.index') }}"
                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                Kembali
            </a>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-md space-y-8">

            {{-- SECTION: INFORMASI UMUM --}}
            <div>
                <h4 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Informasi Umum</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <span class="block text-xs font-bold text-gray-500 uppercase">Tanggal Transaksi</span>
                        <span
                            class="block text-gray-800 font-medium">{{ optional($report->transaction_date)->format('d M Y') }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-500 uppercase">Tanggal Posting</span>
                        <span
                            class="block text-gray-800 font-medium">{{ optional($report->posting_date)->format('d M Y') }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-500 uppercase">Shift</span>
                        <span class="block text-gray-800 font-medium">{{ $report->shift }}</span>
                    </div>

                     <div>
                        <span class="block text-xs font-bold text-gray-500 uppercase">No</span>
                        <span class="block text-gray-800 font-medium">{{ $report->no }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-500 uppercase">Work Center</span>
                        <span class="block text-gray-800 font-medium">{{ $report->work_center }}</span>
                    </div>
                </div>
            </div>

            {{-- SECTION: RAW MATERIAL --}}
            <div>
                <h4 class="text-lg font-bold text-blue-700 border-b pb-2 mb-4 bg-blue-50 p-2 rounded">Raw Material Flow</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="col-span-1 md:col-span-2">
                        <span class="block text-xs font-bold text-gray-500 uppercase">Oil Type RM</span>
                        {{-- Menggunakan alias name dari query controller --}}
                        <span
                            class="block text-gray-800 font-medium">{{ $report->oil_type_rm_name ?? $report->oil_type_rm }}</span>
                    </div>
                   
                    <div>
                        <span class="block text-xs font-bold text-gray-500 uppercase">RM CR</span>
                        <span class="block text-gray-800">{{ $report->oil_type_rm_cr ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-500 uppercase">Tank Asal</span>
                        <span class="block text-gray-800">{{ $report->oil_type_rm_from_tank }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-500 uppercase">Jam (Awal - Akhir)</span>
                        <span class="block text-gray-800">
                            {{ $report->oil_type_rm_awal_jam }} - {{ $report->oil_type_rm_akhir_jam }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-500 uppercase">Flowmeter (Awal - Akhir)</span>
                        <span class="block text-gray-800">
                            {{ number_format($report->oil_type_rm_awal_flowmeter) }} -
                            {{ number_format($report->oil_type_rm_akhir_flowmeter) }}
                        </span>
                    </div>
                    <div class="bg-blue-100 p-2 rounded">
                        <span class="block text-xs font-bold text-blue-800 uppercase">Total Pemakaian (KG)</span>
                        <span
                            class="block text-blue-900 font-bold text-lg">{{ number_format($report->oil_type_rm_total) }}</span>
                    </div>
                </div>
            </div>

            {{-- SECTION: FGS --}}
            <div>
                <h4 class="text-lg font-bold text-green-700 border-b pb-2 mb-4 bg-green-50 p-2 rounded">Finished Goods
                    (Stearin)</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="col-span-1 md:col-span-2">
                        <span class="block text-xs font-bold text-gray-500 uppercase">Oil Type FGS</span>
                        <span
                            class="block text-gray-800 font-medium">{{ $report->oil_type_fgs_name ?? $report->oil_type_fgs }}</span>
                    </div>
                   
                    <div>
                        <span class="block text-xs font-bold text-gray-500 uppercase">FGS CR</span>
                        <span class="block text-gray-800">{{ $report->oil_type_fgs_cr ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-500 uppercase">Tank Tujuan</span>
                        <span class="block text-gray-800">{{ $report->oil_type_fgs_to_tank }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-500 uppercase">Jam (Awal - Akhir)</span>
                        <span class="block text-gray-800">
                            {{ $report->oil_type_fgs_awal_jam }} - {{ $report->oil_type_fgs_akhir_jam }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-500 uppercase">Flowmeter (Awal - Akhir)</span>
                        <span class="block text-gray-800">
                            {{ number_format($report->oil_type_fgs_awal_flowmeter) }} -
                            {{ number_format($report->oil_type_fgs_akhir_flowmeter) }}
                        </span>
                    </div>
                    <div class="bg-green-100 p-2 rounded">
                        <span class="block text-xs font-bold text-green-800 uppercase">Total Produksi (KG)</span>
                        <span
                            class="block text-green-900 font-bold text-lg">{{ number_format($report->oil_type_fgs_total) }}</span>
                    </div>
                </div>
            </div>

            {{-- SECTION: FGH --}}
            <div>
                <h4 class="text-lg font-bold text-purple-700 border-b pb-2 mb-4 bg-purple-50 p-2 rounded">Finished Goods
                    (Olein/FGH)</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="col-span-1 md:col-span-2">
                        <span class="block text-xs font-bold text-gray-500 uppercase">Oil Type FGH</span>
                        <span
                            class="block text-gray-800 font-medium">{{ $report->oil_type_fgh_name ?? $report->oil_type_fgh }}</span>
                    </div>
                   
                    <div>
                        <span class="block text-xs font-bold text-gray-500 uppercase">Tank Tujuan</span>
                        <span class="block text-gray-800">{{ $report->oil_type_fgh_to_tank }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-500 uppercase">Jam (Awal - Akhir)</span>
                        <span class="block text-gray-800">
                            {{ $report->oil_type_fgh_awal_jam }} - {{ $report->oil_type_fgh_akhir_jam }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-500 uppercase">Flowmeter (Awal - Akhir)</span>
                        <span class="block text-gray-800">
                            {{ number_format($report->oil_type_fgh_awal_flowmeter) }} -
                            {{ number_format($report->oil_type_fgh_akhir_flowmeter) }}
                        </span>
                    </div>
                    <div class="bg-purple-100 p-2 rounded">
                        <span class="block text-xs font-bold text-purple-800 uppercase">Total Produksi (KG)</span>
                        <span
                            class="block text-purple-900 font-bold text-lg">{{ number_format($report->oil_type_fgh_total) }}</span>
                    </div>
                </div>
            </div>

            {{-- SECTION: UTILITIES --}}
            <div>
                <h4 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Utilities Usage</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div>
                        <span class="block text-xs font-bold text-gray-500 uppercase">Item</span>
                        <span class="block text-gray-800">{{ $report->uu_item ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-500 uppercase">Total Listrik</span>
                        <span class="block text-gray-800">{{ number_format($report->uu_listrik ?? 0) }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-500 uppercase">Total Air</span>
                        <span class="block text-gray-800">{{ number_format($report->uu_air ?? 0) }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-500 uppercase">Yield</span>
                        <span class="block text-gray-800 font-bold">{{ $report->uu_yield_percent }}%</span>
                    </div>
                </div>
            </div>

            {{-- SECTION: VALIDATION & APPROVAL --}}
            <div>
                <h4 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Approval Status</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-4 rounded-lg">

                    {{-- LEADER --}}
                    <div
                        class="border-l-4 {{ $report->prepared_status === 'Approved' ? 'border-green-500' : ($report->prepared_status === 'Rejected' ? 'border-red-500' : 'border-gray-300') }} pl-4">
                        <span class="block text-xs font-bold text-gray-500 uppercase mb-1">Shift Leader</span>
                        <div class="text-sm">
                            <span class="font-bold">Status:</span>
                            <span
                                class="{{ $report->prepared_status === 'Approved' ? 'text-green-600' : ($report->prepared_status === 'Rejected' ? 'text-red-600' : 'text-gray-500') }} font-bold">
                                {{ $report->prepared_status ?? 'Pending' }}
                            </span>
                        </div>
                        <div class="text-sm"><span class="font-bold">By:</span> {{ $report->prepared_by ?? '-' }}</div>
                        <div class="text-sm"><span class="font-bold">Date:</span>
                            {{ optional($report->prepared_date)->format('d M Y H:i') ?? '-' }}</div>
                        @if ($report->prepared_status_remarks)
                            <div class="mt-2 text-xs text-red-600 bg-red-50 p-1 rounded">
                                <strong>Remark:</strong> {{ $report->prepared_status_remarks }}
                            </div>
                        @endif
                    </div>

                    {{-- MANAGER --}}
                    <div
                        class="border-l-4 {{ $report->checked_status === 'Approved' ? 'border-green-500' : ($report->checked_status === 'Rejected' ? 'border-red-500' : 'border-gray-300') }} pl-4">
                        <span class="block text-xs font-bold text-gray-500 uppercase mb-1">Production Manager</span>
                        <div class="text-sm">
                            <span class="font-bold">Status:</span>
                            <span
                                class="{{ $report->checked_status === 'Approved' ? 'text-green-600' : ($report->checked_status === 'Rejected' ? 'text-red-600' : 'text-gray-500') }} font-bold">
                                {{ $report->checked_status ?? 'Pending' }}
                            </span>
                        </div>
                        <div class="text-sm"><span class="font-bold">By:</span> {{ $report->checked_by ?? '-' }}</div>
                        <div class="text-sm"><span class="font-bold">Date:</span>
                            {{ optional($report->checked_date)->format('d M Y H:i') ?? '-' }}</div>
                        @if ($report->checked_status_remarks)
                            <div class="mt-2 text-xs text-red-600 bg-red-50 p-1 rounded">
                                <strong>Remark:</strong> {{ $report->checked_status_remarks }}
                            </div>
                        @endif
                    </div>

                </div>
            </div>

            {{-- REMARKS GENERAL --}}
            @if ($report->remarks)
                <div>
                    <h4 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">General Remarks</h4>
                    <div class="bg-yellow-50 p-4 rounded text-gray-700">
                        {{ $report->remarks }}
                    </div>
                </div>
            @endif

        </div>
    </div>
@endsection
