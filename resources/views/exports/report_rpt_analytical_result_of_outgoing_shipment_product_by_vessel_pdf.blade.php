<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AROS Product By Vessel - {{ $header->id }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000;
            margin: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td,
        th {
            padding: 6px;
            vertical-align: top;
        }

        .bordered th,
        .bordered td {
            border: 1px solid #222;
        }

        .text-center {
            text-align: center;
        }

        .header-box {
            border: 1px solid #222;
            padding: 8px;
            margin-bottom: 12px;
            font-size: 11px;
        }

        .small {
            font-size: 11px;
        }

        .sig-space {
            height: 55px;
        }

        @page {
            size: A4 landscape;
            margin: 12mm;
        }
    </style>
</head>
<body>
    @php
        $fmt = function ($val, $decimals = 4) {
            if ($val === null || $val === '') {
                return '-';
            }
            return number_format((float) $val, $decimals);
        };
    @endphp

    <table style="margin-bottom: 12px;">
        <tr>
            <td style="width:25%;">
                <div style="font-weight:700; font-size:16px;">PT. PRISCOLIN</div>
                <div>BEKASI</div>
            </td>
            <td style="width:50%; text-align:center;">
                <h2 style="margin:0; font-size:16px; text-transform:uppercase;">
                    Analytical Result of Outgoing Shipment<br>Product by Vessel
                </h2>
            </td>
            <td style="width:25%;">
                <div style="border:1px solid #222; padding:6px; display:inline-block; font-size:11px;">
                    <div><strong>Form No</strong> : {{ $header->form_no ?? 'F/QCO-020' }}</div>
                    <div><strong>Issued Date</strong> : {{ $header->date_issued ? \Carbon\Carbon::parse($header->date_issued)->format('ymd') : '-' }}</div>
                    <div><strong>Rev.</strong> : {{ $header->revision_no ?? '-' }}</div>
                    <div><strong>Rev. Date</strong> : {{ $header->revision_date ? \Carbon\Carbon::parse($header->revision_date)->format('ymd') : '-' }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="header-box">
        <table>
            <tr>
                <td style="width:50%;">
                    <table>
                        <tr><td style="width:120px;"><strong>Sampling Date</strong></td><td>: {{ $header->sampling_date ? \Carbon\Carbon::parse($header->sampling_date)->format('d-m-Y') : '-' }}</td></tr>
                        <tr><td><strong>Product Name</strong></td><td>: {{ $header->product_name ?? '-' }}</td></tr>
                        <tr><td><strong>Quantity</strong></td><td>: {{ $header->quantity ?? '-' }}</td></tr>
                    </table>
                </td>
                <td style="width:50%;">
                    <table>
                        <tr><td style="width:120px;"><strong>Vessel Name</strong></td><td>: {{ $header->vessel_name ?? '-' }}</td></tr>
                        <tr><td><strong>Shipper</strong></td><td>: {{ $header->shipper ?? '-' }}</td></tr>
                        <tr><td><strong>Destination</strong></td><td>: {{ $header->destination ?? '-' }}</td></tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <table class="bordered" style="margin-bottom:12px;">
        <thead>
            <tr>
                <th colspan="12" class="text-center">Hasil Analisa Tiap Palka</th>
            </tr>
            <tr>
                <th colspan="6" class="text-center">Palka S</th>
                <th colspan="6" class="text-center">Palka P</th>
            </tr>
            <tr>
                <th>Palka No</th>
                <th>FFA</th>
                <th>IV</th>
                <th>Colour</th>
                <th>PV</th>
                <th>M&amp;I</th>
                <th>Palka No</th>
                <th>FFA</th>
                <th>IV</th>
                <th>Colour</th>
                <th>PV</th>
                <th>M&amp;I</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($header->details as $detail)
                <tr>
                    <td class="text-center">{{ $fmt($detail->palka_s_palka) }}</td>
                    <td class="text-center">{{ $fmt($detail->palka_s_ffa) }}</td>
                    <td class="text-center">{{ $fmt($detail->palka_s_iv) }}</td>
                    <td class="text-center">{{ $fmt($detail->palka_s_colour) }}</td>
                    <td class="text-center">{{ $fmt($detail->palka_s_pv) }}</td>
                    <td class="text-center">{{ $fmt($detail->palka_s_mni) }}</td>

                    <td class="text-center">{{ $fmt($detail->palka_p_palka) }}</td>
                    <td class="text-center">{{ $fmt($detail->palka_p_ffa) }}</td>
                    <td class="text-center">{{ $fmt($detail->palka_p_iv) }}</td>
                    <td class="text-center">{{ $fmt($detail->palka_p_colour) }}</td>
                    <td class="text-center">{{ $fmt($detail->palka_p_pv) }}</td>
                    <td class="text-center">{{ $fmt($detail->palka_p_mni) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center">No data available</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="bordered" style="margin-bottom:16px;">
        <thead>
            <tr>
                <th colspan="2" class="text-center">Hasil Analisa Komposit Palka</th>
            </tr>
        </thead>
        <tbody>
            <tr><td class="text-center">FFA</td><td class="text-center">{{ $fmt($header->hasil_analisa_ffa) }}</td></tr>
            <tr><td class="text-center">IV</td><td class="text-center">{{ $fmt($header->hasil_analisa_iv) }}</td></tr>
            <tr><td class="text-center">Moisture</td><td class="text-center">{{ $fmt($header->hasil_analisa_moisture) }}</td></tr>
            <tr><td class="text-center">Colour</td><td class="text-center">{{ $fmt($header->hasil_analisa_colour) }}</td></tr>
            <tr><td class="text-center">PV</td><td class="text-center">{{ $fmt($header->hasil_analisa_pv) }}</td></tr>
            <tr><td class="text-center">SMP</td><td class="text-center">{{ $fmt($header->hasil_analisa_smp) }}</td></tr>
        </tbody>
    </table>

    <table>
        <tr>
            <td style="width:33%; text-align:center;">
                <div style="font-weight:700;">Done by</div>
                <div class="sig-space"></div>
                <div>{{ $header->entry_by ?? '____________________' }}</div>
                <div class="small">{{ $header->entry_date ? \Carbon\Carbon::parse($header->entry_date)->format('d-m-Y H:i') : '' }}</div>
            </td>
            <td style="width:33%; text-align:center;">
                <div style="font-weight:700;">Prepared by</div>
                <div class="sig-space"></div>
                <div>{{ $header->prepared_by ?? '____________________' }}</div>
                <div class="small">{{ $header->prepared_date ? \Carbon\Carbon::parse($header->prepared_date)->format('d-m-Y H:i') : '' }}</div>
            </td>
            <td style="width:33%; text-align:center;">
                <div style="font-weight:700;">Approved by</div>
                <div class="sig-space"></div>
                <div>{{ $header->approved_by ?? '____________________' }}</div>
                <div class="small">{{ $header->approved_date ? \Carbon\Carbon::parse($header->approved_date)->format('d-m-Y H:i') : '' }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
