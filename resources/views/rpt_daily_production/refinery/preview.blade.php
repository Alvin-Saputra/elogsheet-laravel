@extends('layouts.app')

@section('page_title', 'Laporan Harian Produksi Refinery')

@section('content')
    <div class="bg-white p-6 rounded shadow-md text-sm relative">
        <div class="absolute top-4 right-6 text-xs leading-tight text-left">
            {{-- Form Info (Upper Right Corner) --}}
            <div><strong>Form No.</strong> : {{ $formInfoFirst->form_no ?? 'F/RFA-XXX' }}</div>
            <div><strong>Date Issued</strong> :
                {{ $formInfoFirst ? optional($formInfoFirst->date_issued)->format('ymd') : 'YYMMDD' }}</div>
            <div><strong>Revision</strong> : {{ $formInfoLast ? sprintf('%02d', $formInfoLast->revision_no) : '01' }}</div>
            <div><strong>Rev. Date</strong> :
                {{ $formInfoLast ? optional($formInfoLast->revision_date)->format('ymd') : 'YYMMDD' }}</div>
        </div>

        <div class="text-center mb-4">
            <h2 class="text-lg font-bold uppercase">PT.PRISCOLIN</h2>
            <h3 class="text-xl font-bold uppercase">DAILY PRODUCTION REFINERY REPORT</h3>
            <div class="mt-1">Date: {{ \Carbon\Carbon::parse($tanggal)->format('d-m-Y') }}</div>
        </div>

        {{-- Dynamic Content: Single Work Center or Grouped --}}
        @if (!empty($workCenter))
            @php
                $summaryRows = $shiftSummaries instanceof \Illuminate\Support\Collection ? $shiftSummaries : collect();
            @endphp
            <h4 class="text-md font-bold mb-2">Work Center: {{ $workCenter }}</h4>
            {{-- Include the dedicated table for the single work center --}}
            @include('rpt_daily_production.refinery._table_product', ['rows' => $data])
            @include('rpt_daily_production.refinery._table_chemicals', ['rows' => $summaryRows])
            @include('rpt_daily_production.refinery._table_utilities', ['rows' => $summaryRows])
            @include('rpt_daily_production.refinery._table_remarks', ['rows' => $summaryRows])
        @else
            {{-- Loop through grouped data if no specific work center is selected --}}

            @foreach ($groupedData as $wc => $rows)
                @php
                    $summaryRows = $shiftSummaries[$wc] ?? collect();
                @endphp
                <h4 class="text-md font-bold mt-6 mb-2">Work Center: {{ $wc }}</h4>
                {{-- 1. Product Table (Individual Tickets) --}}
                @include('rpt_daily_production.refinery._table_product', ['rows' => $rows])

                {{-- Summary Tables (using the $summaryRows collection for the current WC) --}}
                @if (!$summaryRows->isEmpty())
                    {{-- 2. Chemicals Table (Shift Summary) --}}
                    @include('rpt_daily_production.refinery._table_chemicals', ['rows' => $summaryRows])

                    {{-- 3. Utilities Table (Shift Summary) --}}
                    @include('rpt_daily_production.refinery._table_utilities', ['rows' => $summaryRows])

                    {{-- 4. Remarks Table (Shift Summary - Concatenated Remarks) --}}
                    @include('rpt_daily_production.refinery._table_remarks', ['rows' => $summaryRows])
                @else
                    <p class="mt-4 text-center text-gray-500 italic">No shift summary data available for Work Center:
                        {{ $wc }} on this date.</p>
                @endif
            @endforeach
        @endif

        {{-- Signatures Section --}}
        <div class="grid grid-cols-4 text-center mt-10 text-xs">
            @foreach (['1' => 'SHIFT 1', '2' => 'SHIFT 2', '3' => 'SHIFT 3'] as $key => $label)
                <div><strong>Prepared by: ({{ $label }})</strong><br><br><br>
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
        <div class="mt-6 text-center text-xs text-gray-600 italic">Dokumen ini telah disetujui secara elektronik melalui
            sistem [E-Logsheet],
            sehingga tidak memerlukan tanda tangan asli.</div>
    </div>
@endsection
