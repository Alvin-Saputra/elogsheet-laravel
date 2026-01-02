<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Document</title>

    <style>
        body {
            background: #ffffff;
            font-family: Arial, Helvetica, sans-serif;
        }

        .container {
            background: #ffffff;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .1);
            max-width: 900px;
            margin: auto;
        }

        h2 {
            font-size: 24px;
            margin-bottom: 16px;
            color: #1f2937;
        }

        .text-blue {

            color: #2563eb;
        }

        /* Info table (replaces info-grid) */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            table-layout: fixed;
        }

        .info-table td {
            vertical-align: top;
            padding: 6px 8px;
            border: none;
            /* no box border for info cells */
            font-size: 14px;
        }

        .info-label {
            display: block;
            font-size: 13px;
            color: #4b5563;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 14px;
            color: #111827;
        }

        /* Keep main data tables style */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        table.data-table th,
        table.data-table td {
            border: 1px solid #9ca3af;
            padding: 6px 8px;
            text-align: center;
            font-size: 13px;
        }

        table.data-table thead {
            background: #e5e7eb;
            color: #1f2937;
        }

        .empty-row {
            color: #6b7280;
            font-style: italic;
        }

        /* Responsive: stack info cells on small screens */
        @media (max-width: 640px) {
            .info-table td {
                display: block;
                width: 100%;
            }
        }

        .approval-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
            text-align: center;
        }

        .approval-table td {
            width: 33.33%;
            padding: 12px 8px;
            font-size: 12px;
            vertical-align: top;
        }

        .approval-role {
            font-weight: bold;
        }

        .approval-sign {
            margin: 40px 0 10px 0;
        }

        .approval-date {
            font-size: 11px;
            margin-top: 4px;
        }

        .approval-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 11px;
            color: #4b5563;
            font-style: italic;
        }

        .report-title {
            margin-bottom: 16px;
            font-size: 24px;
            color: #1f2937;
        }
    </style>
</head>

<body>
    <div class="container">

        <h2 class="report-title">
            Report ID:
            <span class="text-blue">{{ $header->id }}</span>
        </h2>


        <!-- Info table: 3 columns per row (label + value stacked) -->
        <table class="info-table" role="presentation">
            <tr>
                <td>
                    <span class="info-label">Arrival Date:</span>
                    <span class="info-value">{{ $header->arrival->format('Y-m-d H:i') }}</span>
                </td>

                <td>
                    <span class="info-label">Material:</span>
                    <span class="info-value">{{ $header->material ?? 'N/A' }}</span>
                </td>

                <td>
                    <span class="info-label">Quantity:</span>
                    <span class="info-value">{{ $header->quantity ?? 'N/A' }}</span>
                </td>
            </tr>

            <tr>
                <td>
                    <span class="info-label">Ship's Name:</span>
                    <span class="info-value">{{ $header->shipName ?? 'N/A' }}</span>
                </td>

                <td>
                    <span class="info-label">Contract/Do Number:</span>
                    <span class="info-value">{{ $header->contract_do_nomor ?? 'N/A' }}</span>
                </td>

                <td>
                    <span class="info-label">FFA:</span>
                    <span class="info-value">{{ $header->ffa ?? 'N/A' }}</span>
                </td>
            </tr>

            <tr>
                <td>
                    <span class="info-label">M&I:</span>
                    <span class="info-value">{{ $header->mni ?? 'N/A' }}</span>
                </td>

                <td>
                    <span class="info-label">DOBI:</span>
                    <span class="info-value">{{ $header->dobi ?? 'N/A' }}</span>
                </td>

                <td>
                    <span class="info-label">Others:</span>
                    <span class="info-value">{{ $header->others ?? 'N/A' }}</span>
                </td>
            </tr>
        </table>
        <br>
        <br>
        <!-- Main palka analysis table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th colspan="15">Hasil Analisa Tiap Palka</th>
                </tr>
                <tr>
                    <th colspan="5">Palka S</th>
                    <th colspan="5">Palka C</th>
                    <th colspan="5">Palka P</th>
                </tr>
                <tr>
                    <th>Palka No</th>
                    <th>FFA</th>
                    <th>IV</th>
                    <th>DOBI</th>
                    <th>M&amp;I</th>
                    <th>Palka No</th>
                    <th>FFA</th>
                    <th>IV</th>
                    <th>DOBI</th>
                    <th>M&amp;I</th>
                    <th>Palka No</th>
                    <th>FFA</th>
                    <th>IV</th>
                    <th>DOBI</th>
                    <th>M&amp;I</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($header->details as $detail)
                    <tr>
                        <td>{{ $detail->palka_s_no ?? '-' }}</td>
                        <td>{{ $detail->palka_s_ffa ?? '-' }}</td>
                        <td>{{ $detail->palka_s_iv ?? '-' }}</td>
                        <td>{{ $detail->palka_s_dobi ?? '-' }}</td>
                        <td>{{ $detail->palka_s_mni ?? '-' }}</td>

                        <td>{{ $detail->palka_c_no ?? '-' }}</td>
                        <td>{{ $detail->palka_c_ffa ?? '-' }}</td>
                        <td>{{ $detail->palka_c_iv ?? '-' }}</td>
                        <td>{{ $detail->palka_c_dobi ?? '-' }}</td>
                        <td>{{ $detail->palka_c_mni ?? '-' }}</td>

                        <td>{{ $detail->palka_p_no ?? '-' }}</td>
                        <td>{{ $detail->palka_p_ffa ?? '-' }}</td>
                        <td>{{ $detail->palka_p_iv ?? '-' }}</td>
                        <td>{{ $detail->palka_p_dobi ?? '-' }}</td>
                        <td>{{ $detail->palka_p_mni ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="15" class="empty-row">No Palka data found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Composite analysis -->
        <table class="data-table">
            <thead>
                <tr>
                    <th colspan="2">Hasil Analisa Komposit Palka</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>FFA</td>
                    <td>{{ $header->hasil_analisa_ffa }}</td>
                </tr>
                <tr>
                    <td>IV</td>
                    <td>{{ $header->hasil_analisa_iv }}</td>
                </tr>
                <tr>
                    <td>Moisture</td>
                    <td>{{ $header->hasil_analisa_moisture }}</td>
                </tr>
                <tr>
                    <td>Dobi</td>
                    <td>{{ $header->hasil_analisa_dobi }}</td>
                </tr>
                <tr>
                    <td>PV</td>
                    <td>{{ $header->hasil_analisa_pv }}</td>
                </tr>
                <tr>
                    <td>AnV</td>
                    <td>{{ $header->hasil_analisa_anv }}</td>
                </tr>
            </tbody>
        </table>


        <table class="approval-table">
            <tr>
                <td>
                    <div class="approval-role">Done by</div>
                    <div>(Operator)</div>

                    <div class="approval-sign">
                        ( {{ $header->entry_by ?? '_______________________' }} )
                    </div>

                    <div class="approval-date">
                        Date:
                        {{ $header->entry_date ? \Carbon\Carbon::parse($header->entry_date)->format('d-m-Y H:i') : '' }}
                    </div>
                </td>

                <td>
                    <div class="approval-role">Prepared by</div>
                    <div>(Shift Leader)</div>

                    <div class="approval-sign">
                        ( {{ $header->prepared_by ?? '_______________________' }} )
                    </div>

                    <div class="approval-date">
                        Date:
                        {{ $header->prepared_date ? \Carbon\Carbon::parse($header->prepared_date)->format('d-m-Y H:i') : '' }}
                    </div>
                </td>

                <td>
                    <div class="approval-role">Approved by</div>
                    <div>(Section Head)</div>

                    <div class="approval-sign">
                        ( {{ $header->approved_by ?? '_______________________' }} )
                    </div>

                    <div class="approval-date">
                        Date:
                        {{ $header->approved_date ? \Carbon\Carbon::parse($header->approved_date)->format('d-m-Y H:i') : '' }}
                    </div>
                </td>
            </tr>
        </table>

        <div class="approval-footer">
            Dokumen ini telah disetujui secara elektronik melalui sistem [E-Logsheet],
            sehingga tidak memerlukan tanda tangan asli.
        </div>

    </div>



</body>

</html>
