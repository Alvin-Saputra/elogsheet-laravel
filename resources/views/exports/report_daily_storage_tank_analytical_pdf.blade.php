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
        <p>Date: {{ \Carbon\Carbon::parse($tanggal)->format('d-m-Y') }}</p>
    </div>


    {{-- This block runs if a specific machine is selected --}}
    <table>
        <thead>
            <tr>
                <th class="border p-1">Tank No.</th>
                <th class="border p-1">Oil Type</th>
                <th class="border p-1">Analysis Date</th>
                <th class="border p-1">Kapasitas Tanki</th>
                <th class="border p-1">Quantity</th>
                <th class="border p-1">Empty Space</th>
                <th class="border p-1">Suhu</th>
                <th class="border p-1">FFA</th>
                <th class="border p-1">Moisture</th>
                <th class="border p-1">Lovibond Color R</th>
                <th class="border p-1">Lovibond Color Y</th>
                <th class="border p-1">IV</th>
                <th class="border p-1">PV</th>
                <th class="border p-1">Slip Melting Point</th>
                <th class="border p-1">Cloud Point</th>
                <th class="border p-1">AnV</th>
                <th class="border p-1">B-Carotene</th>
                <th class="border p-1">P</th>
                <th class="border p-1">DOBI</th>
                <th class="border p-1">Totox</th>
                <th class="border p-1">Odor</th>
                <th class="border p-1">Remark</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $row)
                <tr>
                    <td class="border p-1">{{ $row->tank_no }}</td>
                    <td class="border p-1">{{ $row->oil_type }}</td>
                    <td class="border p-1">{{ $row->transaction_date }}</td>
                    <td class="border p-1">{{ $row->kapasitas_tanki }}</td>
                    <td class="border p-1">{{ $row->quantity }}</td>
                    <td class="border p-1">{{ $row->empty_space }}</td>
                    <td class="border p-1">{{ $row->suhu }}</td>
                    <td class="border p-1">{{ $row->qp_ffa }}</td>
                    <td class="border p-1">{{ $row->qp_moisture }}</td>
                    <td class="border p-1">{{ $row->qp_lovibond_color_r }}</td>
                    <td class="border p-1">{{ $row->qp_lovibond_color_y }}</td>
                    <td class="border p-1">{{ $row->qp_iv }}</td>
                    <td class="border p-1">{{ $row->qp_pv }}</td>
                    <td class="border p-1">{{ $row->qp_slip_melting_point }}</td>
                    <td class="border p-1">{{ $row->qp_cloud_point }}</td>
                    <td class="border p-1">{{ $row->qp_anv }}</td>
                    <td class="border p-1">{{ $row->qp_beta_carotene }}</td>
                    <td class="border p-1">{{ $row->qp_p }}</td>
                    <td class="border p-1">{{ $row->qp_dobi }}</td>
                    <td class="border p-1">{{ $row->qp_totox }}</td>
                    <td class="border p-1">{{ $row->qp_odor }}</td>
                    <td class="border p-1">{{ $row->qp_remark }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>


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
                    Acknowledge by,<br>SPV<br><br><br>
                    @php $first = $data->first(); @endphp
                    @if ($first && $first->approved_by)
                        <strong>({{ $first->approved_by }})</strong><br>
                        {{ \Carbon\Carbon::parse($first->approved_date)->format('d-m-Y H:i') }}
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
