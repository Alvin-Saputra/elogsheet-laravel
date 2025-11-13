@extends('layouts.app')

@section('page_title', 'Laporan Quality Refinery')

@section('content')
    <div class="bg-white p-6 rounded shadow-md text-sm relative">
        {{-- Header --}}

        <div class="grid grid-cols-3 items-start mb-6 text-sm">
            {{-- Kiri atas --}}
            <div class="leading-tight">
                <div class="font-bold text-base">PT. PRISCOLLIN</div>
                <div>Date Of Production : {{ \Carbon\Carbon::parse($tanggal)->format('F d, Y') }}</div>
                <div>Date Of Report : {{ \Carbon\Carbon::parse($tanggal)->format('F d, Y') }}</div>
            </div>

            {{-- Judul Tengah --}}
            <div class="text-center col-span-1 flex flex-col justify-center">
                <h2 class="font-bold uppercase text-base">
                    DAILY QUALITY REFINERY PRODUCTION REPORT
                </h2>

                {{-- Kalau single refinery --}}
                @if (!empty($workCenter))
                    <div class="mt-1 text-sm font-medium">
                        {{ $refinery->name ?? '-' }} {{ $refinery->capacity ?? '' }}
                    </div>
                @else
                    {{-- Kalau multiple refinery --}}
                    <div class="mt-2 text-sm font-medium space-y-1">
                        @foreach ($groupedData as $wc => $rows)
                            @php
                                $firstRow = $rows->first();
                                $wcName = $firstRow->refinery_name ?? $wc;
                                $capacity = $firstRow->capacity ?? '';
                            @endphp
                            <div>{{ $wcName }} {{ $capacity }}</div>
                        @endforeach
                    </div>
                @endif
            </div>


            {{-- Kanan atas --}}
            <div class="text-xs leading-tight text-right">
                <div><strong>No. Form</strong> : {{ $formInfoFirst->form_no ?? '-' }}</div>
                <div><strong>Issue. Date</strong> :
                    {{ $formInfoFirst && $formInfoFirst->date_issued
                        ? \Carbon\Carbon::parse($formInfoFirst->date_issued)->format('ymd')
                        : '-' }}
                    <div><strong>Rev. No.</strong> :
                        {{ '0' }}</div>
                    <div><strong>Rev. Date.</strong> :
                        {{ '-' }}</div>
                    {{-- <div><strong>Rev. No.</strong> :
                    {{ $formInfoLast && $formInfoLast->revision_no !== null ? $formInfoLast->revision_no : '-' }}</div> --}}
                </div>
            </div>
        </div>




        {{-- Table Section --}}
        @if (!empty($workCenter))
            @include('rpt_quality.QC._table', ['rows' => $data])
        @else
            @foreach ($groupedData as $wc => $rows)
                @php
                    $firstRow = $rows->first();
                    $oilTypeName = $firstRow->oil_type ?? '-';
                    $wcName = $firstRow->refinery_name ?? $wc;
                @endphp

                <h4 class="text-md font-bold mt-6 mb-2">
                    {{ $wcName }} ({{ $wc }}) | Oil Type: {{ $oilTypeName }}
                </h4>

                @include('rpt_quality.QC._table', ['rows' => $rows, 'workCenter' => $firstRow->work_center ?? $wc,])
            @endforeach
        @endif


        {{-- Footer Box --}}
        <div class="grid grid-cols-3 gap-6 mt-6 text-xs">
            <div class="border p-2">
                <strong>Daily Chemical Usage</strong>
                <table class="w-full text-left mt-1">
                    <tr>
                        <td>Bleaching Earth</td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td>Phosphoric Acid</td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td>RPO Usage</td>
                        <td>-</td>
                    </tr>
                </table>
            </div>
            <div class="border p-2">
                <strong>Theoretical Yield</strong>
                <table class="w-full text-left mt-1">
                    <tr>
                        <td>RPO</td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td>PFAD</td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td>Losses</td>
                        <td>-</td>
                    </tr>
                </table>
            </div>
        </div>

        @php
            $lastShift = collect($signaturesQc)
                ->filter(function ($s) {
                    return $s['prepared'] || $s['acknowledge'];
                })
                ->last();
        @endphp

        @if ($lastShift)
            <div class="grid grid-cols-2 mt-10 text-center text-xs">
                <div>
                    Prepared by,<br><br><br>
                    <strong>({{ $lastShift['prepared']['name'] ?? '-' }})</strong><br>
                    {{ $lastShift['prepared']['date']
                        ? \Carbon\Carbon::parse($lastShift['prepared']['date'])->format('d-m-Y H:i')
                        : '' }}
                </div>
                <div>
                    Acknowledged by,<br><br><br>
                    <strong>({{ $lastShift['acknowledge']['name'] ?? '-' }})</strong><br>
                    {{ $lastShift['acknowledge']['date']
                        ? \Carbon\Carbon::parse($lastShift['acknowledge']['date'])->format('d-m-Y H:i')
                        : '' }}
                </div>
            </div>
        @endif



        {{-- Informasi persetujuan elektronik --}}
        <div class="mt-6 text-center text-xs text-gray-600 italic">
            Dokumen ini telah disetujui secara elektronik melalui sistem [E-Logsheet],
            sehingga tidak memerlukan tanda tangan asli.
        </div>
    </div>
@endsection
