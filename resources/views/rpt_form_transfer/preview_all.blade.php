@extends('layouts.app')

@section('page_title', 'Form Transfer - All Reports Preview')

@section('content')
    <div class="bg-white p-6 rounded shadow-md text-sm relative max-w-6xl mx-auto">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M7 7h10M7 12h10M7 17h6M3 5a2 2 0 012-2h12a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5z" />
                </svg>
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Form Transfer - All Reports</h2>
                    <div class="text-sm text-gray-600 mt-1">
                        <span class="font-medium text-gray-700">Date:</span>
                        <span class="inline-block px-2 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded">
                            {{ \Carbon\Carbon::parse($tanggal)->format('d M Y') }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('report.form-transfer.index', ['filter_tanggal' => $tanggal]) }}"
                    class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-semibold rounded-lg shadow transition">
                    ← Back to List
                </a>
                <a href="{{ route('report.form-transfer.export.pdf', ['filter_tanggal' => $tanggal]) }}"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg shadow transition"
                    target="_blank">
                    Download PDF
                </a>
            </div>
        </div>

        @forelse ($transfers as $transfer)
            <div class="mb-8 border border-gray-300 rounded-lg p-4 {{ !$loop->last ? 'page-break-after' : '' }}">
                {{-- Transfer Header --}}
                <table class="w-full mb-4">
                    <tbody>
                        <tr class="align-top">
                            <td class="w-1/5 text-center">
                                <img src="{{ asset('images/KPN Corp.jpg') }}" alt="Logo" class="h-12 mx-auto mb-1">
                                <span class="font-bold">Bekasi</span>
                            </td>
                            <td class="w-3/5 text-center pt-2">
                                <h3 class="text-xl font-bold uppercase">Form Transfer</h3>
                                <div class="mt-1">PT. PRISCOLIN</div>
                            </td>
                            <td class="w-1/5">
                                <div class="text-xs leading-tight text-left border border-gray-400 p-2 rounded-md">
                                    <div><strong>Form No.</strong> : {{ $transfer->form_no ?? 'F/QCO-018' }}</div>
                                    <div><strong>Date Issued</strong> :
                                        {{ $transfer->date_issued ? \Carbon\Carbon::parse($transfer->date_issued)->format('ymd') : '' }}
                                    </div>
                                    <div><strong>Revision</strong> : {{ $transfer->revision_no ?? '00' }}</div>
                                    <div><strong>Rev. Date</strong> :
                                        {{ $transfer->revision_date ? \Carbon\Carbon::parse($transfer->revision_date)->format('ymd') : '' }}
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="border border-gray-400 p-2 rounded-md mb-4">
                    <table class="w-full">
                        <tbody>
                            <tr class="align-top">
                                <td class="w-1/2 pr-2">
                                    <div class="flex mb-1">
                                        <strong class="w-32">From Dept</strong>:
                                        {{ $transfer->from_dept ?? '-' }}
                                    </div>
                                    <div class="flex mb-1">
                                        <strong class="w-32">To Dept</strong>:
                                        {{ $transfer->to_dept ?? '-' }}
                                    </div>
                                </td>
                                <td class="w-1/2">
                                    <div class="flex mb-1">
                                        <strong class="w-32">Transaction Date</strong>:
                                        {{ $transfer->transaction_date ? \Carbon\Carbon::parse($transfer->transaction_date)->format('d-m-Y') : '-' }}
                                    </div>
                                    <div class="flex mb-1">
                                        <strong class="w-32">Company</strong>: {{ $transfer->company ?? '-' }}
                                    </div>
                                    <div class="flex mb-1">
                                        <strong class="w-32">Plant</strong>: {{ $transfer->plant ?? '-' }}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="overflow-x-auto">
                    @include('rpt_form_transfer._table', ['details' => $transfer->details])
                </div>

                <div class="grid grid-cols-2 gap-12 mt-6 text-xs text-center">
                    <div>
                        <strong>Prepared By</strong><br>
                        <small class="text-gray-500">Lead / Lead QC</small><br><br>
                        {{ $transfer->prepared_by ?? '________________' }}<br>
                        <small>{{ $transfer->prepared_date ? \Carbon\Carbon::parse($transfer->prepared_date)->format('d M Y H:i') : '' }}</small>
                    </div>
                    <div>
                        <strong>Approved By</strong><br>
                        <small class="text-gray-500">Manager / Admin</small><br><br>
                        {{ $transfer->approved_by ?? '________________' }}<br>
                        <small>{{ $transfer->approved_date ? \Carbon\Carbon::parse($transfer->approved_date)->format('d M Y H:i') : '' }}</small>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-gray-500 py-12">
                <p>No Form Transfer records found for {{ \Carbon\Carbon::parse($tanggal)->format('d M Y') }}</p>
            </div>
        @endforelse

        <div class="mt-6 text-center text-xs text-gray-600 italic">
            Dokumen ini telah disetujui secara elektronik melalui sistem [E-Logsheet], sehingga tidak memerlukan tanda
            tangan asli.
        </div>
    </div>
@endsection
