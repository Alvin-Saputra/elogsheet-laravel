@extends('layouts.app')

@section('page_title', 'Analytical Result Incoming Plant Fuel - Preview')


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
{{-- BACK BUTTON --}}
<div class="max-w-5xl mx-auto mb-4">
  <a href="{{ route('analytical-result-incoming-plant-fuel.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400
              text-gray-800 text-sm font-semibold rounded-lg shadow">
    ← Back to List
  </a>
</div>

    {{-- ================================================= --}}
    {{-- ========== PAGE 1 : ANALYTICAL RESULT =========== --}}
    {{-- ================================================= --}}
    <div class="bg-white p-6 rounded shadow-md text-sm relative max-w-4xl mx-auto">

      {{-- ===== TOP HEADER ===== --}}
      <table class="w-full mb-4">
        <tr class="align-top">
          <td class="w-1/5 text-center">
            <img src="{{ asset('images/KPN Corp.jpg') }}" class="h-14 mx-auto mb-1">
            <div class="font-bold">BEKASI</div>
          </td>

          <td class="w-3/5 text-center">
            <h3 class="text-lg font-bold uppercase">
              Analytical Result of Incoming Plant Fuel
            </h3>
          </td>

          <td class="w-1/5">
            <div class="text-[11px] border border-black p-1">
              <div class="grid grid-cols-[80px_10px_1fr] gap-x-1">
                <span>No. Form</span><span>:</span><span>{{ $header->form_no }}</span>
                <span>Issued</span><span>:</span><span>{{ \Carbon\Carbon::parse($header->date_issued)->format('ymd') }}</span>
                <span>Rev</span><span>:</span><span>{{ $header->revision_no }}</span>
                <span>Rev
                  Date</span><span>:</span><span>{{ \Carbon\Carbon::parse($header->revision_date)->format('ymd') }}</span>
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
              <span class="ml-2">: {{ $displayDate ? \Carbon\Carbon::parse($displayDate)->format('d/m/Y') : '-' }}</span>
            </div>
            <div class="flex mt-1">
              <strong class="w-28">Material</strong>
              <span class="ml-2">: {{ $header->material }}</span>
            </div>
            <div class="flex mt-1">
              <strong class="w-28">Quantity</strong>
              <span class="ml-2">: {{ $header->quantity }}</span>
            </div>
          </div>
          <div>
            <div class="flex">
              <strong class="w-28">Supplier</strong>
              <span class="ml-2">: {{ $header->supplier }}</span>
            </div>
            <div class="flex mt-1">
              <strong class="w-28">Police No</strong>
              <span class="ml-2">: {{ $header->police_no }}</span>
            </div>
            <div class="flex mt-1">
              <strong class="w-28">Analyst</strong>
              <span class="ml-2">: {{ $header->analyst ?? $header->entry_by }}</span>
            </div>
          </div>
        </div>
      </div>

      {{-- ===== ANALYTICAL TABLE ===== --}}
      <table class="w-full border border-gray-400 mb-6">
        <thead class="bg-gray-200">
          <tr>
            <th class="border px-2 py-1">Parameter</th>
            <th class="border px-2 py-1">Result</th>
            <th class="border px-2 py-1">Specification</th>
            <th class="border px-2 py-1">OK</th>
            <th class="border px-2 py-1">NOK</th>
            <th class="border px-2 py-1">Remark</th>
          </tr>
        </thead>
        <tbody>
          @forelse($header->details as $d)
            <tr>
              <td class="border px-2 py-1">{{ $d->parameter }}</td>
              <td class="border px-2 py-1 text-center">{{ $d->result }}</td>
              <td class="border px-2 py-1 text-center">{{ $d->specification }}</td>
              <td class="border px-2 py-1 text-center">{{ in_array($d->status_ok, ['1', 'Y', 'OK']) ? '✓' : '' }}</td>
              <td class="border px-2 py-1 text-center">{{ in_array($d->status_ok, ['N', 'NO', 'NOT OK']) ? '✓' : '' }}</td>
              <td class="border px-2 py-1">{{ $d->remark }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center italic">No data</td>
            </tr>
          @endforelse
        </tbody>
      </table>

      {{-- ===== SIGNATURE ===== --}}
      <div class="grid grid-cols-2 text-center text-xs mt-6 gap-4">
        <div class="space-y-1">
          <div class="font-semibold">Corrected by</div>
          <div class="text-[12px]">(QC Leader)</div>
          <div class="h-6"></div>
          <div class="font-medium">{{ $header->prepared_by ?? '_________________' }}</div>
          <div class="text-xs text-gray-600">
            {{ $header->prepared_date ? \Carbon\Carbon::parse($header->prepared_date)->format('d-m-Y H:i') : '' }}
          </div>

          @if(in_array(strtoupper($header->prepared_status ?? ''), ['REJECT', 'REJECTED']))
            <div class="mt-1 text-[10px] italic text-red-600">Rejected</div>
          @endif
        </div>

        <div class="space-y-1">
          <div class="font-semibold">Approved by</div>
          <div class="text-[12px]">(QC Head)</div>
          <div class="h-6"></div>
          <div class="font-medium">{{ $header->approved_by ?? '_________________' }}</div>
          <div class="text-xs text-gray-600">
            {{ $header->approved_date ? \Carbon\Carbon::parse($header->approved_date)->format('d-m-Y H:i') : '' }}
          </div>

          @if(in_array(strtoupper($header->approved_status ?? ''), ['REJECT', 'REJECTED']))
            <div class="mt-1 text-[10px] italic text-red-600">Rejected</div>
          @endif
        </div>
      </div>

    </div>

    {{-- PAGE BREAK --}}
    <div class="page-break"></div>

    {{-- ================================================= --}}
    {{-- ========== PAGE 2 : REPORT OF ANALYSIS =========== --}}
    {{-- ================================================= --}}
    <div class="bg-white p-6 max-w-4xl mx-auto text-xs">

      <h2 class="text-center text-lg font-bold mb-4 uppercase">
        Report of Analysis
      </h2>

      @php $roa = $header->roa; @endphp

      <table class="w-full border border-black mb-4">
        <tr>
          <td class="border p-2">
            <div class="grid grid-cols-[110px_10px_1fr] gap-y-1">
              <div class="font-bold">Report No</div>
              <div>:</div>
              <div>{{ $roa->report_no }}</div>

              <div class="font-bold">Shipper</div>
              <div>:</div>
              <div>{{ $roa->shipper }}</div>

              <div class="font-bold">Buyer</div>
              <div>:</div>
              <div>{{ $roa->buyer }}</div>

              <div class="font-bold">Date Received</div>
              <div>:</div>
              <div>{{ $roa->date_received ? \Carbon\Carbon::parse($roa->date_received)->format('F d, Y') : '-' }}</div>

              <div class="font-bold">Date Analyzed</div>
              <div>:</div>
              <div>
                {{ $roa->date_analyzed_start ? \Carbon\Carbon::parse($roa->date_analyzed_start)->format('F d, Y') : '-' }}
                up to
                {{ $roa->date_analyzed_end ? \Carbon\Carbon::parse($roa->date_analyzed_end)->format('F d, Y') : '-' }}
              </div>

              <div class="font-bold">Reported</div>
              <div>:</div>
              <div>{{ $roa->date_reported ? \Carbon\Carbon::parse($roa->date_reported)->format('F d, Y') : '-' }}</div>
            </div>
          </td>

        </tr>
      </table>

      {{-- ===== ANALYSIS RESULT HEADER ===== --}}
      <table class="w-full border border-black mb-4">
        <thead>
          <tr class="bg-gray-200">
            <th colspan="2" class="border px-2 py-1 text-left">Analysis Result</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="border px-2 py-1 w-1/3">Lab Sample ID</td>
            <td class="border px-2 py-1">{{ $roa->lab_sample_id }}</td>
          </tr>
          <tr>
            <td class="border px-2 py-1">Customer Sample ID</td>
            <td class="border px-2 py-1">{{ $roa->customer_sample_id }}</td>
          </tr>
          <tr>
            <td class="border px-2 py-1">Seal No</td>
            <td class="border px-2 py-1">{{ $roa->seal_no }}</td>
          </tr>
          <tr>
            <td class="border px-2 py-1">Weight of Received Sample</td>
            <td class="border px-2 py-1">{{ $roa->weight_of_received_sample }}</td>
          </tr>
          <tr>
            <td class="border px-2 py-1">Top Size of Received Sample</td>
            <td class="border px-2 py-1">{{ $roa->top_size_of_received_sample }}</td>
          </tr>
          <tr>
            <td class="border px-2 py-1">Hardgrove Grindability Index</td>
            <td class="border px-2 py-1">{{ $roa->hardgrove_grindability_index }}</td>
          </tr>
        </tbody>
      </table>

      {{-- ===== ROA DETAIL TABLE (CRITICAL FIX) ===== --}}
      <table class="w-full border border-black">
        <thead class="bg-gray-200">
          <tr>
            <th class="border px-2 py-1">Parameter</th>
            <th class="border px-2 py-1">Unit</th>
            <th class="border px-2 py-1">Basis</th>
            <th class="border px-2 py-1">Result</th>
          </tr>
        </thead>
        <tbody>
          @forelse($roa->details as $rd)
            <tr>
              <td class="border px-2 py-1">{{ $rd->parameter }}</td>
              <td class="border px-2 py-1 text-center">{{ $rd->unit }}</td>
              <td class="border px-2 py-1 text-center">{{ $rd->basis }}</td>
              <td class="border px-2 py-1 text-center">{{ $rd->result }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="text-center italic">No ROA detail</td>
            </tr>
          @endforelse
        </tbody>
      </table>

      {{-- ===== ROA SIGNATURE ===== --}}
      <div class="flex justify-end mt-12 mr-4">
        <div class="text-center w-48">
          <div class="font-bold mb-12">Authorized By</div>
          <div class="font-bold">{{ $roa->authorized_by ?? '_____________________' }}</div>
        </div>
      </div>

    </div>

  </div>
@endsection