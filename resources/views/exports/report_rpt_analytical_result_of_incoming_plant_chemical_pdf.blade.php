<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>AROIP Chemical - {{ $header->id }}</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <style>
    /* Basic reset */
    html,
    body {
      margin: 0;
      padding: 0;
      font-family: Arial, Helvetica, sans-serif;
      background: #fff;
      color: #111;
    }

    .page {
      width: 210mm;
      margin: 0 auto;
      padding: 18mm;
      box-sizing: border-box;
    }

    .section {
      background: #fff;
      border-radius: 0;
      padding: 0;
      margin-bottom: 18px;
    }

    .top-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 12px;
    }

    .top-table td {
      vertical-align: top;
    }

    .logo {
      text-align: center;
    }

    .logo img {
      height: 56px;
    }

    .title {
      text-align: center;
      font-weight: 700;
      font-size: 16px;
      line-height: 1.05;
      text-transform: uppercase;
    }

    .form-box {
      font-size: 11px;
      border: 1px solid #000;
      padding: 6px;
      width: 100%;
      box-sizing: border-box;
    }

    /* grid inside form-box */
    .form-grid {
      display: grid;
      grid-template-columns: 70px 8px 1fr;
      gap: 2px;
      align-items: start;
      font-size: 11px;
    }

    .form-grid span:first-child {
      font-weight: 600;
    }

    .info-box {
      border: 1px solid #9ca3af;
      padding: 8px;
      font-size: 12px;
      margin-bottom: 10px;
    }

    .info-grid {
      width: 100%;
      border-collapse: collapse;
      font-size: 12px;
    }

    .info-grid td {
      padding: 4px 6px;
      vertical-align: top;
    }

    .data-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 10px;
      font-size: 12px;
    }

    .data-table th,
    .data-table td {
      border: 1px solid #9ca3af;
      padding: 6px;
      text-align: center;
      vertical-align: middle;
    }

    .data-table thead {
      background: #e5e7eb;
      font-weight: 600;
    }

    .small {
      font-size: 11px;
      color: #6b7280;
    }

    .signature-row {
      width: 100%;
      border-collapse: collapse;
      margin-top: 18px;
      text-align: center;
      font-size: 12px;
    }

    .signature-row td {
      padding: 12px 8px;
      vertical-align: top;
    }

    .sig-name {
      font-weight: 600;
      margin-top: 6px;
    }

    .sig-date {
      font-size: 11px;
      color: #6b7280;
      margin-top: 4px;
    }

    /* COA page */
    .coa-title {
      text-align: center;
      font-weight: 700;
      font-size: 16px;
      margin: 6px 0 12px 0;
      text-transform: uppercase;
    }

    .box-black {
      border: 1px solid #000;
    }

    .coa-grid {
      width: 100%;
      border-collapse: collapse;
      font-size: 12px;
    }

    .coa-grid td {
      padding: 6px;
      vertical-align: top;
    }

    .coa-params {
      width: 100%;
      border-collapse: collapse;
      margin-top: 6px;
      font-size: 12px;
    }

    .coa-params th,
    .coa-params td {
      border: 1px solid #000;
      padding: 6px;
      text-align: center;
    }

    .coa-params thead {
      background: #f3f4f6;
      font-weight: 600;
    }

    .right-block {
      display: flex;
      justify-content: flex-end;
    }

    .coa-issue {
      width: 160px;
      text-align: center;
    }

    /* page break */
    .page-break {
      page-break-before: always;
    }

    /* small helpers */
    .text-left {
      text-align: left;
    }

    .text-center {
      text-align: center;
    }

    .muted {
      color: #6b7280;
      font-size: 11px;
    }

    /* checkmark */
    .check {
      font-weight: 700;
    }
  </style>
</head>

<body>
  <div class="page">
    @php
      $displayDate = $header->date ?? $header->entry_date ?? null;
    @endphp

    <!-- PAGE 1 : ANALYTICAL RESULT -->
    <div class="section">

      <table class="top-table">
        <tr>
          <td style="width:18%" class="logo">
            <img src="{{ public_path('images/KPN Corp.jpg') }}" alt="logo" />
            <div style="font-weight:700;margin-top:4px;font-size:12px;">BEKASI</div>
          </td>

          <td style="width:62%" class="title">
            ANALYTICAL RESULT OF INCOMING PLANT<br />CHEMICAL / INGREDIENT
          </td>

          <td style="width:20%" class="text-right">
            <div class="form-box">
              <div class="form-grid">
                <span>No. Form</span><span>:</span><span>{{ $header->form_no ?? 'F/QOC-011' }}</span>
                <span>Issued date</span><span>:</span>
                <span>
                  {{ $header->date_issued
  ? \Carbon\Carbon::parse($header->date_issued)->format('ymd')
  : ($header->entry_date ? \Carbon\Carbon::parse($header->entry_date)->format('ymd') : '-') }}
                </span>
                <span>Rev</span><span>:</span><span>{{ $header->revision_no ? str_pad($header->revision_no, 2, '0', STR_PAD_LEFT) : '00' }}</span>
                <span>Rev date</span><span>:</span>
                <span>{{ $header->revision_date ? \Carbon\Carbon::parse($header->revision_date)->format('ymd') : '-' }}</span>
              </div>
            </div>
          </td>
        </tr>
      </table>

      <div class="info-box">
        <table class="info-grid">
          <tr>
            <td style="width:50%;">
              <div><strong style="display:inline-block;width:86px">Date</strong> :
                {{ $displayDate ? \Carbon\Carbon::parse($displayDate)->format('d/m/Y') : '-' }}</div>
              <div style="margin-top:6px;"><strong style="display:inline-block;width:86px">Material</strong> :
                {{ $header->material ?? '-' }}</div>
              <div style="margin-top:6px;"><strong style="display:inline-block;width:86px">Quantity</strong> :
                {{ $header->quantity ?? '-' }}</div>
              <div style="margin-top:6px;"><strong style="display:inline-block;width:86px">Analyst</strong> :
                {{ $header->analyst ?? $header->entry_by ?? '-' }}</div>
            </td>
            <td style="width:50%;">
              <div><strong style="display:inline-block;width:86px">No Ref CoA</strong> :
                {{ $header->no_ref_coa ?? '-' }}</div>
              <div style="margin-top:6px;"><strong style="display:inline-block;width:86px">Supplier</strong> :
                {{ $header->supplier ?? '-' }}</div>
              <div style="margin-top:6px;"><strong style="display:inline-block;width:86px">Police No.</strong> :
                {{ $header->police_no ?? '-' }}</div>
              <div style="margin-top:6px;"><strong style="display:inline-block;width:86px">Batch/Lot</strong> :
                {{ $header->batch_lot ?? '-' }}</div>
              <div style="margin-top:6px;"><strong style="display:inline-block;width:86px">Exp Date</strong> :
                {{ $header->exp_date ? \Carbon\Carbon::parse($header->exp_date)->format('d/m/Y') : '-' }}</div>
            </td>
          </tr>
        </table>
      </div>

      <table class="data-table">
        <thead>
          <tr>
            <th>Parameter</th>
            <th>Min Result</th>
            <th>Max Result</th>
            <th>Min Spec</th>
            <th>Max Spec</th>
            <th>OK</th>
            <th>Not OK</th>
            <th>Remark</th>
          </tr>
        </thead>
        <tbody>
          @if($header->details && $header->details->count())
            @foreach($header->details as $detail)
              <tr>
                <td class="text-left">{{ $detail->parameter ?? '-' }}</td>
                <td>{{ $detail->result_min ?? '-' }}</td>
                <td>{{ $detail->result_max ?? '-' }}</td>
                <td>{{ $detail->specification_min ?? '-' }}</td>
                <td>{{ $detail->specification_max ?? '-' }}</td>
                <td>@if(in_array(strtoupper((string) $detail->status_ok), ['1', 'Y', 'OK'])) <span class="check">&#10003;</span>
                @endif</td>
                <td>@if(in_array(strtoupper((string) $detail->status_ok), ['N', 'NOT OK', 'NO'])) <span class="check">&#10003;</span>
                @endif</td>
                <td class="text-left">{{ $detail->remark ?? '-' }}</td>
              </tr>
            @endforeach
          @else
            <tr>
              <td colspan="8" class="small">No analytical detail found</td>
            </tr>
          @endif
        </tbody>
      </table>

      <table class="signature-row">
        <tr>
          <td>
            <div class="sig-role">Done by</div>
            <div class="muted">(Operator)</div>
            <div class="sig-name">({{ $header->entry_by ?? '_______________________' }})</div>
            <div class="sig-date">Date:
              {{ $header->entry_date ? \Carbon\Carbon::parse($header->entry_date)->format('d-m-Y H:i') : '' }}</div>
          </td>
          <td>
            <div class="sig-role">Prepared by</div>
            <div class="muted">(Shift Leader)</div>
            <div class="sig-name">({{ $header->prepared_by ?? '_______________________' }})</div>
            <div class="sig-date">Date:
              {{ $header->prepared_date ? \Carbon\Carbon::parse($header->prepared_date)->format('d-m-Y H:i') : '' }}
            </div>
            @if(in_array(strtoupper($header->prepared_status ?? ''), ['REJECT', 'REJECTED']))
              <div style="margin-top:6px;color:#b91c1c;font-style:italic;font-size:11px;">Rejected</div>
            @endif
          </td>
          <td>
            <div class="sig-role">Approved by</div>
            <div class="muted">(QC Head)</div>
            <div class="sig-name">({{ $header->approved_by ?? '_______________________' }})</div>
            <div class="sig-date">Date:
              {{ $header->approved_date ? \Carbon\Carbon::parse($header->approved_date)->format('d-m-Y H:i') : '' }}
            </div>
            @if(in_array(strtoupper($header->approved_status ?? ''), ['REJECT', 'REJECTED']))
              <div style="margin-top:6px;color:#b91c1c;font-style:italic;font-size:11px;">Rejected</div>
            @endif
          </td>
        </tr>
      </table>

      <div style="margin-top:12px;font-size:11px;color:#6b7280;font-style:italic;text-align:center;">
        Dokumen ini telah disetujui secara elektronik melalui sistem E-Logsheet, sehingga tidak memerlukan tanda tangan
        asli.
      </div>

    </div>

    <!-- PAGE BREAK -->
    <div class="page-break"></div>

    <!-- PAGE 2 : CERTIFICATE OF ANALYSIS -->
    <div class="section">
      <div class="coa-title">Certificate of Analysis</div>

      @php $coa = $header->coa ?? null; @endphp

      <table class="box-black coa-grid">
        <tr>
          <td style="width:50%;padding:8px; vertical-align:top;">
            <div><strong>Product :</strong> {{ $coa->product ?? $header->material ?? '-' }}</div>
            <div style="margin-top:6px;"><strong>Grade :</strong> {{ $coa->grade ?? '-' }}</div>
            <div style="margin-top:6px;"><strong>Packing :</strong> {{ $coa->packing ?? '-' }}</div>
            <div style="margin-top:6px;"><strong>Quantity :</strong> {{ $coa->quantity ?? $header->quantity ?? '-' }}
            </div>
            <div style="margin-top:6px;"><strong>No. Doc :</strong> {{ $coa->no_doc ?? $header->no_ref_coa ?? '-' }}
            </div>
          </td>
          <td style="width:50%;padding:8px; vertical-align:top;">
            <div><strong>Tanggal Pengiriman :</strong>
              {{ $coa && $coa->tanggal_pengiriman ? \Carbon\Carbon::parse($coa->tanggal_pengiriman)->format('d/m/Y') : '-' }}
            </div>
            <div style="margin-top:6px;"><strong>Vehicle :</strong> {{ $coa->vehicle ?? $header->police_no ?? '-' }}
            </div>
            <div style="margin-top:6px;"><strong>Lot No :</strong> {{ $coa->lot_no ?? $header->batch_lot ?? '-' }}</div>
            <div style="margin-top:6px;"><strong>Production Date :</strong>
              {{ $coa && $coa->production_date ? \Carbon\Carbon::parse($coa->production_date)->format('d/m/Y') : '-' }}
            </div>
            <div style="margin-top:6px;"><strong>Expired Date :</strong>
              {{ $coa && $coa->expired_date ? \Carbon\Carbon::parse($coa->expired_date)->format('d/m/Y') : ($header->exp_date ? \Carbon\Carbon::parse($header->exp_date)->format('d/m/Y') : '-') }}
            </div>
          </td>
        </tr>
      </table>

      <table class="coa-params">
        <thead>
          <tr>
            <th>PARAMETERS</th>
            <th>ACTUAL</th>
            <th>STANDARD</th>
            <th>METHOD</th>
          </tr>
        </thead>
        <tbody>
          @if($coa && $coa->details && $coa->details->count())
            @foreach($coa->details as $cd)
              <tr>
                <td class="text-left">{{ $cd->parameter ?? '-' }}</td>
                <td>{{ $cd->actual_min ?? '' }}{{ $cd->actual_max ? ' - ' . $cd->actual_max : '' }}</td>
                <td>{{ $cd->standard_min ?? '' }}{{ $cd->standard_max ? ' - ' . $cd->standard_max : '' }}</td>
                <td>{{ $cd->method ?? '-' }}</td>
              </tr>
            @endforeach
          @else
            @if($header->details && $header->details->count())
              @foreach($header->details as $d)
                <tr>
                  <td class="text-left">{{ $d->parameter ?? '-' }}</td>
                  <td>{{ $d->result_min ?? '' }}{{ $d->result_max ? ' - ' . $d->result_max : '' }}</td>
                  <td>{{ $d->specification_min ?? '' }}{{ $d->specification_max ? ' - ' . $d->specification_max : '' }}</td>
                  <td>-</td>
                </tr>
              @endforeach
            @else
              <tr>
                <td colspan="4" class="small">No COA data found</td>
              </tr>
            @endif
          @endif
        </tbody>
      </table>

      <div style="display:flex; justify-content:flex-end; margin-top:28px;">
        <div style="width:170px; text-align:center;">
          <div style="font-size:13px; font-weight:600;">Issue by</div>
          <div style="height:52px;"></div>
          <div style="font-weight:700;">{{ $coa->issue_by ?? $header->approved_by ?? '_________________' }}</div>
          <div style="font-size:11px;color:#6b7280;">Head of Quality Assurance</div>
        </div>
      </div>

    </div>

  </div>
</body>

</html>