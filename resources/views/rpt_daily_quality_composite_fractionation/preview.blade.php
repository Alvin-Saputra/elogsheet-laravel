@extends('layouts.app')

@section('page_title', 'Laporan Daily Quality Composite Fractionation')

@section('content')
    <div class="bg-white p-6 rounded shadow-md text-sm relative">

        {{-- ================= FORM INFO (KANAN ATAS) ================= --}}
        <div class="absolute top-4 right-6 text-xs leading-tight text-left">
            <div><strong>Form No.</strong> : {{ $formInfoFirst->form_no ?? 'F/QCO-003' }}</div>
            <div><strong>Date Issued</strong> :
                {{ $formInfoFirst ? optional($formInfoFirst->date_issued)->format('ymd') : '210101' }}
            </div>
            <div><strong>Revision</strong> :
                {{ $formInfoLast ? sprintf('%02d', $formInfoLast->revision_no) : '01' }}
            </div>
            <div><strong>Rev. Date</strong> :
                {{ $formInfoLast ? optional($formInfoLast->revision_date)->format('ymd') : '210901' }}
            </div>
        </div>

        {{-- ================= HEADER ================= --}}
        <div class="text-center mb-4">
            <h2 class="text-lg font-bold uppercase">PT. PRISCOLIN</h2>
            <h3 class="text-xl font-bold uppercase">
                LOGSHEET DAILY QUALITY COMPOSITE FRACTIONATION
            </h3>

            {{-- OPERATIONAL DATE --}}
            <div class="mt-2 text-sm">
                <strong>Operational Date:</strong>
                {{ \Carbon\Carbon::parse($filterTanggal)->format('d-m-Y') }} 08:00 –
                {{ \Carbon\Carbon::parse($filterTanggal)->addDay()->format('d-m-Y') }} 07:59
            </div>
        </div>

        {{-- ================= WORK CENTER SECTION ================= --}}
        @if (!empty($filterWorkCenter))

            {{-- SINGLE WORK CENTER --}}
            <div class="text-center mb-4">
                @if ($filterWorkCenter === 'FRAC-02')
                    <h2 class="text-sm font-bold uppercase">Fractionation 500</h2>
                @else
                    <h2 class="text-sm font-bold uppercase">Fractionation 400</h2>
                @endif
            </div>

            @include('rpt_daily_quality_composite_fractionation._table', ['rows' => $data])

        @else

            {{-- MULTI WORK CENTER --}}
            @foreach ($groupedData as $wc => $rows)
                @php
                    $firstRow = $rows->first();
                    $workCenter = $firstRow->work_center ?? '';
                @endphp

                <div class="text-center mb-4 mt-6">
                    @if ($workCenter === 'FRAC-02')
                        <h2 class="text-sm font-bold uppercase">Fractionation 500</h2>
                    @else
                        <h2 class="text-sm font-bold uppercase">Fractionation 400</h2>
                    @endif
                </div>

                @include('rpt_daily_quality_composite_fractionation._table', [
                    'rows' => $rows,
                    'workCenter' => $workCenter,
                ])
            @endforeach

        @endif

        {{-- ================= SIGNATURE ================= --}}
        <div class="flex justify-center gap-16 mt-10 text-xs text-center">
            <div>
                <strong>Prepared By:</strong><br><br><br>
                {{ $sign->prepared_by ?? '________________' }}<br>
                (Leader Shift)<br>
                <small>
                    {{ !empty($sign->prepared_date)
                        ? \Carbon\Carbon::parse($sign->prepared_date)->format('d M Y H:i')
                        : '' }}
                </small>
            </div>

            <div>
                <strong>Approved By:</strong><br><br><br>
                {{ $sign->checked_by ?? '________________' }}<br>
                (QC Section Head)<br>
                <small>
                    {{ !empty($sign->checked_date)
                        ? \Carbon\Carbon::parse($sign->checked_date)->format('d M Y H:i')
                        : '' }}
                </small>
            </div>
        </div>

        {{-- ================= FOOTNOTE ================= --}}
        <div class="mt-6 text-center text-xs text-gray-600 italic">
            Data disajikan berdasarkan <strong>hari operasional (08.00 – 07.59)</strong>,
            bukan berdasarkan kalender.
            <br>
            Dokumen ini telah disetujui secara elektronik melalui sistem <strong>[E-Logsheet]</strong>,
            sehingga tidak memerlukan tanda tangan basah.
        </div>

    </div>
@endsection
