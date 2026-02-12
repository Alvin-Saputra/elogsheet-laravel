<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Form Transfer - {{ $tanggal }}</title>
    <style>
        body {
            font-size: 10px;
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
            padding: 4px;
            text-align: center;
        }

        th {
            background-color: #f3f3f3;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .mt-8 {
            margin-top: 40px;
        }

        .signature-table td {
            border: none;
            text-align: center;
            padding-top: 20px;
        }

        .header-meta {
            text-align: right;
            font-size: 10px;
        }

        .info-table td {
            border: none;
            padding: 2px 4px;
            text-align: left;
        }

        .note {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            font-style: italic;
            color: #555;
        }

        .status-approved {
            color: green;
        }

        .status-pending {
            color: orange;
        }

        .page-break {
            page-break-after: always;
        }

        .transfer-section {
            margin-bottom: 30px;
        }
    </style>
</head>

<body>
    @php
        if (!function_exists('formatDecimal')) {
            function formatDecimal($value)
            {
                if ($value === null || $value === '') {
                    return '-';
                }
                if (!is_numeric($value)) {
                    return $value;
                }
                $formatted = number_format((float) $value, 3, '.', '');
                $formatted = rtrim(rtrim($formatted, '0'), '.');
                return $formatted === '' ? '0' : $formatted;
            }
        }
    @endphp

    @forelse ($transfers as $index => $transfer)
        <div class="transfer-section {{ !$loop->last ? 'page-break' : '' }}">
            {{-- Header Meta --}}
            <div class="header-meta" style="text-align:right; font-size:10px; line-height:1.3;">
                <div><strong>Form No.</strong> : {{ $transfer->form_no ?? '' }}</div>
                <div><strong>Date Issued</strong> :
                    {{ $transfer && $transfer->date_issued ? \Carbon\Carbon::parse($transfer->date_issued)->format('ymd') : '' }}
                </div>
                <div><strong>Revision</strong> :
                    {{ $transfer ? sprintf('%02d', $transfer->revision_no ?? 0) : '' }}
                </div>
                <div><strong>Rev. Date</strong> :
                    {{ $transfer && $transfer->revision_date ? \Carbon\Carbon::parse($transfer->revision_date)->format('ymd') : '' }}
                </div>
            </div>

            {{-- Title --}}
            <div class="text-center" style="margin-bottom:15px;">
                <h2 style="text-transform:uppercase; font-weight:bold;">PT.PRISCOLIN</h2>
                <h3 style="text-transform:uppercase; font-weight:bold;">FORM TRANSFER</h3>
            </div>

            {{-- Transfer Info --}}
            <table class="info-table" style="margin-bottom:20px;">
                <tr>
                    <td style="width:15%;"><strong>From Dept</strong></td>
                    <td style="width:35%;">: {{ $transfer->from_dept ?? '-' }}</td>
                    <td style="width:15%;"><strong>To Dept</strong></td>
                    <td style="width:35%;">: {{ $transfer->to_dept ?? '-' }}</td>
                </tr>
                <tr>
                    <td><strong>Transaction Date</strong></td>
                    <td>: {{ $transfer && $transfer->transaction_date ? \Carbon\Carbon::parse($transfer->transaction_date)->format('d-m-Y') : '-' }}
                    </td>
                    <td><strong>Company</strong></td>
                    <td>: {{ $transfer->company ?? '-' }}</td>
                </tr>
                <tr>
                    <td><strong>Plant</strong></td>
                    <td>: {{ $transfer->plant ?? '-' }}</td>
                    <td></td>
                    <td></td>
                </tr>
            </table>

            {{-- Details Table --}}
            <table>
                <thead>
                    <tr>
                        <th rowspan="3">No</th>
                        <th rowspan="3">Oil Type</th>
                        <th rowspan="3">Quantity</th>
                        <th colspan="3">From</th>
                        <th colspan="3">To</th>
                        <th colspan="8">Quality Parameters</th>
                        <th rowspan="3">Remark</th>
                    </tr>
                    <tr>
                        <th rowspan="2">Storage Tank</th>
                        <th rowspan="2">Refinery / Fracination</th>
                        <th rowspan="2">Other</th>
                        <th rowspan="2">Storage Tank</th>
                        <th rowspan="2">Refinery / Fracination</th>
                        <th rowspan="2">Auto Filling Tank</th>
                        <th rowspan="2">M&I (%)</th>
                        <th rowspan="2">FFA (%)</th>
                        <th colspan="2">Lov. Color</th>
                        <th>CP / TEMP</th>
                        <th>SMP</th>
                        <th>PV</th>
                        <th>IV</th>
                    </tr>
                    <tr>
                        <th>R</th>
                        <th>Y</th>
                        <th>oC</th>
                        <th>oC</th>
                        <th>Me/O2</th>
                        <th>gr12/100gr</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transfer->details as $detailIndex => $detail)
                        <tr>
                            <td>{{ $detailIndex + 1 }}</td>
                            <td>{{ $detail->oil_type ?? '-' }}</td>
                            <td>{{ formatDecimal($detail->quantity) }}</td>
                            <td>{{ $detail->from_storage_tank_no ?? '-' }}</td>
                            <td>{{ $detail->from_refinery_fractionation ?? '-' }}</td>
                            <td>{{ $detail->from_other ?? '-' }}</td>
                            <td>{{ $detail->to_storage_tank_no ?? '-' }}</td>
                            <td>{{ $detail->to_refinery_fractionation ?? '-' }}</td>
                            <td>{{ $detail->to_auto_filling_tank ?? '-' }}</td>
                            <td>{{ formatDecimal($detail->quality_m_and_i) }}</td>
                            <td>{{ formatDecimal($detail->quality_ffa) }}</td>
                            <td>{{ formatDecimal($detail->quality_lov_color_r) }}</td>
                            <td>{{ formatDecimal($detail->quality_lov_color_y) }}</td>
                            <td>{{ formatDecimal($detail->quality_cp_temp) }}</td>
                            <td>{{ formatDecimal($detail->quality_smp) }}</td>
                            <td>{{ formatDecimal($detail->quality_pv) }}</td>
                            <td>{{ formatDecimal($detail->quality_iv) }}</td>
                            <td>{{ $detail->remark ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="19" style="text-align:center;">No data available</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Signature Blocks --}}
            <div class="mt-8">
                <table class="signature-table" width="100%">
                    <tr>
                        <td style="width:50%;">
                            <div style="border:1px solid #444; padding:10px; min-height:60px;">
                                <strong>Prepared By (Lead / Lead QC)</strong><br><br>
                                @if ($transfer->prepared_by)
                                    {{ $transfer->prepared_by }}<br>
                                    {{ $transfer->prepared_date ? \Carbon\Carbon::parse($transfer->prepared_date)->format('d-m-Y H:i') : '' }}<br>
                                    @if (strtoupper($transfer->prepared_status) === 'APPROVED')
                                        <span class="status-approved">{{ $transfer->prepared_status }}</span>
                                    @else
                                        <span class="status-pending">{{ $transfer->prepared_status ?? 'PENDING' }}</span>
                                    @endif
                                @else
                                    (Pending)
                                @endif
                            </div>
                        </td>
                        <td style="width:50%;">
                            <div style="border:1px solid #444; padding:10px; min-height:60px;">
                                <strong>Approved By (Manager / Admin)</strong><br><br>
                                @if ($transfer->approved_by)
                                    {{ $transfer->approved_by }}<br>
                                    {{ $transfer->approved_date ? \Carbon\Carbon::parse($transfer->approved_date)->format('d-m-Y H:i') : '' }}<br>
                                    @if (strtoupper($transfer->approved_status) === 'APPROVED')
                                        <span class="status-approved">{{ $transfer->approved_status }}</span>
                                    @else
                                        <span class="status-pending">{{ $transfer->approved_status ?? 'PENDING' }}</span>
                                    @endif
                                @else
                                    (Pending)
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="note">
                Dokumen ini telah disetujui secara elektronik melalui sistem [E-Logsheet],
                sehingga tidak memerlukan tanda tangan asli.
            </div>
        </div>
    @empty
        <div class="text-center" style="padding: 50px;">
            <p>No Form Transfer records found for {{ $tanggal }}</p>
        </div>
    @endforelse
</body>

</html>
