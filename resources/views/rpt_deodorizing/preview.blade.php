@extends('layouts.app')

@section('page_title', 'Laporan Deodorizing & Filtration')

@section('content')
    <div class="bg-white p-6 rounded shadow-md text-sm relative">
        <div class="absolute top-4 right-6 text-xs leading-tight text-left">
            <div><strong>Form No.</strong> : {{ $formInfoFirst->form_no ?? 'F/RFA-003' }}</div>
            <div><strong>Date Issued</strong> :
                {{ $formInfoFirst ? optional($formInfoFirst->date_issued)->format('ymd') : '210101' }}</div>
            <div><strong>Revision</strong> : {{ $formInfoLast ? sprintf('%02d', $formInfoLast->revision_no) : '01' }}</div>
            <div><strong>Rev. Date</strong> :
                {{ $formInfoLast ? optional($formInfoLast->revision_date)->format('ymd') : '210901' }}</div>
        </div>

        <div class="text-center mb-4">
            <h2 class="text-lg font-bold uppercase">PT.PRISCOLIN</h2>
            <h3 class="text-xl font-bold uppercase">DEODORIZING & FILTRATION LOGSHEET</h3>
            <div class="mt-1">Date: {{ \Carbon\Carbon::parse($tanggal)->format('d-m-Y') }}</div>
        </div>

            @foreach ($groupedData as $rm => $rows)
                <h4 class="text-md font-bold mt-6 mb-2">Work Center: {{ $rm }}</h4>
                @include('rpt_deodorizing._table', ['rows' => $rows])
            @endforeach

        <div class="grid grid-cols-4 text-center mt-10 text-xs">
            @foreach (['shift1' => '1st SHIFT', 'shift2' => '2nd SHIFT', 'shift3' => '3rd SHIFT'] as $key => $label)
                <div><strong>{{ $label }}</strong><br><br><br>
                    @if (!empty($signatures[$key]))
                        {{ $signatures[$key]['name'] ?? '---' }}<br>(Shift
                        Leader)<br><small>{{ !empty($signatures[$key]['date']) ? \Carbon\Carbon::parse($signatures[$key]['date'])->format('d M Y H:i') : '' }}</small>
                    @else
                        ______________<br>(Shift Leader)<br>
                    @endif
                </div>
            @endforeach
            <div><strong>Checked by:</strong><br><br><br>
                {{ $sign->checked_by ?? '________________' }}<br>(Department
                Head)<br><small>{{ !empty($sign->checked_date) ? \Carbon\Carbon::parse($sign->checked_date)->format('d M Y H:i') : '' }}</small>
            </div>
        </div>
        <div class="mt-6 text-center text-xs text-gray-600 italic">Dokumen ini telah disetujui secara elektronik.</div>
    </div>
@endsection
