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
      {{-- Logic untuk mengambil Data Daily Production (Interlock) --}}
        @php
            $production = null;
            // Cek sumber data, apakah single ($data) atau grouped ($groupedData)
            if (!empty($data) && $data->count() > 0) {
                $production = $data->first()->dailyProduction;
            } elseif (!empty($groupedData) && $groupedData->count() > 0) {
                // Ambil dari grup pertama, baris pertama
                $production = $groupedData->first()->first()->dailyProduction;
            }
        @endphp

      {{-- Logic untuk mengelompokkan Data Daily Production per Work Center --}}
        @php
            $productionList = [];

            // Skenario 1: Jika User memfilter 1 Work Center spesifik
            if (!empty($workCenter) && !empty($data) && $data->count() > 0) {
                // Ambil data produksi dari row pertama
                $prod = $data->first()->dailyProduction;
                if ($prod) {
                    $productionList[$workCenter] = $prod;
                }
            } 
            // Skenario 2: Jika Laporan menampilkan banyak Work Center (Grouped Data)
            elseif (!empty($groupedData)) {
                foreach ($groupedData as $wc => $rows) {
                    if ($rows->count() > 0) {
                        $prod = $rows->first()->dailyProduction;
                        // Masukkan ke list meskipun null (nanti di-handle di view dengan tanda '-')
                        $productionList[$wc] = $prod;
                    }
                }
            }
        @endphp

        {{-- Footer Box Loop --}}
        @foreach ($productionList as $wcKey => $production)
            <div class="mt-6">
                {{-- Judul Kecil untuk membedakan Data Produksi milik siapa --}}
                <div class="font-bold text-xs mb-1 underline">
                    Refinery Data: {{ $wcKey }}
                </div>

                <div class="grid grid-cols-3 gap-6 text-xs">
                    {{-- Box Kiri: Chemical Usage --}}
                    <div class="border p-2">
                        <strong>Daily Chemical Usage</strong>
                        <table class="w-full text-left mt-1">
                            <tr>
                                <td class="w-1/2">Bleaching Earth</td>
                                <td>: {{ $production->be_ref_qty ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td>Phosphoric Acid</td>
                                <td>: {{ $production->pa_ref_qty ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td>RPO Usage</td>
                                <td>: {{ $production->oil_type_rm_total ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>

                    {{-- Box Tengah: Theoretical Yield --}}
                    <div class="border p-2">
                        <strong>Theoretical Yield</strong>
                        <table class="w-full text-left mt-1">
                            <tr>
                                <td class="w-1/2">RPO</td>
                                <td>: {{ $production->oil_type_fg_total ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td>PFAD</td>
                                <td>: {{ $production->bp_total ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td>Losses</td>
                                <td>: {{ $production->uu_yield_percent ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    
                    {{-- Box Kanan (Opsional: Kosong atau Catatan lain) --}}
                    {{-- Jika ingin layout 2 kolom saja, ubah grid-cols-3 jadi grid-cols-2 --}}
                    <div></div> 
                </div>
            </div>
        @endforeach

        {{-- Jika Data Produksi Kosong sama sekali (Opsional) --}}
        @if (empty($productionList))
            <div class="mt-6 text-xs text-center border p-2 text-gray-500">
                Data Produksi (Interlock) belum tersedia.
            </div>
        @endif

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
                     <p>({{ $lastShift['prepared']['role'] ?? '-' }})</p><br>
                    {{ $lastShift['prepared']['date']
                        ? \Carbon\Carbon::parse($lastShift['prepared']['date'])->format('d-m-Y H:i')
                        : '' }}
                </div>
                <div>
                    Acknowledged by,<br><br><br>
                    <strong>({{ $lastShift['acknowledge']['name'] ?? '-' }})</strong><br>
                     <p>({{ $lastShift['acknowledge']['role'] ?? '-' }})</p><br>
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
