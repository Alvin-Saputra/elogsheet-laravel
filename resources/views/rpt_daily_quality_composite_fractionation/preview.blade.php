@extends('layouts.app')

@section('page_title', 'Laporan Daily Quality Composite Fractionation')

@section('content')
    <div class="bg-white p-6 rounded shadow-md text-sm relative">
        <div class="absolute top-4 right-6 text-xs leading-tight text-left">
            <div><strong>Form No.</strong> : {{ $formInfoFirst->form_no ?? 'F/QOC-003' }}</div>
            <div><strong>Date Issued</strong> :
                {{ $formInfoFirst ? optional($formInfoFirst->date_issued)->format('ymd') : '210101' }}</div>
            <div><strong>Revision</strong> : {{ $formInfoLast ? sprintf('%02d', $formInfoLast->revision_no) : '01' }}</div>
            <div><strong>Rev. Date</strong> :
                {{ $formInfoLast ? optional($formInfoLast->revision_date)->format('ymd') : '210901' }}</div>
        </div>

        <div class="text-center mb-4">
            <h2 class="text-lg font-bold uppercase">PT.PRISCOLIN</h2>
            <h3 class="text-xl font-bold uppercase">LOGSHEET DAILY Quality Composite Fractionation</h3>
            <div class="mt-1">Date: {{ \Carbon\Carbon::parse($filterTanggal)->format('d-m-Y') }}</div>
            <br>
        </div>


        @if (!empty($filterWorkCenter))
            @if ($filterWorkCenter === 'FRAC-02')
            <div class="text-center mb-4">
                <h2 class="text-sm font-bold uppercase">Fractionation 500</h2>
            </div>
            @else
             <div class="text-center mb-4">
                <h2 class="text-lg font-bold uppercase">Fractionation 400</h2>
             </div>
            @endif
            @include('rpt_daily_quality_composite_fractionation._table', ['rows' => $data])
        @else
            @foreach ($groupedData as $wc => $rows)
                @php
                    $firstRow = $rows->first();
                    $workCenter = $firstRow->work_center;
                @endphp

                {{-- <h4 class="text-md font-bold mt-6 mb-2">
                    {{ $wcName }} ({{ $wc }}) | Oil Type: {{ $oilTypeName }}
                </h4> --}}
                @if ($workCenter === 'FRAC-02')
                 <div class="text-center mb-4">
                    <h2 class="text-sm font-bold uppercase">Fractionation 500</h2>
                 </div>
                @else
                 <div class="text-center mb-4">
                    <h2 class="text-sm font-bold uppercase">Fractionation 400</h2>
                 </div>
                @endif
                @include('rpt_daily_quality_composite_fractionation._table', [
                    'rows' => $rows,
                    'workCenter' => $firstRow->work_center,
                ])
            @endforeach
        @endif


        <div class="flex justify-center gap-16 mt-10 text-xs text-center">
            <div><strong>Prepared By:</strong><br><br><br>
                {{ $sign->prepared_by ?? '________________' }}<br>(Leader
                Shift)<br><small>{{ !empty($sign->prepared_date) ? \Carbon\Carbon::parse($sign->prepared_date)->format('d M Y H:i') : '' }}</small>
            </div>
            <div><strong>Approved by:</strong><br><br><br>
                {{ $sign->checked_by ?? '________________' }}<br>(QC Section Head)<br><small>{{ !empty($sign->checked_date) ? \Carbon\Carbon::parse($sign->checked_date)->format('d M Y H:i') : '' }}</small>
            </div>
        </div>

        <div class="mt-6 text-center text-xs text-gray-600 italic">Dokumen ini telah disetujui secara elektronik melalui
            sistem [E-Logsheet], sehingga tidak memerlukan tanda tangan asli.</div>
    </div>
@endsection
