<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Analytical Result Truck - {{ $header->id }}</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <style>
        /* Basic reset & Style from Chemical Report */
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

        /* Header Table */
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

        /* Right Form Box */
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

        /* Info Section */
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

        .info-grid strong {
            display: inline-block;
            width: 100px;
            /* Adjusted width for labels */
        }

        /* Data Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 11px;
            /* Slightly smaller for many columns */
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
            color: #1f2937;
        }

        .empty-row {
            color: #6b7280;
            font-style: italic;
        }

        /* Signatures */
        .signature-row {
            width: 100%;
            border-collapse: collapse;
            margin-top: 24px;
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
            /* Space for signature */
            text-decoration: underline;
        }

        .sig-date {
            font-size: 11px;
            color: #6b7280;
            margin-top: 4px;
        }

        .footer-note {
            margin-top: 12px;
            font-size: 11px;
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
        <div class="section">

            <table class="top-table">
                <tr>
                    <td style="width:18%" class="logo">
                        <img src="{{ public_path('images/KPN Corp.jpg') }}" alt="logo" />
                    </td>

                    <td style="width:57%" class="title">
                        ANALYTICAL RESULT OF<br />INCOMING MATERIAL BY TRUCK
                    </td>

                    <td style="width:20%" class="text-right">
                        <div class="form-box">
                            <div class="form-grid">
                                <span>No. Form</span><span>:</span><span>{{ $header->form_no ?? 'F/QOC-011' }}</span>
                                <span>Issued date</span><span>:</span>
                                <span>
                                    {{ $header->date_issued
                                        ? \Carbon\Carbon::parse($header->date_issued)->format('ymd')
                                        : ($header->entry_date
                                            ? \Carbon\Carbon::parse($header->entry_date)->format('ymd')
                                            : '-') }}
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
                            <div><strong>Arrival Date</strong> :
                                {{ $header->arrival_date ? \Carbon\Carbon::parse($header->arrival_date)->format('Y-m-d H:i') : '-' }}
                            </div>
                            <div style="margin-top:6px;"><strong>Material</strong> : {{ $header->material ?? 'N/A' }}
                            </div>
                            <div style="margin-top:6px;"><strong>Vessel Vehicle</strong> :
                                {{ $header->vessel_vehicle ?? 'N/A' }}</div>
                        </td>
                        <td style="width:50%;">
                            <div><strong>Contract/DO</strong> : {{ $header->contract_do ?? 'N/A' }}</div>
                            <div style="margin-top:6px;"><strong>FFA (Spec)</strong> : {{ $header->ss_ffa ?? 'N/A' }}
                            </div>
                            <div style="margin-top:6px;"><strong>M&I (Spec)</strong> : {{ $header->ss_mni ?? 'N/A' }}
                            </div>
                            <div style="margin-top:6px;"><strong>Others</strong> : {{ $header->others ?? 'N/A' }}</div>
                        </td>
                    </tr>
                </table>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 30px;">No</th>
                        <th rowspan="2" style="width: 80px;">Sampling Date</th>
                        <th rowspan="2">Police No</th>
                        <th colspan="7">Parameter</th>
                        <th rowspan="2">Analyst</th>
                        <th rowspan="2">Remark</th>
                    </tr>
                    <tr>
                        <th>FFA</th>
                        <th>Moist</th>
                        <th>IV</th>
                        <th>DOBI</th>
                        <th>PV</th>
                        <th>Color R</th>
                        <th>Color Y</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($header->details as $detail)
                        <tr>
                            <td>{{ $detail->no ?? '-' }}</td>
                            <td>{{ $detail->sampling_date ? \Carbon\Carbon::parse($detail->sampling_date)->format('Y-m-d') : '-' }}
                            </td>
                            <td class="text-left">{{ $detail->police_no ?? '-' }}</td>
                            <td>{{ $detail->p_ffa ?? '-' }}</td>
                            <td>{{ $detail->p_moisture ?? '-' }}</td>
                            <td>{{ $detail->p_iv ?? '-' }}</td>
                            <td>{{ $detail->p_dobi ?? '-' }}</td>
                            <td>{{ $detail->p_pv ?? '-' }}</td>
                            <td>{{ $detail->p_color_r ?? '-' }}</td>
                            <td>{{ $detail->p_color_y ?? '-' }}</td>
                            <td>{{ $detail->analis ?? '-' }}</td>
                            <td class="text-left">{{ $detail->remarks ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="empty-row">No data found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <table class="signature-row">
                <tr>
                    <td>
                        <div class="sig-role">Done by</div>
                        <div style="font-size: 11px; color: #6b7280;">{{ optional($header->preparedByUser)->roles }}</div>

                        <div class="sig-name">
                            {{ optional($header->preparedByUser)->fullname ??$header->entry_by ?? '_______________________' }}
                        </div>

                        <div class="sig-date">
                            Date:
                            {{ $header->entry_date ? \Carbon\Carbon::parse($header->entry_date)->format('d-m-Y H:i') : '' }}
                        </div>
                    </td>

                    <td>
                        <div class="sig-role">Prepared by</div>
                        <div style="font-size: 11px; color: #6b7280;">{{ optional($header->preparedByUser)->roles }}</div>

                        <div class="sig-name">
                            {{optional($header->preparedByUser)->fullname ??$header->prepared_by ?? '_______________________' }}
                        </div>

                        <div class="sig-date">
                            Date:
                            {{ $header->prepared_date ? \Carbon\Carbon::parse($header->prepared_date)->format('d-m-Y H:i') : '' }}
                        </div>
                    </td>

                    <td>
                        <div class="sig-role">Approved by</div>
                        <div style="font-size: 11px; color: #6b7280;">{{ optional($header->preparedByUser)->roles }}</div>

                        <div class="sig-name">
                            {{ optional($header->preparedByUser)->fullname ??$header->approved_by ?? '_______________________' }}
                        </div>

                        <div class="sig-date">
                            Date:
                            {{ $header->approved_date ? \Carbon\Carbon::parse($header->approved_date)->format('d-m-Y H:i') : '' }}
                        </div>
                    </td>
                </tr>
            </table>

            <div class="footer-note">
                Dokumen ini telah disetujui secara elektronik melalui sistem E-Logsheet,
                sehingga tidak memerlukan tanda tangan asli.
            </div>

        </div>
    </div>
</body>

</html>
