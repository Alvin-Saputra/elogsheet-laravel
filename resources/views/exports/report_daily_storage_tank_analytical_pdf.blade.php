<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Daily Storage Tank Analytical - {{ $tanggal }}</title>
    <style>
        body { 
            font-size: 9px; 
            font-family: sans-serif; 
            margin: 0; 
            padding: 10px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px;
        }
        th, td { 
            border: 1px solid #444; 
            padding: 3px; 
            text-align: center; 
            word-wrap: break-word;
        }
        th { 
            background: #f3f3f3; 
            font-weight: bold;
        }
        
        /* Signature Styles */
        .signature-container {
            width: 100%;
            margin-top: 30px;
        }
        .sig-box {
            float: left;
            width: 25%;
            text-align: center;
        }
        .sig-spacer {
            float: left;
            width: 50%;
        }
        .clear {
            clear: both;
        }
        .footer-note {
            margin-top: 40px;
            text-align: center;
            font-size: 8px;
            font-style: italic;
            color: #666;
        }
        .header-info {
            text-align: right;
            font-size: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

    <div class="header-info">
        <div><strong>Form No.</strong> : F/QCO-001</div>
        <div><strong>Revision</strong> : 00</div>
    </div>

    <h3 style="text-align:center; margin-bottom: 5px;">PT. PRISCOLIN</h3>
    <h4 style="text-align:center; margin-top: 0;">LOGSHEET DAILY STORAGE TANK ANALYTICAL</h4>
    <p style="text-align:center;">
        Date: {{ \Carbon\Carbon::parse($tanggal)->format('d-m-Y') }}
    </p>

    <table>
        <thead>
            <tr>
                <th>Tank No.</th>
                <th>Oil Type</th>
                <th>Analysis Date</th>
                <th>Kapasitas tanki</th>
                <th>Quantity</th>
                <th>Empty Space</th>
                <th>Suhu</th>
                <th>FFA</th>
                <th>Moisture</th>
                <th>Color R</th>
                <th>Color Y</th>
                <th>IV</th>
                <th>PV</th>
                <th>SMP</th>
                <th>Cloud</th>
                <th>AnV</th>
                <th>B-Carotene</th>
                <th>P</th>
                <th>DOBI</th>
                <th>Totox</th>
                <th>Odor</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
            <tr>
                <td>{{ $row->tank_no }}</td>
                <td>{{ $row->oil_type ?? '-' }}</td>
                <td>
                    {{ $row->analysis_date
                        ? \Carbon\Carbon::parse($row->analysis_date)->format('d-m-Y H:i')
                        : '-' }}
                </td>
                <td>{{ number_format($row->capacity ?? 0) }}</td>
                <td>{{ $row->quantity ?? '-' }}</td>
                <td>{{ $row->empty_space ?? '-' }}</td>
                <td>{{ $row->suhu ?? '-' }}</td>
                <td>{{ $row->ffa ?? '-' }}</td>
                <td>{{ $row->moisture ?? '-' }}</td>
                <td>{{ $row->r ?? '-' }}</td>
                <td>{{ $row->y ?? '-' }}</td>
                <td>{{ $row->iv ?? '-' }}</td>
                <td>{{ $row->pv ?? '-' }}</td>
                <td>{{ $row->smp ?? '-' }}</td>
                <td>{{ $row->cloud ?? '-' }}</td>
                <td>{{ $row->anv ?? '-' }}</td>
                <td>{{ $row->bcar ?? '-' }}</td>
                <td>{{ $row->p ?? '-' }}</td>
                <td>{{ $row->dobi ?? '-' }}</td>
                <td>{{ $row->totox ?? '-' }}</td>
                <td>{{ $row->odor ?? '-' }}</td>
                <td>{{ $row->remark ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="22" style="text-align:center;">No data available</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signature-container">
        <div class="sig-box">
            <strong>Prepared By,</strong>
            <br><br><br><br>
            <div style="text-decoration: underline;">
                {{ optional($sign->preparedByUser)->fullname ?? $sign->prepared_by ?? '________________' }}
            </div>
            <div style="font-size: 8px;">
                {{ optional($sign->preparedByUser)->roles ?? 'QC Operator' }}
            </div>
            <small>{{ $sign->prepared_date ? \Carbon\Carbon::parse($sign->prepared_date)->format('d M Y H:i') : '' }}</small>
        </div>

        <div class="sig-spacer"></div>

        <div class="sig-box">
            <strong>Approved By,</strong>
            <br><br><br><br>
            <div style="text-decoration: underline;">
                {{ optional($sign->approvedByUser)->fullname ?? $sign->approved_by ?? '________________' }}
            </div>
            <div style="font-size: 8px;">
                {{ optional($sign->approvedByUser)->roles ?? 'QC Supervisor' }}
            </div>
            <small>{{ $sign->approved_date ? \Carbon\Carbon::parse($sign->approved_date)->format('d M Y H:i') : '' }}</small>
        </div>

        <div class="clear"></div>
    </div>

    <div class="footer-note">
        Dokumen ini telah disetujui secara elektronik melalui sistem E-Logsheet.
    </div>

</body>
</html>