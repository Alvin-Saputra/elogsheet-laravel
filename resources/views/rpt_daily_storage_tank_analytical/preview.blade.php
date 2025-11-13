@extends('layouts.app')

@section('page_title', 'Laporan Daily Storage Tank Analytical')

@section('content')
    <div class="bg-white p-6 rounded shadow-md text-sm relative">
        <div class="absolute top-4 right-6 text-xs leading-tight text-left">
            <div><strong>Form No.</strong> : {{ $formInfoFirst->form_no ?? 'F/RFA-010' }}</div>
            <div><strong>Date Issued</strong> :
                {{ $formInfoFirst ? optional($formInfoFirst->date_issued)->format('ymd') : '210101' }}</div>
            <div><strong>Revision</strong> : {{ $formInfoLast ? sprintf('%02d', $formInfoLast->revision_no) : '01' }}</div>
            <div><strong>Rev. Date</strong> :
                {{ $formInfoLast ? optional($formInfoLast->revision_date)->format('ymd') : '210901' }}</div>
        </div>

        <div class="text-center mb-4">
            <h2 class="text-lg font-bold uppercase">PT.PRISCOLIN</h2>
            <h3 class="text-xl font-bold uppercase">LOGSHEET DAILY STORAGE TANK ANALYTICAL</h3>
            <div class="mt-1">Date: {{ \Carbon\Carbon::parse($tanggal)->format('d-m-Y') }}</div>
        </div>

        @include('rpt_daily_storage_tank_analytical._table', ['rows' => $data])

        <div class="flex justify-center gap-16 mt-10 text-xs text-center">
            <div><strong>Prepared By:</strong><br><br><br>
                {{ $sign->prepared_by ?? '________________' }}<br>(Leader
                Shift)<br><small>{{ !empty($sign->prepared_date) ? \Carbon\Carbon::parse($sign->prepared_date)->format('d M Y H:i') : '' }}</small>
            </div>
            <div><strong>Approved by:</strong><br><br><br>
                {{ $sign->approved_by ?? '________________' }}<br>(SPV)<br><small>{{ !empty($sign->approved_date) ? \Carbon\Carbon::parse($sign->approved_date)->format('d M Y H:i') : '' }}</small>
            </div>
        </div>

        <div class="mt-6 text-center text-xs text-gray-600 italic">Dokumen ini telah disetujui secara elektronik melalui
            sistem [E-Logsheet], sehingga tidak memerlukan tanda tangan asli.</div>
    </div>
@endsection
