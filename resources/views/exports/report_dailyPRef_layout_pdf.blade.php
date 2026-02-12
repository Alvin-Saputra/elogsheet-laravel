{{-- fileName: exports/report_dailyPRef_layout_pdf.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>PT.PRISCOLIN Daily Production Refinery Report</title>
    <style>
        body {
            font-size: 8px;
            /* Lebih kecil untuk A3 Landscape */
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

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
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

        .sub-header {
            font-size: 10px;
            font-weight: bold;
            margin: 10px 0 5px 0;
        }

        /* Colors for grouping */
        .bg-yellow-100 {
            background-color: #fffac2;
        }

        .bg-green-100 {
            background-color: #e6ffe6;
        }

        .bg-red-100 {
            background-color: #ffe6e6;
        }

        .bg-blue-100 {
            background-color: #e6f7ff;
        }

        .bg-purple-100 {
            background-color: #f3e6ff;
        }

        .bg-gray-200 {
            background-color: #f5f5f5;
        }

        .font-semibold {
            font-weight: 600;
        }

        .font-bold {
            font-weight: 700;
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
        <h3 style="text-transform:uppercase; font-weight:bold;">DAILY PRODUCTION REFINERY REPORT</h3>
        <p>Date: {{ \Carbon\Carbon::parse($tanggal)->format('d-m-Y') }}</p>
    </div>

    @php
        // Determine the main data source for iteration
        $dataGroups = empty($workCenter) ? $groupedData : [$workCenter => $data];
        $isGrouped = empty($workCenter);
    @endphp

    @foreach ($dataGroups as $wc => $rows)
        @php
            // Get the summary data specific to the current Work Center
            $summaryRows = $isGrouped ? $shiftSummaries[$wc] ?? collect() : $shiftSummaries;
        @endphp

        <div class="text-center" style="margin:15px 0 5px 0;">
            <h4 style="font-weight:bold; font-size:10px;">Work Center: {{ $wc }}</h4>
        </div>

        {{-- ========================================================================= --}}
        {{-- 1. DETAIL PRODUK PER TICKET (RAW MATERIAL, FG, BP) --}}
        {{-- ========================================================================= --}}
        <div class="sub-header text-left">1. Detail Produksi Per Ticket</div>
        <table>
            <thead>
                <tr>
                    <th rowspan="3" class="border p-1">Shift</th>
                    <th rowspan="3" class="border p-1">Oil Type</th>
                    <th colspan="6" class="border p-1 bg-yellow-100">Raw Material</th>
                    <th rowspan="3" class="border p-1">Oil Type</th>
                    <th colspan="6" class="border p-1 bg-green-100">Finished Goods</th>
                    {{-- <th rowspan="3" class="border p-1">Oil Type</th> --}}
                    <th colspan="6" class="border p-1 bg-red-100">By Product</th>
                </tr>
                <tr>

                    <th rowspan="2" class="border p-1 bg-yellow-100">From Tank</th>
                    <th colspan="2" class="border p-1 bg-yellow-100">Awal</th>
                    <th colspan="2" class="border p-1 bg-yellow-100">Akhir</th>
                    <th rowspan="2" class="border p-1 bg-yellow-100">Total</th>


                    <th colspan="2" class="border p-1 bg-green-100">Awal</th>
                    <th colspan="2" class="border p-1 bg-green-100">Akhir</th>
                    <th rowspan="2" class="border p-1 bg-green-100">Total</th>
                    <th rowspan="2" class="border p-1 bg-green-100">To Tank</th>

                    <th colspan="2" class="border p-1 bg-red-100">Awal</th>
                    <th colspan="2" class="border p-1 bg-red-100">Akhir</th>
                    <th rowspan="2" class="border p-1 bg-red-100">Total</th>
                    <th rowspan="2" class="border p-1 bg-red-100">To Tank</th>

                </tr>
                <tr>
                    <th class="border p-1 bg-yellow-100">Jam</th>
                    <th class="border p-1 bg-yellow-100">Flowmeter</th>
                    <th class="border p-1 bg-yellow-100">Jam</th>
                    <th class="border p-1 bg-yellow-100">Flowmeter</th>

                    <th class="border p-1 bg-green-100">Jam</th>
                    <th class="border p-1 bg-green-100">Flowmeter</th>
                    <th class="border p-1 bg-green-100">Jam</th>
                    <th class="border p-1 bg-green-100">Flowmeter</th>

                    <th class="border p-1 bg-red-100">Jam</th>
                    <th class="border p-1 bg-red-100">Flowmeter</th>
                    <th class="border p-1 bg-red-100">Jam</th>
                    <th class="border p-1 bg-red-100">Flowmeter</th>

                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td class="border p-1 text-center font-bold">{{ $row->shift }}</td>

                        {{-- Raw Material --}}
                        <td class="border p-1 text-center font-bold">{{ $row->oil_type_rm ?? '-' }}</td>
                        <td class="border p-1 text-center">{{ $row->cpo_tank ?? '-' }}</td>
                        <td class="border p-1 text-center">{{ $row->oil_type_rm_awal_jam ? \Carbon\Carbon::parse($row->oil_type_rm_awal_jam)->format('H:i') : '-' }}
                        </td>
                        <td class="border p-1 text-right">{{ is_null($row->oil_type_rm_awal_flowmeter) ? '-' : number_format((float) $row->oil_type_rm_awal_flowmeter, 0) }}</td>
                        <td class="border p-1 text-center">{{ $row->oil_type_rm_akhir_jam ? \Carbon\Carbon::parse($row->oil_type_rm_akhir_jam)->format('H:i') : '-' }}
                        </td>
                        <td class="border p-1 text-right">{{ is_null($row->oil_type_rm_akhir_flowmeter) ? '-' : number_format((float) $row->oil_type_rm_akhir_flowmeter, 0) }}
                        </td>
                        <td class="border p-1 text-right font-semibold">{{ is_null($row->oil_type_rm_total) ? '-' : number_format((float) $row->oil_type_rm_total, 0) }}
                        </td>
                        {{-- Finished Goods --}}
                        <td class="border p-1 text-center font-bold">{{ $row->oil_type_fg ?? '-' }}</td>
                        <td class="border p-1 text-center">{{ $row->oil_type_fg_awal_jam ? \Carbon\Carbon::parse($row->oil_type_fg_awal_jam)->format('H:i') : '-' }}
                        </td>
                        <td class="border p-1 text-right">{{ is_null($row->oil_type_fg_awal_flowmeter) ? '-' : number_format((float) $row->oil_type_fg_awal_flowmeter, 0) }}</td>
                        <td class="border p-1 text-center">{{ $row->oil_type_fg_akhir_jam ? \Carbon\Carbon::parse($row->oil_type_fg_akhir_jam)->format('H:i') : '-' }}
                        </td>
                        <td class="border p-1 text-right">{{ is_null($row->oil_type_fg_akhir_flowmeter) ? '-' : number_format((float) $row->oil_type_fg_akhir_flowmeter, 0) }}
                        </td>
                        <td class="border p-1 text-right font-semibold">{{ is_null($row->oil_type_fg_total) ? '-' : number_format((float) $row->oil_type_fg_total, 0) }}
                        </td>
                        <td class="border p-1 text-center">{{ $row->oil_type_fg_to_tank ?? '-' }}</td>

                        {{-- By Product --}}
                        {{-- <td class="border p-1 text-center font-bold">{{ $row->oil_type_bp }}</td> --}}
                        <td class="border p-1 text-center">{{ $row->bp_awal_jam ? \Carbon\Carbon::parse($row->bp_awal_jam)->format('H:i') : '-' }}</td>
                        <td class="border p-1 text-right">{{ is_null($row->bp_awal_flowmeter) ? '-' : number_format((float) $row->bp_awal_flowmeter, 0) }}</td>
                        <td class="border p-1 text-center">{{ $row->bp_akhir_jam ? \Carbon\Carbon::parse($row->bp_akhir_jam)->format('H:i') : '-' }}</td>
                        <td class="border p-1 text-right">{{ is_null($row->bp_akhir_flowmeter) ? '-' : number_format((float) $row->bp_akhir_flowmeter, 0) }}</td>
                        <td class="border p-1 text-right font-semibold">{{ is_null($row->bp_total) ? '-' : number_format((float) $row->bp_total, 0) }}</td>
                        <td class="border p-1 text-center">{{ $row->bp_to_tank ?? '-' }}</td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="21" class="border p-4 text-center text-gray-500">
                            Tidak ada data Raw Material, Finished Goods, atau By Product untuk Work Center ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- ========================================================================= --}}
        {{-- 2. RINGKASAN DATA PER SHIFT (HANYA MUNCUL JIKA ADA SUMMARY DATA) --}}
        {{-- ========================================================================= --}}
        {{-- @if (!$summaryRows->isEmpty()) --}}
        <div class="sub-header text-left" style="margin-top:10px;">Pemakaian Bahan Penolong</div>

        {{-- Chemicals Table --}}
        <table style="width: 100%; float: left;">
            <thead>
                <tr>
                    <th rowspan="2" class="border p-1">Shift</th>
                    <th colspan="3" class="border p-1 bg-blue-100">Bleaching Earth (BE)</th>
                    <th colspan="3" class="border p-1 bg-purple-100">Phosphoric Acid (PA)</th>
                </tr>
                <tr>
                    <th class="border p-1 bg-blue-100">Total Bag</th>
                    <th class="border p-1 bg-blue-100">Lot/Batch No.</th>
                    <th class="border p-1 bg-blue-100">Yield BE (%)</th>
                    <th class="border p-1 bg-purple-100">Total (KG)</th>
                    <th class="border p-1 bg-purple-100">Lot/Batch No.</th>
                    <th class="border p-1 bg-purple-100">Yield PA (%)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($summaryRows as $row)
                    <tr>
                        <td class="border p-1 text-center font-bold">{{ $row->shift }}</td>
                        <td class="border p-1 text-right font-semibold">{{ number_format($row->be_total_bag, 0) }}
                        </td>
                        <td class="border p-1 text-center">{{ $row->be_lot_batch_number ?? 'N/A' }}</td>
                        <td class="border p-1 text-right">{{ number_format($row->be_yield_percent, 2) }}</td>
                        <td class="border p-1 text-right font-semibold">{{ number_format($row->pa_total, 0) }}</td>
                        <td class="border p-1 text-center">{{ $row->pa_lot_batch_number ?? 'N/A' }}</td>
                        <td class="border p-1 text-right">{{ number_format($row->pa_yield_percent, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Utilities Table --}}
        <div class="sub-header text-left" style="margin-top:10px;">Utility Usage</div>

        <table style="width: 100%; float: left;">
            <thead>
                <tr>
                    <th class="border p-1">Shift</th>
                    <th class="border p-1 bg-gray-200">Item Utility</th>
                    <th class="border p-1 bg-gray-200">Total CPO (KG)</th>
                    <th class="border p-1 bg-gray-200">Total Steam (Ton)</th>
                    <th class="border p-1 bg-gray-200">Steam/CPO Ratio</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($summaryRows as $row)
                    <tr>
                        <td class="border p-1 text-center font-bold">{{ $row->shift }}</td>
                        <td class="border p-1 text-center">{{ $row->uu_item ?? 'N/A' }}</td>
                        <td class="border p-1 text-right font-semibold">{{ number_format($row->uu_total_cpo, 0) }}
                        </td>
                        <td class="border p-1 text-right font-semibold">
                            {{ number_format($row->uu_total_steam, 0) }}</td>
                        <td class="border p-1 text-right">{{ number_format($row->uu_steam_cpo, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Clear float after two tables --}}
        <div style="clear: both;"></div>

        {{-- Remarks Table (100% width) --}}
        <table style="margin-top: 5px;">
            <thead>
                <tr>
                    <th class="border p-1" style="width: 10%;">Shift</th>
                    <th class="border p-1" style="width: 90%;">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($summaryRows as $row)
                    <tr>
                        <td class="border p-1 text-center font-bold">{{ $row->shift }}</td>
                        {{-- whitespace-pre-line is implemented via PHP_EOL in Controller. --}}
                        <td class="border p-1 text-left" style="white-space: pre-line;">{{ $row->remarks }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{-- @else
            <p style="text-align:center; color:#555; margin-top: 10px; font-style:italic;">Tidak ada data ringkasan per
                shift untuk Work Center ini.</p>
        @endif --}}

        {{-- Page break only if not the last Work Center and is grouped view --}}
        @if ($isGrouped && !$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

    {{-- Signature section (placed only once at the end of the entire report) --}}
    @php $firstReport = $data->first() ?? $groupedData->first()->first() ?? null; @endphp
    <div class="mt-8">
        <table class="signature-table" width="100%">
            <tr>
                {{-- Prepared By (Shift Leaders) --}}
                @foreach (['1' => 'SHIFT 1', '2' => 'SHIFT 2', '3' => 'SHIFT 3'] as $shiftKey => $label)
                    <td>
                        Prepared By: ({{ $label }})<br><br><br>
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
                {{-- Checked By (Department Head/Manager) --}}
                <td>
                    Checked By:<br><br><br>
                    @if ($firstReport && $firstReport->checked_by)
                        <strong>({{ $firstReport->checked_by }})</strong><br>
                        {{ \Carbon\Carbon::parse($firstReport->checked_date)->format('d-m-Y H:i') }}
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
        Dokumen ini telah disetujui secara elektronik melalui sistem [E-Logsheet],<br>
        sehingga tidak memerlukan tanda tangan asli.
    </div>
</body>

</html>
