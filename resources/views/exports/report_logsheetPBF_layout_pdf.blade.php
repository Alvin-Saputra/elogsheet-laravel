<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>PT.PRISCOLIN Pretreatment Report</title>
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
        <div><strong>Form No.</strong> : {{ $formInfoFirst->form_no ?? 'F/RFA-002' }}</div>
        <div><strong>Date Issued</strong> :
            {{ $formInfoFirst ? \Carbon\Carbon::parse($formInfoFirst->date_issued)->format('d-m-Y') : '210101' }}</div>
        <div><strong>Revision</strong> : {{ $formInfoLast->revision_no ?? '01' }}</div>
        <div><strong>Rev. Date</strong> :
            {{ $formInfoLast ? \Carbon\Carbon::parse($formInfoLast->revision_date)->format('d-m-Y') : '210901' }}</div>
    </div>

    <div class="text-center" style="margin-bottom:15px;">
        <h2 style="text-transform:uppercase; font-weight:bold;">PT.PRISCOLIN</h2>
        <h3 style="text-transform:uppercase; font-weight:bold;">PRETREATMENT, BLEACHING & FILTRATION LOGSHEET</h3>
        <p>Date: {{ \Carbon\Carbon::parse($tanggal)->format('d-m-Y') }}</p>
    </div>

    @if (!empty($refineryMachine))
        {{-- This block runs if a specific machine is selected --}}
        <h4 class="text-md font-bold mt-6 mb-2">
            Work Center: {{ $refineryMachine }}
        </h4>
        <table>
            <thead>
                <tr>
                    <th rowspan="2">Time</th>
                    <th colspan="1">FIT001</th>
                    <th colspan="1">E001A</th>
                    <th colspan="1">F001/2</th>
                    <th colspan="1">H3PO4</th>
                    <th colspan="1">BE</th>
                    <th colspan="4">Bleacher (B602)</th>
                    <th colspan="3">Pump (P602)</th>
                    <th colspan="3">Niagara Filter</th>
                    <th colspan="3">Filter Bag</th>
                    <th colspan="2">Catridge Filter</th>
                    <th rowspan="2">Clarity</th>
                    <th rowspan="2">Remarks</th>
                </tr>
                <tr>
                    <th>CPO</th>
                    <th>T Inlet CPO</th>
                    <th>Str.</th>
                    <th>0.05-0.08</th>
                    <th>0.6-1.5</th>
                    <th>Vacum</th>
                    <th>T Inlet</th>
                    <th>T B-602</th>
                    <th>Spurge</th>
                    <th>P(A)</th>
                    <th>P(B)</th>
                    <th>P(C)</th>
                    <th>F-601</th>
                    <th>F-602</th>
                    <th>F-603</th>
                    <th>F604A</th>
                    <th>F604B</th>
                    <th>F604C</th>
                    <th>F605A</th>
                    <th>F605B</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $row)
                    <tr>
                        <td>{{ optional($row->time)->format('H:i') }}</td>
                        <td>{{ $row->pt_fit001 }}</td>
                        <td>{{ $row->pt_e001a_inlet }}</td>
                        <td>{{ $row->pt_f0012 }}</td>
                        <td>{{ $row->pt_h3po4 }}</td>
                        <td>{{ $row->pt_be }}</td>
                        <td>{{ $row->bl_vacum }}</td>
                        <td>{{ $row->bl_t_inlet }}</td>
                        <td>{{ $row->bl_t_b602 }}</td>
                        <td>{{ $row->bl_spurge }}</td>
                        <td>{{ $row->p_a }}</td>
                        <td>{{ $row->p_b }}</td>
                        <td>{{ $row->p_c }}</td>
                        <td>{{ $row->fn_f601 }}</td>
                        <td>{{ $row->fn_f602 }}</td>
                        <td>{{ $row->fn_f603 }}</td>
                        <td>{{ $row->fb_604a }}</td>
                        <td>{{ $row->fb_604b }}</td>
                        <td>{{ $row->fb_604c }}</td>
                        <td>{{ $row->fc_605a }}</td>
                        <td>{{ $row->fc_605b }}</td>
                        <td>{{ $row->clarity }}</td>
                        <td>{{ $row->remarks }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        {{-- This block runs if NO specific machine is selected, grouping all of them --}}
        @foreach ($groupedData as $rm => $rows)
            <div class="text-center" style="margin:15px 0;">
                <h4 style="font-weight:bold;">Work Center: {{ $rm }}</h4>
            </div>
            <table>
                <thead>
                    <tr>
                        <th rowspan="3">Time</th>
                        <th rowspan="3">Oil Type</th>
                        <th rowspan="3">FIT001</th>
                        <th rowspan="1">E001A</th>
                        <th rowspan="1">F001/2</th>
                        <th rowspan="1">H<sub>3</sub>PO<sub>4</sub></th>
                        <th rowspan="1">BE</th>
                        <th colspan="4">Bleacher(B602)</th>
                        <th colspan="3">Pump(P602)</th>
                        <th colspan="3">Niagara Filter</th>
                        <th colspan="3">Filter Bag</th>
                        <th colspan="2">Catridge Filter</th>
                        <th rowspan="3">Clarity</th>
                        <th rowspan="3">Remarks</th>
                    </tr>
                    <tr>
                        <th>Inlet</th>
                        <th>Str.</th>
                        <th>0.03-0.05</th>
                        <th>0.6-1.5</th>
                        <th rowspan="1">Vacum</th>
                        <th rowspan="1">T Inlet</th>
                        <th rowspan="1">T B-602</th>
                        <th rowspan="1">Spurge</th>
                        <th rowspan="1">A</th>
                        <th rowspan="1">B</th>
                        <th rowspan="1">C</th>
                        <th rowspan="1">F-601</th>
                        <th rowspan="1">F-602</th>
                        <th rowspan="1">F-603</th>
                        <th rowspan="1">F604A</th>
                        <th rowspan="1">F604B</th>
                        <th rowspan="1">F604C</th>
                        <th rowspan="1">F605A</th>
                        <th rowspan="1">F605B</th>

                    </tr>
                    <tr>
                        <th>(°C)</th>
                        <th>bar</th>
                        <th>%</th>
                        <th>%</th>
                        <th>mmHg</th>
                        <th>(°C)</th>
                        <th>(°C)</th>
                        <th>bar</th>
                        <th>bar</th>
                        <th>bar</th>
                        <th>bar</th>
                        <th>bar</th>
                        <th>bar</th>
                        <th>bar</th>
                        <th>bar</th>
                        <th>bar</th>
                        <th>bar</th>
                        <th>bar</th>
                        <th>bar</th>

                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            <td>{{ optional($row->time)->format('H:i') }}</td>

                            <td>{{ $row->oil_type }}</td>
                            <td>{{ $row->pt_fit001 }}</td>
                            <td>{{ $row->pt_e001a_inlet }}</td>
                            <td>{{ $row->pt_f0012 }}</td>
                            <td>{{ $row->pt_h3po4 }}</td>
                            <td>{{ $row->pt_be }}</td>
                            <td>{{ $row->bl_vacum }}</td>
                            <td>{{ $row->bl_t_inlet }}</td>
                            <td>{{ $row->bl_t_b602 }}</td>
                            <td>{{ $row->bl_spurge }}</td>
                            <td>{{ $row->p_a }}</td>
                            <td>{{ $row->p_b }}</td>
                            <td>{{ $row->p_c }}</td>
                            <td>{{ $row->fn_f601 }}</td>
                            <td>{{ $row->fn_f602 }}</td>
                            <td>{{ $row->fn_f603 }}</td>
                            <td>{{ $row->fb_604a }}</td>
                            <td>{{ $row->fb_604b }}</td>
                            <td>{{ $row->fb_604c }}</td>
                            <td>{{ $row->fc_605a }}</td>
                            <td>{{ $row->fc_605b }}</td>
                            <td>{{ $row->clarity }}</td>
                            <td>{{ $row->remarks }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if (!$loop->last)
                <div class="page-break"></div>
            @endif
        @endforeach
    @endif

    <div class="mt-8">
        <table class="signature-table" width="100%">
            <tr>
                @foreach (['shift1', 'shift2', 'shift3'] as $shiftKey)
                    <td>
                        Shift {{ substr($shiftKey, -1) }}<br><br><br>
                        @if (isset($signatures[$shiftKey]))
                            <strong>({{ $signatures[$shiftKey]['name'] }})</strong><br>
                            {{ \Carbon\Carbon::parse($signatures[$shiftKey]['date'])->format('d-m-Y H:i') }}
                        @else
                            (_________________)
                            <br>
                            -
                        @endif
                    </td>
                @endforeach
                <td>
                    Checked By<br><br><br>
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
