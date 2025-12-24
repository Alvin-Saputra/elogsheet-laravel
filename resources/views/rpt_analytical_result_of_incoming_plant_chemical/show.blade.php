@extends('layouts.app')

@section('page_title', 'Detail Report: ' . $header->id)

@section('content')

{{-- BACK BUTTON --}}
<div class="max-w-5xl mx-auto mb-4">
    <a href="{{ route('analytical-result-incoming-plant-chemical-ingredient.index') }}"
       class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400
              text-gray-800 text-sm font-semibold rounded-lg shadow">
        ← Back to List
    </a>
</div>

@php
    $displayDate = $header->date ?? $header->entry_date ?? null;
    $coa = $header->coa ?? null;
@endphp

<div class="space-y-12">

{{-- ================================================= --}}
{{-- PAGE 1 : ANALYTICAL RESULT --}}
{{-- ================================================= --}}
<div class="bg-white p-6 rounded shadow-md text-sm max-w-5xl mx-auto">

    {{-- TOP HEADER --}}
    <table class="w-full mb-4">
        <tr class="align-top">
            <td class="w-1/5 text-center">
                <img src="{{ asset('images/KPN Corp.jpg') }}" class="h-14 mx-auto mb-1">
                <div class="font-bold">BEKASI</div>
            </td>

            <td class="w-3/5 text-center">
                <h3 class="text-lg font-bold uppercase leading-tight">
                    Analytical Result of Incoming Plant<br>
                    Chemical / Ingredient
                </h3>
            </td>

            <td class="w-1/5">
                <div class="text-[11px] border border-black p-1">
                    <div class="grid grid-cols-[80px_10px_1fr] gap-x-1">
                        <span>No. Form</span><span>:</span>
                        <span>{{ $header->form_no ?? 'F/QOC-011' }}</span>

                        <span>Issued date</span><span>:</span>
                        <span>{{ $header->entry_date ? \Carbon\Carbon::parse($header->entry_date)->format('ymd') : '-' }}</span>

                        <span>Rev</span><span>:</span>
                        <span>{{ $header->revision_no ?? '01' }}</span>

                        <span>Rev date</span><span>:</span>
                        <span>{{ $header->revision_date ? \Carbon\Carbon::parse($header->revision_date)->format('ymd') : '-' }}</span>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- HEADER INFO --}}
    <div class="border border-gray-400 p-3 rounded mb-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <div class="flex"><strong class="w-28">Date</strong>
                    <span class="ml-2">{{ $displayDate ? \Carbon\Carbon::parse($displayDate)->format('d/m/Y') : '-' }}</span>
                </div>
                <div class="flex mt-1"><strong class="w-28">Material</strong>
                    <span class="ml-2">{{ $header->material ?? '-' }}</span>
                </div>
                <div class="flex mt-1"><strong class="w-28">Quantity</strong>
                    <span class="ml-2">{{ $header->quantity ?? '-' }}</span>
                </div>
                <div class="flex mt-1"><strong class="w-28">Analyst</strong>
                    <span class="ml-2">{{ $header->analyst ?? $header->entry_by ?? '-' }}</span>
                </div>
            </div>

            <div>
                <div class="flex"><strong class="w-28">No Ref CoA</strong>
                    <span class="ml-2">{{ $header->no_ref_coa ?? '-' }}</span>
                </div>
                <div class="flex mt-1"><strong class="w-28">Supplier</strong>
                    <span class="ml-2">{{ $header->supplier ?? '-' }}</span>
                </div>
                <div class="flex mt-1"><strong class="w-28">Police No.</strong>
                    <span class="ml-2">{{ $header->police_no ?? '-' }}</span>
                </div>
                <div class="flex mt-1"><strong class="w-28">Batch/Lot</strong>
                    <span class="ml-2">{{ $header->batch_lot ?? '-' }}</span>
                </div>
                <div class="flex mt-1"><strong class="w-28">Exp Date</strong>
                    <span class="ml-2">{{ $header->exp_date ? \Carbon\Carbon::parse($header->exp_date)->format('d/m/Y') : '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ANALYTICAL TABLE --}}
    <table class="w-full border border-gray-400 mb-6">
        <thead class="bg-gray-200">
            <tr>
                <th class="border px-2 py-1">Parameter</th>
                <th class="border px-2 py-1">Min</th>
                <th class="border px-2 py-1">Max</th>
                <th class="border px-2 py-1">Spec Min</th>
                <th class="border px-2 py-1">Spec Max</th>
                <th class="border px-2 py-1">OK</th>
                <th class="border px-2 py-1">NOK</th>
                <th class="border px-2 py-1">Remark</th>
            </tr>
        </thead>
        <tbody>
            @forelse($header->details as $detail)
                <tr>
                    <td class="border px-2 py-1">{{ $detail->parameter }}</td>
                    <td class="border px-2 py-1 text-center">{{ $detail->result_min }}</td>
                    <td class="border px-2 py-1 text-center">{{ $detail->result_max }}</td>
                    <td class="border px-2 py-1 text-center">{{ $detail->specification_min }}</td>
                    <td class="border px-2 py-1 text-center">{{ $detail->specification_max }}</td>
                    <td class="border px-2 py-1 text-center">{{ in_array(strtoupper((string)$detail->status_ok), ['1','Y','OK']) ? '✓' : '' }}</td>
                    <td class="border px-2 py-1 text-center">{{ in_array(strtoupper((string)$detail->status_ok), ['N','NO','NOT OK']) ? '✓' : '' }}</td>
                    <td class="border px-2 py-1">{{ $detail->remark }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-4 italic text-gray-500">
                        No analytical data found
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- SIGNATURE --}}
    <div class="grid grid-cols-3 text-center text-xs mt-6 gap-4">
        <div>
            <div class="font-semibold">Done by</div>
            <div>(Operator)</div>
            <div class="h-6"></div>
            <div>{{ $header->entry_by ?? '_________________' }}</div>
            <div class="text-gray-600">{{ optional($header->entry_date)->format('d-m-Y H:i') }}</div>
        </div>

        <div>
            <div class="font-semibold">Corrected by</div>
            <div>(QC Leader)</div>
            <div class="h-6"></div>
            <div>{{ $header->prepared_by ?? '_________________' }}</div>
            <div class="text-gray-600">{{ optional($header->prepared_date)->format('d-m-Y H:i') }}</div>
            @if(in_array(strtoupper($header->prepared_status ?? ''), ['REJECT','REJECTED']))
                <div class="text-[10px] italic text-red-600">Rejected</div>
            @endif
        </div>

        <div>
            <div class="font-semibold">Approved by</div>
            <div>(QC Head)</div>
            <div class="h-6"></div>
            <div>{{ $header->approved_by ?? '_________________' }}</div>
            <div class="text-gray-600">{{ optional($header->approved_date)->format('d-m-Y H:i') }}</div>
            @if(in_array(strtoupper($header->approved_status ?? ''), ['REJECT','REJECTED']))
                <div class="text-[10px] italic text-red-600">Rejected</div>
            @endif
        </div>
    </div>
</div>

{{-- ================================================= --}}
{{-- PAGE 2 : CERTIFICATE OF ANALYSIS --}}
{{-- ================================================= --}}
<div class="bg-white p-6 rounded shadow-md text-xs max-w-5xl mx-auto">

    <h2 class="text-center text-lg font-bold mb-4 uppercase">
        Certificate of Analysis
    </h2>

    <table class="w-full border border-black mb-4">
        <tr>
            <td class="border p-2 w-1/2">
                <div><strong>Product:</strong> {{ $coa->product ?? $header->material ?? '-' }}</div>
                <div><strong>Grade:</strong> {{ $coa->grade ?? '-' }}</div>
                <div><strong>Packing:</strong> {{ $coa->packing ?? '-' }}</div>
                <div><strong>Quantity:</strong> {{ $coa->quantity ?? $header->quantity ?? '-' }}</div>
                <div><strong>No Doc:</strong> {{ $coa->no_doc ?? $header->no_ref_coa ?? '-' }}</div>
            </td>
            <td class="border p-2 w-1/2">
                <div><strong>Vehicle:</strong> {{ $coa->vehicle ?? $header->police_no ?? '-' }}</div>
                <div><strong>Lot No:</strong> {{ $coa->lot_no ?? $header->batch_lot ?? '-' }}</div>
                <div><strong>Production:</strong> {{ optional($coa->production_date)->format('d/m/Y') }}</div>
                <div><strong>Expired:</strong> {{ optional($coa->expired_date)->format('d/m/Y') }}</div>
            </td>
        </tr>
    </table>

    <table class="w-full border border-black">
        <thead class="bg-gray-200">
            <tr>
                <th class="border px-2 py-1">Parameter</th>
                <th class="border px-2 py-1">Actual</th>
                <th class="border px-2 py-1">Standard</th>
                <th class="border px-2 py-1">Method</th>
            </tr>
        </thead>
        <tbody>
            @forelse($coa?->details ?? [] as $cdetail)
                <tr>
                    <td class="border px-2 py-1">{{ $cdetail->parameter }}</td>
                    <td class="border px-2 py-1 text-center">{{ $cdetail->actual_min }} - {{ $cdetail->actual_max }}</td>
                    <td class="border px-2 py-1 text-center">{{ $cdetail->standard_min }} - {{ $cdetail->standard_max }}</td>
                    <td class="border px-2 py-1 text-center">{{ $cdetail->method }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center italic py-4 text-gray-500">
                        No COA data found
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</div>

</div>
@endsection
