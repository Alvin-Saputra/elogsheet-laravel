<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>PT.PRISCOLIN Deodorizing & Filtration Report</title>
    <style>
        body {
            font-size: 9px;
            font-family: sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #444;
            padding: 3px;
            text-align: center;
        }

        th {
            background-color: #f3f3f3;
        }

        .text-center {
            text-align: center;
        }

        .mt-8 {
            margin-top: 40px;
        }

        .signature-table td {
            border: none;
            text-align: center;
            padding-top: 30px;
        }

        .header-meta {
            text-align: right;
            font-size: 10px;
            line-height: 1.3;
        }

        .note {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
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
        <div><strong>Form No.</strong> : {{ $formInfoFirst->form_no ?? 'F/RFA-010' }}</div>
        <div><strong>Date Issued</strong> :
            {{ $formInfoFirst ? \Carbon\Carbon::parse($formInfoFirst->date_issued)->format('d-m-Y') : '210101' }}</div>
        <div><strong>Revision</strong> : {{ $formInfoLast->revision_no ?? '01' }}</div>
        <div><strong>Rev. Date</strong> :
            {{ $formInfoLast ? \Carbon\Carbon::parse($formInfoLast->revision_date)->format('d-m-Y') : '210901' }}</div>
    </div>

    <div class="text-center" style="margin-bottom:15px;">
        <h2 style="text-transform:uppercase; font-weight:bold;">PT.PRISCOLIN</h2>
        <h3 style="text-transform:uppercase; font-weight:bold;">LOGSHEET DAILY STORAGE TANK ANALYTICAL</h3>
        <p>Date: {{ \Carbon\Carbon::parse($filterTanggal)->format('d-m-Y') }}</p>
    </div>


    {{-- This block runs if a specific machine is selected --}}
    @if ($groupedData->isNotEmpty())
        @foreach ($groupedData as $wc => $rows)
            @php
                $firstRow = $rows->first();
                $workCenter = $firstRow->work_center;
            @endphp

            @if ($workCenter === 'FRAC-02')
                <div class="text-center mb-4">
                    <h2 class="text-sm font-bold uppercase">Fractionation 500</h2>
                </div>
            @else
                <div class="text-center mb-4">
                    <h2 class="text-sm font-bold uppercase">Fractionation 400</h2>
                </div>
            @endif
            <table style="margin-bottom: 20px;">
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
                        <th>M&I</th>
                        <th>IV</th>
                        <th>Colour R</th>
                        <th>Colour Y</th>
                        <th>Colour W</th>
                        <th>Colour B</th>

                        <th>FFA</th>
                        <th>MNI</th>
                        <th>IV</th>
                        <th>Colour R</th>
                        <th>Colour Y</th>
                        <th>Colour W</th>
                        <th>Colour B</th>
                        <th>CP</th>
                        <th>Clarity</th>
                        <th>To Tank</th>

                        <th>FFA</th>
                        <th>MNI</th>
                        <th>IV</th>
                        <th>PV</th>
                        <th>Colour R</th>
                        <th>Colour Y</th>
                        <th>Colour W</th>
                        <th>Colour B</th>
                        <th>To Tank</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            <td>{{ $row->time }}</td>
                            <td>{{ $row->crystalizer }}</td>
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
    @else
        @if ($filterWorkCenter === 'FRAC-02')
            <div class="text-center mb-4">
                <h2 class="text-sm font-bold uppercase">Fractionation 500</h2>
            </div>
        @else
            <div class="text-center mb-4">
                <h2 class="text-lg font-bold uppercase">Fractionation 400</h2>
            </div>
        @endif
        <table style="margin-bottom: 20px;">
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
                    <th>M&I</th>
                    <th>IV</th>
                    <th>Colour R</th>
                    <th>Colour Y</th>
                    <th>Colour W</th>
                    <th>Colour B</th>

                    <th>FFA</th>
                    <th>MNI</th>
                    <th>IV</th>
                    <th>Colour R</th>
                    <th>Colour Y</th>
                    <th>Colour W</th>
                    <th>Colour B</th>
                    <th>CP</th>
                    <th>Clarity</th>
                    <th>To Tank</th>

                    <th>FFA</th>
                    <th>MNI</th>
                    <th>IV</th>
                    <th>PV</th>
                    <th>Colour R</th>
                    <th>Colour Y</th>
                    <th>Colour W</th>
                    <th>Colour B</th>
                    <th>To Tank</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $row)
                    <tr>
                        <td>{{ $row->time }}</td>
                        <td>{{ $row->crystalizer }}</td>
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
    @endif

    {{-- Signature section remains the same --}}
    <div class="mt-8">
        <table class="signature-table" width="100%">
            <tr>
                <td>
                    Prepared by<br>Leader Shift<br><br><br>
                    @php $first = $data->first(); @endphp
                    @if ($first && $first->prepared_by)
                        <strong>({{ $first->prepared_by }})</strong><br>
                        {{ \Carbon\Carbon::parse($first->prepared_date)->format('d-m-Y H:i') }}
                    @else
                        (_________________)
                        <br>
                        -
                    @endif
                </td>
                <td>
                    Acknowledge by,<br>QC Section Head<br><br><br>
                    @php $first = $data->first(); @endphp
                    @if ($first && $first->checked_by)
                        <strong>({{ $first->checked_by }})</strong><br>
                        {{ \Carbon\Carbon::parse($first->checked_date)->format('d-m-Y H:i') }}
                    @else
                        (_________________)
                        <br>
                        -
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="note">
        Dokumen ini telah disetujui secara elektronik melalui sistem [E-Form],<br>
        sehingga tidak memerlukan tanda tangan asli.
    </div>

</body>

</html>
