<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>PT.PRISCOLIN Daily Production Fractionation Report</title>
    <style>
        body {
            font-size: 8px;
            font-family: sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th,
        td {
            border: 1px solid #444;
            padding: 3px;
            text-align: center;
            vertical-align: top;
        }

        th {
            background-color: #f3f3f3;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .mt-8 {
            margin-top: 20px;
        }

        .signature-table td {
            border: none;
            text-align: center;
            padding-top: 20px;
        }

        .header-meta {
            text-align: right;
            font-size: 9px;
            line-height: 1.2;
            margin-bottom: 15px;
        }

        .note {
            margin-top: 15px;
            text-align: center;
            font-size: 8px;
            font-style: italic;
            color: #555;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <div class="header-meta">
        <div><strong>Form No.</strong> : {{ $formInfoFirst->form_no ?? 'F/RFA-XXX' }}</div>
        <div><strong>Date Issued</strong> :
            {{ $formInfoFirst ? optional($formInfoFirst->date_issued)->format('d-m-Y') : 'YY-MM-DD' }}</div>
        <div><strong>Revision</strong> : {{ $formInfoLast ? sprintf('%02d', $formInfoLast->revision_no) : '01' }}</div>
        <div><strong>Rev. Date</strong> :
            {{ $formInfoLast ? optional($formInfoLast->revision_date)->format('d-m-Y') : 'YY-MM-DD' }}</div>
    </div>

    <div class="text-center" style="margin-bottom:15px;">
        <h2 style="text-transform:uppercase; font-weight:bold;">PT.PRISCOLIN</h2>
        <h3 style="text-transform:uppercase; font-weight:bold;">DAILY PRODUCTION FRACTIONATION REPORT</h3>
        <p>Date: {{ \Carbon\Carbon::parse($tanggal)->format('d-m-Y') }}</p>
    </div>

    @php
        $dataGroups = empty($workCenter) ? $groupedData : [$workCenter => $data];
        $isGrouped = empty($workCenter);
    @endphp

    @foreach ($dataGroups as $wc => $rows)
        <div class="text-center" style="margin:15px 0 5px 0;">
            <h4 style="font-weight:bold; font-size:10px;">Work Center: {{ $wc }}</h4>
        </div>

        @include('rpt_daily_production.fractionation._table_dailyPFra', ['rows' => $rows])

        @if ($isGrouped && !$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

    @php $first = $data->first() ?? $groupedData->first()->first() ?? null; @endphp

    <div class="mt-8">
        <table class="signature-table" width="100%">
            <tr>
                @foreach (['1' => 'SHIFT 1', '2' => 'SHIFT 2', '3' => 'SHIFT 3'] as $key => $label)
                    <td>
                        Prepared by: ({{ $label }})<br><br><br>
                        @if (isset($signatures[$key]))
                            <strong>({{ $signatures[$key]['name'] }})</strong><br>
                            {{ !empty($signatures[$key]['date']) ? \Carbon\Carbon::parse($signatures[$key]['date'])->format('d-m-Y H:i') : '-' }}
                        @else
                            (_________________)<br>
                            -
                        @endif
                    </td>
                @endforeach
                <td>
                    Checked by:<br><br><br>
                    @if ($first && $first->checked_by)
                        <strong>({{ $first->checked_by }})</strong><br>
                        {{ !empty($first->checked_date) ? \Carbon\Carbon::parse($first->checked_date)->format('d-m-Y H:i') : '-' }}
                    @else
                        (_________________)<br>
                        -
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="note">
        Dokumen ini telah disetujui secara elektronik melalui sistem [E-Logsheet],<br>
        sehingga tidak memerlukan tanda tangan asli.
    </div>
</body>

</html>
