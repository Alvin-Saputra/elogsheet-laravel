@extends('layouts.app')

@section('page_title', 'Analytical Result Incoming Plant Chemical - Preview')

@section('content')

  @php
    $displayDate = $header->date ?? $header->entry_date ?? null;
  @endphp

  <style>
    .page-break {
      page-break-before: always;
    }
  </style>

  <div class="space-y-10">

    {{-- ================================================= --}}
    {{-- ========== PAGE 1 : ANALYTICAL RESULT =========== --}}
    {{-- ================================================= --}}
    <div class="bg-white p-6 rounded shadow-md text-sm relative max-w-4xl mx-auto">

      {{-- ===== TOP HEADER ===== --}}
      <table class="w-full mb-4">
        <tr class="align-top">
          <td class="w-1/5 text-center align-top">
            <img src="{{ asset('images/KPN Corp.jpg') }}" class="h-14 mx-auto mb-1" alt="logo">
            <div class="font-bold">BEKASI</div>
          </td>

          <td class="w-3/5 text-center align-top">
            <h3 class="text-lg font-bold uppercase leading-tight">
              Analytical Result of Incoming Plant<br>
              Chemical / Ingredient
            </h3>
          </td>

          <td class="w-1/5 align-top">
            <div class="text-[11px] border border-black p-1">
              <div class="grid grid-cols-[80px_10px_1fr] gap-x-1">
                <span>No. Form</span>
                <span>:</span>
                <span>{{ $header->form_no ?? 'F/QOC-011' }}</span>

                <span>Issued date</span>
                <span>:</span>
                <span>
                  {{ $header->entry_date
                      ? \Carbon\Carbon::parse($header->entry_date)->format('ymd')
                      : '-' }}
                </span>

                <span>Rev</span>
                <span>:</span>
                <span>{{ $header->revision_no ?? '01' }}</span>

                <span>Rev date</span>
                <span>:</span>
                <span>
                  {{ $header->revision_date
                      ? \Carbon\Carbon::parse($header->revision_date)->format('ymd')
                      : '-' }}
                </span>
              </div>
            </div>
          </td>
        </tr>
      </table>

      {{-- ===== HEADER INFO ===== --}}
      <div class="border border-gray-400 p-3 rounded mb-4">
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div>
            <div class="flex">
              <strong class="w-28">Date</strong>
              <span class="ml-2">{{ $displayDate ? \Carbon\Carbon::parse($displayDate)->format('d/m/Y') : '-' }}</span>
            </div>
            <div class="flex mt-1">
              <strong class="w-28">Material</strong>
              <span class="ml-2">{{ $header->material ?? '-' }}</span>
            </div>
            <div class="flex mt-1">
              <strong class="w-28">Quantity</strong>
              <span class="ml-2">{{ $header->quantity !== null ? (string) $header->quantity : '-' }}</span>
            </div>
            <div class="flex mt-1">
              <strong class="w-28">Analyst</strong>
              <span class="ml-2">{{ $header->analyst ?? $header->entry_by ?? '-' }}</span>
            </div>
          </div>

          <div>
            <div class="flex">
              <strong class="w-28">No Ref CoA</strong>
              <span class="ml-2">{{ $header->no_ref_coa ?? '-' }}</span>
            </div>
            <div class="flex mt-1">
              <strong class="w-28">Supplier</strong>
              <span class="ml-2">{{ $header->supplier ?? '-' }}</span>
            </div>
            <div class="flex mt-1">
              <strong class="w-28">Police No.</strong>
              <span class="ml-2">{{ $header->police_no ?? '-' }}</span>
            </div>
            <div class="flex mt-1">
              <strong class="w-28">Batch/Lot</strong>
              <span class="ml-2">{{ $header->batch_lot ?? '-' }}</span>
            </div>
            <div class="flex mt-1">
              <strong class="w-28">Exp Date</strong>
              <span class="ml-2">{{ $header->exp_date ? \Carbon\Carbon::parse($header->exp_date)->format('d/m/Y') : '-' }}</span>
            </div>
          </div>
        </div>
      </div>

      {{-- ===== ANALYTICAL TABLE ===== --}}
      <table class="w-full border border-gray-400 mb-6 table-auto">
        <thead class="bg-gray-200">
          <tr>
            <th class="border px-2 py-2 text-sm">Parameter</th>
            <th class="border px-2 py-2 text-sm">Min</th>
            <th class="border px-2 py-2 text-sm">Max</th>
            <th class="border px-2 py-2 text-sm">Spec Min</th>
            <th class="border px-2 py-2 text-sm">Spec Max</th>
            <th class="border px-2 py-2 text-sm">OK</th>
            <th class="border px-2 py-2 text-sm">NOK</th>
            <th class="border px-2 py-2 text-sm">Remark</th>
          </tr>
        </thead>
        <tbody class="text-sm text-gray-800">
          @forelse($header->details as $detail)
            <tr>
              <td class="border px-2 py-1">{{ $detail->parameter ?? '-' }}</td>
              <td class="border px-2 py-1 text-center">{{ $detail->result_min ?? '-' }}</td>
              <td class="border px-2 py-1 text-center">{{ $detail->result_max ?? '-' }}</td>
              <td class="border px-2 py-1 text-center">{{ $detail->specification_min ?? '-' }}</td>
              <td class="border px-2 py-1 text-center">{{ $detail->specification_max ?? '-' }}</td>
              <td class="border px-2 py-1 text-center">{{ in_array(strtoupper((string)$detail->status_ok), ['1','Y','OK']) ? '✓' : '' }}</td>
              <td class="border px-2 py-1 text-center">{{ in_array(strtoupper((string)$detail->status_ok), ['N','NOT OK','NO']) ? '✓' : '' }}</td>
              <td class="border px-2 py-1">{{ $detail->remark ?? '-' }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center py-4 text-gray-500 italic">No analytical data found</td>
            </tr>
          @endforelse
        </tbody>
      </table>

      {{-- ===== SIGNATURE ===== --}}
      <div class="grid grid-cols-3 text-center text-xs mt-6 gap-4">
        <div class="space-y-1">
          <div class="font-semibold">Done by</div>
          <div class="text-[12px]">(Operator)</div>
          <div class="h-6"></div>
          <div class="font-medium">{{ $header->entry_by ?? '_________________' }}</div>
          <div class="text-xs text-gray-600">{{ $header->entry_date ? \Carbon\Carbon::parse($header->entry_date)->format('d-m-Y H:i') : '' }}</div>
        </div>

        <div class="space-y-1">
          <div class="font-semibold">Corrected by</div>
          <div class="text-[12px]">(QC Leader)</div>
          <div class="h-6"></div>
          <div class="font-medium">{{ $header->prepared_by ?? '_________________' }}</div>
          <div class="text-xs text-gray-600">{{ $header->prepared_date ? \Carbon\Carbon::parse($header->prepared_date)->format('d-m-Y H:i') : '' }}</div>

          @if(in_array(strtoupper($header->prepared_status ?? ''), ['REJECT', 'REJECTED']))
            <div class="mt-1 text-[10px] italic text-red-600">Rejected</div>
          @endif
        </div>

        <div class="space-y-1">
          <div class="font-semibold">Approved by</div>
          <div class="text-[12px]">(QC Head)</div>
          <div class="h-6"></div>
          <div class="font-medium">{{ $header->approved_by ?? '_________________' }}</div>
          <div class="text-xs text-gray-600">{{ $header->approved_date ? \Carbon\Carbon::parse($header->approved_date)->format('d-m-Y H:i') : '' }}</div>

          @if(in_array(strtoupper($header->approved_status ?? ''), ['REJECT', 'REJECTED']))
            <div class="mt-1 text-[10px] italic text-red-600">Rejected</div>
          @endif
        </div>
      </div>

    </div>

    {{-- PAGE BREAK --}}
    <div class="page-break"></div>

    {{-- ================================================= --}}
    {{-- ========== PAGE 2 : CERTIFICATE OF ANALYSIS ====== --}}
    {{-- ================================================= --}}
    <div class="bg-white p-6 text-xs max-w-4xl mx-auto">

      <h2 class="text-center text-lg font-bold mb-4 uppercase">
        Certificate of Analysis
      </h2>

      @php $coa = $header->coa ?? null; @endphp

      <table class="w-full border border-black mb-4">
        <tr>
          <td class="border p-2 w-1/2 align-top">
            <div class="flex"><span class="w-28 font-semibold">Product :</span><span class="ml-2">{{ $coa->product ?? $header->material ?? '-' }}</span></div>
            <div class="flex mt-1"><span class="w-28 font-semibold">Grade :</span><span class="ml-2">{{ $coa->grade ?? '-' }}</span></div>
            <div class="flex mt-1"><span class="w-28 font-semibold">Packing :</span><span class="ml-2">{{ $coa->packing ?? '-' }}</span></div>
            <div class="flex mt-1"><span class="w-28 font-semibold">Quantity :</span><span class="ml-2">{{ $coa->quantity ?? $header->quantity ?? '-' }}</span></div>
            <div class="flex mt-1"><span class="w-28 font-semibold">No Doc :</span><span class="ml-2">{{ $coa->no_doc ?? $header->no_ref_coa ?? '-' }}</span></div>
          </td>

          <td class="border p-2 w-1/2 align-top">
            <div class="flex"><span class="w-28 font-semibold">Vehicle :</span><span class="ml-2">{{ $coa->vehicle ?? $header->police_no ?? '-' }}</span></div>
            <div class="flex mt-1"><span class="w-28 font-semibold">Lot No :</span><span class="ml-2">{{ $coa->lot_no ?? $header->batch_lot ?? '-' }}</span></div>
            <div class="flex mt-1"><span class="w-28 font-semibold">Production :</span><span class="ml-2">{{ $coa->production_date ? \Carbon\Carbon::parse($coa->production_date)->format('d/m/Y') : '-' }}</span></div>
            <div class="flex mt-1"><span class="w-28 font-semibold">Expired :</span><span class="ml-2">{{ $coa->expired_date ? \Carbon\Carbon::parse($coa->expired_date)->format('d/m/Y') : ($header->exp_date ? \Carbon\Carbon::parse($header->exp_date)->format('d/m/Y') : '-') }}</span></div>
          </td>
        </tr>
      </table>

      <table class="w-full border border-black mb-6">
        <thead>
          <tr class="bg-gray-200">
            <th class="border px-2 py-2 text-sm">Parameter</th>
            <th class="border px-2 py-2 text-sm">Actual</th>
            <th class="border px-2 py-2 text-sm">Standard</th>
            <th class="border px-2 py-2 text-sm">Method</th>
          </tr>
        </thead>
        <tbody class="text-sm text-gray-800">
          @if($coa && $coa->details->count())
            @foreach($coa->details as $cdetail)
              <tr>
                <td class="border px-2 py-1">{{ $cdetail->parameter ?? '-' }}</td>
                <td class="border px-2 py-1 text-center">{{ $cdetail->actual_min ?? '-' }}{{ $cdetail->actual_max ? ' - '.$cdetail->actual_max : '' }}</td>
                <td class="border px-2 py-1 text-center">{{ $cdetail->standard_min ?? '-' }}{{ $cdetail->standard_max ? ' - '.$cdetail->standard_max : '' }}</td>
                <td class="border px-2 py-1 text-center">{{ $cdetail->method ?? '-' }}</td>
              </tr>
            @endforeach
          @else
            @forelse($header->details as $detail)
              <tr>
                <td class="border px-2 py-1">{{ $detail->parameter ?? '-' }}</td>
                <td class="border px-2 py-1 text-center">{{ $detail->result_min ?? '-' }}{{ $detail->result_max ? ' - '.$detail->result_max : '' }}</td>
                <td class="border px-2 py-1 text-center">{{ $detail->specification_min ?? '-' }}{{ $detail->specification_max ? ' - '.$detail->specification_max : '' }}</td>
                <td class="border px-2 py-1 text-center">-</td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="text-center py-4 text-gray-500 italic">No COA data found</td>
              </tr>
            @endforelse
          @endif
        </tbody>
      </table>

      {{-- COA Signature: right side but content centered --}}
      <div class="flex justify-end mt-12">
        <div class="text-center w-48">
          <div class="text-sm font-medium">Issue by</div>
          <div class="h-10"></div>
          <div class="font-semibold">{{ $coa->issue_by ?? $header->approved_by ?? '_________________' }}</div>
          <div class="text-xs text-gray-600">Head of Quality Assurance</div>
        </div>
      </div>

    </div>

  </div>
@endsection
