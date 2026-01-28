<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PT. PRISCOLIN Daily Quality Composite Fractionation Report</title>
    <style>
        body { font-size: 9px; font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #444; padding: 3px; text-align: center; }
        th { background-color: #f3f3f3; }
        .text-center { text-align: center; }
        .mt-8 { margin-top: 40px; }
        .signature-table td { border: none; text-align: center; padding-top: 30px; }
        .header-meta { text-align: right; font-size: 10px; line-height: 1.3; }
        .note { margin-top: 20px; text-align: center; font-size: 9px; font-style: italic; color: #555; }
    </style>
</head>

<body>

{{-- ================= FORM META ================= --}}
<div class="header-meta">
    <div><strong>Form No.</strong> : {{ $formInfoFirst->form_no ?? 'F/QCO-003' }}</div>
    <div><strong>Date Issued</strong> :
        {{ optional($formInfoFirst?->date_issued)->format('d-m-Y') ?? '-' }}
    </div>
    <div><strong>Revision</strong> : {{ $formInfoLast->revision_no ?? '01' }}</div>
    <div><strong>Rev. Date</strong> :
        {{ optional($formInfoLast?->revision_date)->format('d-m-Y') ?? '-' }}
    </div>
</div>

{{-- ================= HEADER ================= --}}
<div class="text-center" style="margin-bottom:15px;">
    <h2 style="font-weight:bold;">PT. PRISCOLIN</h2>
    <h3 style="font-weight:bold;">
        LOGSHEET DAILY QUALITY COMPOSITE FRACTIONATION
    </h3>
    <p>
        <strong>Operational Date:</strong>
        {{ \Carbon\Carbon::parse($filterTanggal)->format('d-m-Y') }} 08:00 –
        {{ \Carbon\Carbon::parse($filterTanggal)->addDay()->format('d-m-Y') }} 07:59
    </p>
</div>

{{-- ================= DATA ================= --}}
@foreach ($groupedData as $wc => $rows)

@php
    $workCenter = $rows->first()->work_center ?? '';
@endphp

<div class="text-center mb-4">
    <h3 style="font-weight:bold;">
        {{ $workCenter === 'FRAC-02' ? 'Fractionation 500' : 'Fractionation 400' }}
    </h3>
</div>

<table>
    <thead>
        <tr>
            <th rowspan="2">Time</th>
            <th rowspan="2">Crystalizer</th>
            <th colspan="6">RBDPO</th>
            <th colspan="10">OLEIN</th>
            <th colspan="9">STEARIN</th>
            <th rowspan="2">Remarks</th>
        </tr>
        <tr>
            <th>M&I</th><th>IV</th><th>R</th><th>Y</th><th>W</th><th>B</th>
            <th>FFA</th><th>MNI</th><th>IV</th><th>R</th><th>Y</th><th>W</th><th>B</th><th>CP</th><th>Clarity</th><th>To Tank</th>
            <th>FFA</th><th>MNI</th><th>IV</th><th>PV</th><th>R</th><th>Y</th><th>W</th><th>B</th><th>To Tank</th>
        </tr>
    </thead>

    <tbody>
    @foreach ($rows as $row)
        <tr>
            <td>{{ $row->time }}</td>
            <td>{{ $row->crystalizer ?? '-' }}</td>

            <td>{{ $row->rm_mni }}</td>
            <td>{{ $row->rm_iv }}</td>
            <td>{{ $row->rm_color_r }}</td>
            <td>{{ $row->rm_color_y }}</td>
            <td>{{ $row->rm_color_w }}</td>
            <td>{{ $row->rm_color_b }}</td>

            <td>{{ $row->fg_ffa }}</td>
            <td>{{ $row->fg_mni }}</td>
            <td>{{ $row->fg_iv }}</td>
            <td>{{ $row->fg_color_r }}</td>
            <td>{{ $row->fg_color_y }}</td>
            <td>{{ $row->fg_color_w }}</td>
            <td>{{ $row->fg_color_b }}</td>
            <td>{{ $row->fg_cp }}</td>
            <td>{{ $row->fg_clarity }}</td>
            <td>{{ $row->fg_to_tank }}</td>

            <td>{{ $row->bp_ffa }}</td>
            <td>{{ $row->bp_mni }}</td>
            <td>{{ $row->bp_iv }}</td>
            <td>{{ $row->bp_pv }}</td>
            <td>{{ $row->bp_color_r }}</td>
            <td>{{ $row->bp_color_y }}</td>
            <td>{{ $row->bp_color_w }}</td>
            <td>{{ $row->bp_color_b }}</td>
            <td>{{ $row->bp_to_tank }}</td>

            <td>{{ $row->remarks }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

@endforeach

{{-- ================= SIGNATURE ================= --}}
<div class="mt-8">
<table class="signature-table" width="100%">
<tr>
<td>
    Prepared By<br>Leader Shift<br><br><br>
    @if ($sign && $sign->prepared_by)
        <strong>({{ $sign->prepared_by }})</strong><br>
        {{ \Carbon\Carbon::parse($sign->prepared_date)->format('d-m-Y H:i') }}
    @else
        (_________________)<br>-
    @endif
</td>

<td>
    Approved By<br>QC Section Head<br><br><br>
    @if ($sign && $sign->checked_by)
        <strong>({{ $sign->checked_by }})</strong><br>
        {{ \Carbon\Carbon::parse($sign->checked_date)->format('d-m-Y H:i') }}
    @else
        (_________________)<br>-
    @endif
</td>
</tr>
</table>
</div>

<div class="note">
    Data disajikan berdasarkan <strong>hari operasional (08.00 – 07.59)</strong>.<br>
    Dokumen ini disetujui secara elektronik melalui <strong>E-Logsheet</strong>.
</div>

</body>
</html>
