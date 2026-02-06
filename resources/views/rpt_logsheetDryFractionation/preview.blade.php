@extends('layouts.app')

@section('page_title', 'Laporan Dry Fractionation')

@section('content')
    <div class="bg-white p-6 rounded shadow-md text-sm relative print:p-0 print:shadow-none">
        
        {{-- DOC INFO TOP RIGHT --}}
        <div class="absolute top-4 right-6 text-xs leading-tight text-left">
            <div><strong>Form No.</strong> : {{ $formInfoFirst->form_no ?? 'F/RFA-010' }}</div>
            <div><strong>Date Issued</strong> :
                {{ $formInfoFirst ? optional($formInfoFirst->date_issued)->format('ymd') : '210101' }}</div>
            <div><strong>Revision</strong> : {{ $formInfoLast ? sprintf('%02d', $formInfoLast->revision_no) : '01' }}</div>
            <div><strong>Rev. Date</strong> :
                {{ $formInfoLast ? optional($formInfoLast->revision_date)->format('ymd') : '210901' }}</div>
        </div>

        {{-- CENTER HEADER --}}
        <div class="text-center mb-8 mt-4">
            <h2 class="text-lg font-bold uppercase tracking-widest">PT. PRISCOLIN</h2>
            <h3 class="text-xl font-bold uppercase underline">LOGSHEET DRY FRACTIONATION</h3>
            
            <div class="mt-2 font-semibold">
                Date: 
                @if($startDate == $endDate)
                    {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }}
                @else
                    {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} 
                    <span class="mx-1">-</span> 
                    {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                @endif
            </div>
        </div>

        {{-- TABLE CONTENT --}}
        {{-- Note: Ensure 'rpt_logsheetDryFractionation._table' exists --}}
        <div class="space-y-8">
            @forelse ($groupedData as $dateKey => $headers)
                <div class="mb-6 avoid-break-inside">
                    {{-- Optional: Sub-header per date if printing multiple days --}}
                    @if($startDate != $endDate)
                        <h4 class="font-bold text-gray-700 mb-2 border-b pb-1">
                            {{ \Carbon\Carbon::parse($dateKey)->format('l, d F Y') }}
                        </h4>
                    @endif

                    @include('rpt_logsheetDryFractionation._table', ['headers' => $headers])
                </div>

                 <div class="flex justify-center gap-24 mt-12 text-xs text-center avoid-break-inside">
            <div>
                <strong>Prepared By:</strong><br><br><br><br>
                <span class="border-b border-black px-4 inline-block min-w-[120px]">
                    {{ $headers->first()->prepared_by ?? '________________' }}
                </span>
                <br>(Leader Shift)
            </div>
            
            <div>
                <strong>Checked by:</strong><br><br><br><br>
                <span class="border-b border-black px-4 inline-block min-w-[120px]">
                    {{ $headers->first()->checked_by ?? '________________' }}
                </span>
                <br>(SPV / Manager)
            </div>
        </div>
            @empty
                <div class="text-center py-10 border border-dashed text-gray-400">
                    No data found for this date range.
                </div>
            @endforelse
        </div>

        {{-- SIGNATURE SECTION --}}
        {{-- Uncommented and safer logic --}}
       

        <div class="mt-6 text-center text-[10px] text-gray-500 italic">
            Dokumen ini telah disetujui secara elektronik melalui sistem [E-Logsheet], 
            sehingga tidak memerlukan tanda tangan asli basah.
        </div>
    </div>
@endsection