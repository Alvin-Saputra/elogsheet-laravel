<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>PT.PRISCOLIN Daily Quality Refinery 500 MT Production Report</title>
    <style>
        body {
            font-size: 9px;
            font-family: sans-serif;
            margin: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 0.5px solid #444;
            padding: 3px;
            text-align: center;
        }

        th {
            background-color: #f3f3f3;
        }

        .text-center {
            text-align: center;
        }

        .section-table {
            width: 32%;
            float: left;
            margin-right: 1%;
            margin-top: 20px;
        }

        .section-table th,
        .section-table td {
            border: 0.5px solid #444;
            padding: 3px;
        }

        .signature-table {
            width: 100%;
            margin-top: 60px;
        }

        .signature-table td {
            border: none;
            text-align: center;
            height: 80px;
        }

        @page {
            size: A3 landscape;
            margin: 15mm;
        }
    </style>
</head>

<body>
    <div class="text-center">
        <h2 style="text-transform: uppercase; font-weight: bold;">PT. PRISCOLLIN</h2>
        <h3 style="text-transform: uppercase; font-weight: bold;">
            DAILY QUALITY REFINERY PRODUCTION REPORT
        </h3>


        {{-- Jika filter work center --}}
        @if (!empty($workCenter) && isset($refinery))
            <p>{{ $refinery->name ?? '-' }} {{ $refinery->capacity ?? '' }}</p>
        @else
            {{-- Jika ALL, tampilkan semua refinery yang ada di groupedData --}}
            <div style="margin-top: 5px;">
                @foreach ($groupedData as $wc => $rows)
                    @php $firstRow = $rows->first(); @endphp
                    <p>{{ $firstRow->refinery_name ?? $wc }} {{ $firstRow->capacity ?? '' }}</p>
                @endforeach
            </div>
        @endif

        <p>Date: {{ \Carbon\Carbon::parse($tanggal)->format('d-m-Y') }}</p>
    </div>


    <div style="text-align:right; font-size:9px; position:absolute; top:20px; right:20px;">
        <p><strong>No. Form</strong>: {{ $formInfoFirst->form_no ?? '-' }}</p>
        <p><strong>Issue Date</strong>:
            {{ $formInfoFirst && $formInfoFirst->date_issued ? \Carbon\Carbon::parse($formInfoFirst->date_issued)->format('ymd') : '-' }}
        </p>
        <p><strong>Rev. No.</strong>: {{ '0' ?? '-' }}</p>
        <p><strong>Rev. Date.</strong>: {{ '-' }}</p>
        {{-- <p><strong>Rev</strong>: {{ $formInfoLast->revision_no ?? '-' }}</p> --}}
    </div>

    <!-- Tabel utama -->
    @if ($groupedData->isNotEmpty())
        @foreach ($groupedData as $wc => $rows)
            {{-- @php
                $isRef01 = $wc === 'REF-01';
            @endphp --}}
            <table style="margin-bottom: 20px;">
                <thead>
                    <tr>
                        <th rowspan="2">Time</th>
                        <th rowspan="2">From Tank No.</th>
                        <th rowspan="2">Flow Rate</th>
                        <th colspan="10">RAW MATERIAL</th>
                        <th colspan="4">BPO</th>
                        {{-- <th colspan="{{ $isRef01 ? 9 : 10 }}">RRPO</th> --}}
                        <th colspan="10">RRPO</th>
                        <th colspan="3">PFAD</th>
                        <th colspan="2">SPENT EARTH</th>
                        <th rowspan="2">REMARKS</th>
                    </tr>
                    <tr>
                        <th>FFA (%)</th>
                        <th>M&I (%)</th>
                        <th>Dobi</th>
                        <th>IV</th>
                        <th>PV</th>
                        <th>AV</th>
                        <th>Totox</th>
                        <th>Color R</th>
                        <th>Color Y</th>
                        <th>Color B</th>

                        <th>Color R</th>
                        <th>Color Y</th>
                        <th>Color W/B</th>
                        <th>Break Test</th>

                        <th>FFA</th>
                        <th>Moist</th>
                        {{-- @if (!$isRef01) --}}
                            <th>IMP</th>
                        {{-- @endif --}}
                        <th>IV</th>
                        <th>PV</th>
                        <th>Color R</th>
                        <th>Color Y</th>
                        <th>Color W/B</th>
                        <th>To Tank</th>
                        <th>Remarks</th>

                        <th>FFA (%)</th>
                        <th>M&I (%)</th>
                        <th>To Tank</th>
                        <th>M&I (%)</th>
                        <th>OC (%)</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            <td>{{ optional($row->time)->format('H:i') }}</td>
                            <td>{{ $row->rm_tank_source }}</td>
                            <td>{{ $row->rm_flowrate }}</td>
                            <td>{{ $row->rm_ffa }}</td>
                            <td>{{ $row->{'rm_m&i'} }}</td>
                            <td>{{ $row->rm_dobi }}</td>
                            <td>{{ $row->rm_iv }}</td>
                            <td>{{ $row->rm_pv }}</td>
                            <td>{{ $row->rm_av }}</td>
                            <td>{{ $row->rm_totox }}</td>
                            <td>{{ $row->rm_color_r }}</td>
                            <td>{{ $row->rm_color_y }}</td>
                            <td>{{ $row->rm_color_b }}</td>

                            <td>{{ $row->bo_color_r }}</td>
                            <td>{{ $row->bo_color_y }}</td>
                            <td>{{ $row->bo_color_b }}</td>
                            <td>{{ $row->bo_break_test }}</td>

                            <td>{{ $row->fg_ffa }}</td>
                            <td>{{ $row->fg_moist }}</td>
                            {{-- @if (!$isRef01) --}}
                                <td>{{ $row->fg_impurities }}</td>
                            {{-- @endif --}}
                            <td>{{ $row->fg_iv }}</td>
                            <td>{{ $row->fg_pv }}</td>
                            <td>{{ $row->fg_color_r }}</td>
                            <td>{{ $row->fg_color_y }}</td>
                            <td>{{ $row->fg_color_b }}</td>
                            <td>{{ $row->fg_tank_to }}</td>
                            <td>{{ $row->fg_tank_to_others_remarks }}</td>
                            <td>{{ $row->bp_ffa }}</td>
                            <td>{{ $row->{'bp_m&i'} }}</td>
                            <td>{{ $row->bp_to_tank }}</td>
                            <td>{{ $row->{'w_sbe_m&i'} }}</td>
                            <td>{{ $row->w_sbe_qc }}</td>
                            <td>{{ $row->remarks }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @else
        {{-- @php
            $isRef01 = $workCenter === 'REF-01';
        @endphp --}}

        <table style="margin-bottom: 20px;">
            <thead>
                <tr>
                    <th rowspan="2">Time</th>
                    <th rowspan="2">From Tank No.</th>
                    <th rowspan="2">Flow Rate</th>
                    <th colspan="10">RAW MATERIAL</th>
                    <th colspan="4">BPO</th>
                    {{-- <th colspan="{{ $isRef01 ? 9 : 10 }}">RRPO</th> --}}
                    <th colspan="10">RRPO</th>
                    <th colspan="3">PFAD</th>
                    <th colspan="2">SPENT EARTH</th>
                    <th rowspan="2">REMARKS</th>
                </tr>
                <tr>
                    <th>FFA (%)</th>
                    <th>M&I (%)</th>
                    <th>Dobi</th>
                    <th>IV</th>
                    <th>PV</th>
                    <th>AV</th>
                    <th>Totox</th>
                    <th>Color R</th>
                    <th>Color Y</th>
                    <th>Color B</th>

                    <th>Color R</th>
                    <th>Color Y</th>
                    <th>Color W/B</th>
                    <th>Break Test</th>

                    <th>FFA</th>
                    <th>Moist</th>
                    {{-- @if (!$isRef01) --}}
                        <th>IMP</th>
                    {{-- @endif --}}
                    <th>IV</th>
                    <th>PV</th>
                    <th>Color R</th>
                    <th>Color Y</th>
                    <th>Color W/B</th>
                    <th>To Tank</th>
                    <th>Remarks</th>

                    <th>FFA (%)</th>
                    <th>M&I (%)</th>
                    <th>To Tank</th>
                    <th>M&I (%)</th>
                    <th>OC (%)</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($data as $row)
                    <tr>
                        <td>{{ optional($row->time)->format('H:i') }}</td>
                        <td>{{ $row->rm_tank_source }}</td>
                        <td>{{ $row->rm_flowrate }}</td>
                        <td>{{ $row->rm_ffa }}</td>
                        <td>{{ $row->{'rm_m&i'} }}</td>
                        <td>{{ $row->rm_dobi }}</td>
                        <td>{{ $row->rm_iv }}</td>
                        <td>{{ $row->rm_pv }}</td>
                        <td>{{ $row->rm_av }}</td>
                        <td>{{ $row->rm_totox }}</td>
                        <td>{{ $row->rm_color_r }}</td>
                        <td>{{ $row->rm_color_y }}</td>
                        <td>{{ $row->rm_color_b }}</td>

                        <td>{{ $row->bo_color_r }}</td>
                        <td>{{ $row->bo_color_y }}</td>
                        <td>{{ $row->bo_color_b }}</td>
                        <td>{{ $row->bo_break_test }}</td>

                        <td>{{ $row->fg_ffa }}</td>
                        <td>{{ $row->fg_moist }}</td>
                        {{-- @if (!$isRef01) --}}
                            <td>{{ $row->fg_impurities }}</td>
                        {{-- @endif --}}
                        <td>{{ $row->fg_iv }}</td>
                        <td>{{ $row->fg_pv }}</td>
                        <td>{{ $row->fg_color_r }}</td>
                        <td>{{ $row->fg_color_y }}</td>
                        <td>{{ $row->fg_color_b }}</td>
                        <td>{{ $row->fg_tank_to }}</td>
                        <td>{{ $row->fg_tank_to_others_remarks }}</td>
                        <td>{{ $row->bp_ffa }}</td>
                        <td>{{ $row->{'bp_m&i'} }}</td>
                        <td>{{ $row->bp_to_tank }}</td>
                        <td>{{ $row->{'w_sbe_m&i'} }}</td>
                        <td>{{ $row->w_sbe_qc }}</td>
                        <td>{{ $row->remarks }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    @endif
    <!-- Bagian bawah -->
    {{-- <div style="margin-top:20px; display:flex; justify-content:space-between;">
        <!-- Daily Chemical Usage -->
        <table class="section-table">
            <thead>
                <tr>
                    <th colspan="3">Daily Chemical Usage</th>
                </tr>
                <tr>
                    <th>Chemical</th>
                    <th>Amount</th>
                    <th>%</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Bleaching Earth</td>
                    <td>{{ $dailyUsage['bleaching_earth_amount'] ?? '-' }}</td>
                    <td>{{ $dailyUsage['bleaching_earth_percent'] ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Phosphoric Acid</td>
                    <td>{{ $dailyUsage['phosphoric_acid_amount'] ?? '-' }}</td>
                    <td>{{ $dailyUsage['phosphoric_acid_percent'] ?? '-' }}</td>
                </tr>
                <tr>
                    <td>RPO Usage</td>
                    <td colspan="2">{{ $dailyUsage['rpo_usage'] ?? '-' }}</td>
                </tr>
            </tbody>
        </table> --}}

    <!-- Theoretical Yield -->
    {{-- <table class="section-table">
            <thead>
                <tr>
                    <th colspan="2">Theoretical Yield</th>
                </tr>
                <tr>
                    <th>Product</th>
                    <th>%</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>RRPO</td>
                    <td>{{ $yield['rrpo'] ?? '-' }}</td>
                </tr>
                <tr>
                    <td>PFAD</td>
                    <td>{{ $yield['pfad'] ?? '-' }}</td>
                </tr>
                <tr>
                    <td>LOSES</td>
                    <td>{{ $yield['loses'] ?? '-' }}</td>
                </tr>
            </tbody>
        </table> --}}

    <table class="signature-table">
        <tr>
            <td>
                Prepared by,<br><br><br>
                <strong>({{ data_get($lastShift, 'prepared.name', '-') }})</strong><br>
                @php $pdate = data_get($lastShift, 'prepared.date'); @endphp
                {{ $pdate ? \Carbon\Carbon::parse($pdate)->format('d-m-Y H:i') : '-' }}
            </td>
            <td>
                Acknowledged by,<br><br><br>
                <strong>({{ data_get($lastShift, 'acknowledge.name', '-') }})</strong><br>
                @php $adate = data_get($lastShift, 'acknowledge.date'); @endphp
                {{ $adate ? \Carbon\Carbon::parse($adate)->format('d-m-Y H:i') : '-' }}
            </td>
        </tr>
    </table>





    <div style="margin-top:10px; text-align:center; font-size:9px; font-style:italic; color:#555;">
        Dokumen ini telah disetujui secara elektronik melalui sistem [E-Logsheet],
        sehingga tidak memerlukan tanda tangan basah.
    </div>

    </div>
</body>

</html>
