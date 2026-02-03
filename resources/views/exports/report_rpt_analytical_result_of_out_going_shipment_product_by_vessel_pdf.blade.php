<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Analytical Result Outgoing - {{ $header->id ?? 'Preview' }}</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <style>
        /* GLOBAL STYLES (MATCHING CHEMICAL REPORT) */
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
            padding: 15mm;
            /* Slightly reduced padding to fit the wide table */
            box-sizing: border-box;
        }

        .section {
            background: #fff;
            margin-bottom: 18px;
        }

        /* HEADER SECTION */
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
            line-height: 1.2;
            text-transform: uppercase;
        }

        /* FORM INFO BOX (RIGHT) */
        .form-box {
            font-size: 11px;
            border: 1px solid #000;
            padding: 6px;
            width: 100%;
            box-sizing: border-box;
        }

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

        /* INFO BOX (DETAILS) */
        .info-box {
            border: 1px solid #9ca3af;
            padding: 8px;
            font-size: 12px;
            margin-bottom: 15px;
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

        .info-grid strong {
            display: inline-block;
            width: 110px;
        }

        /* DATA TABLES */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
            /* Smaller font for wide tables */
        }

        .data-table th,
        .data-table td {
            border: 1px solid #9ca3af;
            padding: 5px;
            text-align: center;
            vertical-align: middle;
        }

        .data-table thead {
            background: #e5e7eb;
            font-weight: 600;
            color: #1f2937;
        }

        .table-title {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .empty-row {
            color: #6b7280;
            font-style: italic;
            padding: 10px;
        }

        /* SIGNATURES */
        .signature-row {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
        }

        .signature-row td {
            padding: 12px 8px;
            vertical-align: top;
            width: 33.33%;
        }

        .sig-role {
            font-weight: bold;
            margin-bottom: 4px;
        }

        .sig-name {
            font-weight: 600;
            margin-top: 40px;
            text-decoration: underline;
        }

        .sig-date {
            font-size: 11px;
            color: #6b7280;
            margin-top: 4px;
        }

        .footer-note {
            margin-top: 20px;
            font-size: 10px;
            color: #6b7280;
            font-style: italic;
            text-align: center;
        }

        .text-left {
            text-align: left;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="page">
        <table class="top-table">
            <tr>
                <td style="width:18%" class="logo">
                    <img src="{{ public_path('images/KPN Corp.jpg') }}" alt="logo" />
                    <div style="font-weight:700;margin-top:4px;font-size:12px;">BEKASI</div>
                </td>

                <td style="width:55%" class="title">
                    ANALYTICAL RESULT OUTGOING SHIPMENT<br />PRODUCT BY VESSEL
                </td>

                <td style="width:27%" class="text-right">
                    <div class="form-box">
                        <div class="form-grid">
                            <span>Form No.</span><span>:</span><span>{{ $header->form_no ?? 'F-QOC-009' }}</span>
                            <span>Date Issued</span><span>:</span>
                            <span>{{ $header->form_date_issued ? \Carbon\Carbon::parse($header->form_date_issued)->format('ymd') : '241019' }}</span>
                            <span>Revision</span><span>:</span><span>{{ $header->revision_no ?? '00' }}</span>
                            <span>Rev. Date</span><span>:</span>
                            <span>{{ $header->form_rev_date ? \Carbon\Carbon::parse($header->form_rev_date)->format('ymd') : '00' }}</span>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="info-box">
            <table class="info-grid">
                <tr>
                    <td style="width:50%;">
                        <div><strong>Tanggal</strong> : {{ $header->sampling_date ? \Carbon\Carbon::parse($header->sampling_date)->format('d-m-Y') : '-' }}</div>
                        <div style="margin-top:4px;"><strong>Product Name</strong> : {{ $header->product_name ?? '-' }}</div>
                        <div style="margin-top:4px;"><strong>Quantity</strong> : {{ $header->quantity ?? '-' }}</div>
                    </td>
                    <td style="width:50%;">
                        <div><strong>Shipper</strong> : {{ $header->shipper ?? '-' }}</div>
                        <div style="margin-top:4px;"><strong>Destination</strong> : {{ $header->destination ?? '-' }}</div>
                        <div style="margin-top:4px;"><strong>Vessel Name</strong> : {{ $header->vessel_name ?? '-' }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="table-title">Hasil Analisa Tiap Palka</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th colspan="6">Palka S</th>
                    <th colspan="6">Palka P</th>
                </tr>
                <tr>
                    <th style="width: 8%">No</th>
                    <th>FFA</th>
                    <th>IV</th>
                    <th>Color R</th>
                    <th>PV</th>
                    <th>M&I</th>

                    <th style="width: 8%">No</th>
                    <th>FFA</th>
                    <th>IV</th>
                    <th>Color R</th>
                    <th>PV</th>
                    <th>M&I</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($header->details as $detail)
                    <tr>
                        <td>{{ $detail->palka_s_palka ?? '-' }}</td>
                        <td>{{ $detail->palka_s_ffa ?? '-' }}</td>
                        <td>{{ $detail->palka_s_iv ?? '-' }}</td>
                        <td>{{ $detail->palka_s_colour ?? '-' }}</td>
                        <td>{{ $detail->palka_s_pv ?? '-' }}</td>
                        <td>{{ $detail->palka_s_mni ?? '-' }}</td>

                        <td>{{ $detail->palka_p_palka ?? '-' }}</td>
                        <td>{{ $detail->palka_p_ffa ?? '-' }}</td>
                        <td>{{ $detail->palka_p_iv ?? '-' }}</td>
                        <td>{{ $detail->palka_p_colour ?? '-' }}</td>
                        <td>{{ $detail->palka_p_pv ?? '-' }}</td>
                        <td>{{ $detail->palka_p_mni ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="empty-row">No Palka data found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="table-title" style="margin-top: 10px;">Hasil Analisa Komposit Palka</div>
        <table class="data-table" style="width: 60%;">
            <thead>
                <tr>
                    <th style="width: 50%; text-align: left; padding-left: 15px;">Parameter</th>
                    <th style="width: 50%;">Result</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-left" style="padding-left: 15px;">FFA</td>
                    <td>{{ $header->hasil_analisa_ffa ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="text-left" style="padding-left: 15px;">IV</td>
                    <td>{{ $header->hasil_analisa_iv ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="text-left" style="padding-left: 15px;">Moisture</td>
                    <td>{{ $header->hasil_analisa_moisture ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="text-left" style="padding-left: 15px;">Colour R</td>
                    <td>{{ $header->hasil_analisa_colour ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="text-left" style="padding-left: 15px;">PV</td>
                    <td>{{ $header->hasil_analisa_pv ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="text-left" style="padding-left: 15px;">SMP</td>
                    <td>{{ $header->hasil_analisa_smp ?? '-' }}</td>
                </tr>
            </tbody>
        </table>

        <table class="signature-row">
            <tr>
                <td>
                    <div class="sig-role">Done by</div>
                    <div style="font-size: 11px; color: #6b7280;">{{ optional($header->entriedByUser)->roles ?? '(Operator)' }}</div>
                    
                    <div class="sig-name">
                        {{ optional($header->entriedByUser)->fullname ?? ($header->entry_by ?? '_______________________') }}
                    </div>
                    
                    <div class="sig-date">
                        Date: {{ $header->entry_date ? \Carbon\Carbon::parse($header->entry_date)->format('d-m-Y H:i') : '' }}
                    </div>
                </td>

                <td>
                    <div class="sig-role">Prepared by</div>
                    <div style="font-size: 11px; color: #6b7280;">{{ optional($header->preparedByUser)->roles ?? '(Shift Leader)' }}</div>
                    
                    <div class="sig-name">
                        {{ optional($header->preparedByUser)->fullname ?? ($header->prepared_by ?? '_______________________') }}
                    </div>
                    
                    <div class="sig-date">
                        Date: {{ $header->prepared_date ? \Carbon\Carbon::parse($header->prepared_date)->format('d-m-Y H:i') : '' }}
                    </div>
                </td>

                <td>
                    <div class="sig-role">Approved by</div>
                    <div style="font-size: 11px; color: #6b7280;">{{ optional($header->approvedByUser)->roles ?? '(Section Head)' }}</div>
                    
                    <div class="sig-name">
                        {{ optional($header->approvedByUser)->fullname ?? ($header->approved_by ?? '_______________________') }}
                    </div>
                    
                    <div class="sig-date">
                        Date: {{ $header->approved_date ? \Carbon\Carbon::parse($header->approved_date)->format('d-m-Y H:i') : '' }}
                    </div>
                </td>
            </tr>
        </table>

        <div class="footer-note">
            Dokumen ini telah disetujui secara elektronik melalui sistem [E-Logsheet],
            sehingga tidak memerlukan tanda tangan asli.
        </div>
    </div>
</body>

</html>