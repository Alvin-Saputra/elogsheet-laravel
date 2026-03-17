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
        <div><strong>Form No.</strong> : {{ $formInfoFirst->form_no ?? 'F/RFA-002' }}</div>
        <div><strong>Date Issued</strong> :
            {{ $formInfoFirst ? \Carbon\Carbon::parse($formInfoFirst->date_issued)->format('d-m-Y') : '210101' }}</div>
        <div><strong>Revision</strong> : {{ $formInfoLast->revision_no ?? '01' }}</div>
        <div><strong>Rev. Date</strong> :
            {{ $formInfoLast ? \Carbon\Carbon::parse($formInfoLast->revision_date)->format('d-m-Y') : '210901' }}</div>
    </div>

    <div class="text-center" style="margin-bottom:15px;">
        <h2 style="text-transform:uppercase; font-weight:bold;">PT.PRISCOLIN</h2>
        <h3 style="text-transform:uppercase; font-weight:bold;">DEODORIZING & FILTRATION LOGSHEET</h3>
        <p>Date: {{ \Carbon\Carbon::parse($tanggal)->format('d-m-Y') }}</p>
    </div>

    @foreach ($groupedData as $rm => $rows)
        <h4 class="text-center" style="font-weight:bold; margin-top: 20px; margin-bottom: 5px;">
            Work Center: {{ $rm }}
        </h4>
        <table>
            <thead>
                <tr>
                    <th rowspan="3">Time</th>
                    <th rowspan="3">Oil Type</th>
                    <th rowspan="2">FIT701</th>
                    <th colspan="2">D 701</th>
                    <th rowspan="2">E702</th>
                    <th colspan="2">Thermopac</th>
                    <th colspan="3">D702</th>
                    <th colspan="2">Sparging</th>
                    <th colspan="1">E 703</th>
                    <th colspan="1">Steam</th>
                    <th rowspan="2">PISH 706</th>
                    <th rowspan="2">TIWH 706</th>
                    <th colspan="3">F702</th>
                    <th rowspan="3">Oil Type FG</th>
                    <th rowspan="2">FIT704</th>
                    <th rowspan="2">E 704</th>
                    <th rowspan="3">Oil Type BP</th>
                    <th rowspan="2">FIT705</th>
                    <th rowspan="2">E 705</th>
                    <th rowspan="3">Clarity</th>
                    <th rowspan="3">Remarks</th>
                </tr>
                <tr>
                    <th>Vacum</th>
                    <th>T D701</th>
                    <th>Inlet</th>
                    <th>Outlet</th>
                    <th>Inlet</th>
                    <th>Outlet</th>
                    <th>Vacum</th>
                    <th>A</th>
                    <th>B</th>
                    <th>Inlet</th>
                    <th>Inlet</th>
                    <th>A</th>
                    <th>B</th>
                    <th>C</th>
                </tr>
                <tr>
                    <th>tph</th>
                    <th>cmHg</th>
                    <th>(°C)</th>
                    <th>(°C)</th>
                    <th>(°C)</th>
                    <th>(°C)</th>
                    <th>(°C)</th>
                    <th>(°C)</th>
                    <th>mbar</th>
                    <th>bar</th>
                    <th>bar</th>
                    <th>(°C)</th>
                    <th>bar</th>
                    <th>bar</th>
                    <th>(°C)</th>
                    <th>bar</th>
                    <th>bar</th>
                    <th>bar</th>
                    <th>tph</th>
                    <th>(°C)</th>
                    <th>tph</th>
                    <th>(°C)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($row->time)->format('H:i') }}</td>
                        <td>{{ $row->oil_type ?? '' }}</td>
                        <td>{{ $row->fit701_bpo ?? '' }}</td>
                        <td>{{ $row->d701_vacum ?? '' }}</td>
                        <td>{{ $row->d701_td701 ?? '' }}</td>
                        <td>{{ $row->e702 ?? '' }}</td>
                        <td>{{ $row->thermopac_inlet ?? '' }}</td>
                        <td>{{ $row->thermopac_outlet ?? '' }}</td>
                        <td>{{ $row->d702_inlet ?? '' }}</td>
                        <td>{{ $row->d702_outlet ?? '' }}</td>
                        <td>{{ $row->d702_vacum ?? '' }}</td>
                        <td>{{ $row->sparging_a ?? '' }}</td>
                        <td>{{ $row->sparging_b ?? '' }}</td>
                        <td>{{ $row->e730_inlet ?? '' }}</td>
                        <td>{{ $row->steam_inlet ?? '' }}</td>
                        <td>{{ $row->pish_706 ?? '' }}</td>
                        <td>{{ $row->tiwh_706 ?? '' }}</td>
                        <td>{{ $row->f702_a ?? '' }}</td>
                        <td>{{ $row->f702_b ?? '' }}</td>
                        <td>{{ $row->f702_c ?? '' }}</td>
                        <td>{{ $row->oil_type_fg ?? '' }}</td>
                        <td>{{ $row->fit704_rpo ?? '' }}</td>
                        <td>{{ $row->e704 ?? '' }}</td>
                        <td>{{ $row->oil_type_bp ?? '' }}</td>
                        <td>{{ $row->fit_705_pfad ?? '' }}</td>
                        <td>{{ $row->e705 ?? '' }}</td>
                        <td>{{ $row->clarity ?? '' }}</td>
                        <td>{{ $row->remarks ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Jangan beri page break pada tabel terakhir --}}
        @if (!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

    {{-- Signature section remains the same --}}
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
                    @php
                        // Mencari data pertama yang memiliki nilai checked_by (bukan dummy object)
                        $validData = collect($groupedData)
                            ->flatten()
                            ->filter(function ($item) {
                                return isset($item->checked_by) && !empty($item->checked_by);
                            })
                            ->last();
                    @endphp

                    @if ($validData)
                        <strong>({{ $validData->checked_by }})</strong><br>
                        {{ \Carbon\Carbon::parse($validData->checked_date)->format('d-m-Y H:i') }}
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
