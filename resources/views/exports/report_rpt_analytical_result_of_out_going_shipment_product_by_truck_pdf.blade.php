<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AROS Product By Truck - {{ $header->id }}</title>
    <style>
        /* Base */
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000;
            margin: 12px;
        }

        table { width: 100%; border-collapse: collapse; }

        td, th { padding: 6px; vertical-align: top; }

        .bordered th, .bordered td { border: 1px solid #222; }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: 700; }
        .uppercase { text-transform: uppercase; }
        .mb-4 { margin-bottom: 12px; }
        .small { font-size: 11px; }

        /* Header area */
        .logo { height: 46px; }
        .header-box { border: 1px solid #222; padding: 8px; margin-bottom: 12px; font-size: 11px; }

        /* Analytical table */
        .analytical-table th, .analytical-table td { border: 1px solid #222; padding: 6px; font-size: 11px; }
        .analytical-table thead th { background: #efefef; text-align: center; font-weight: 600; }

        /* small helpers */
        .muted { color: #666; font-size: 10px; }

        /* page break helper */
        .page-break { page-break-before: always; }

        /* Signatures */
        .sig-space { height: 60px; }

        /* Force landscape when dompdf respects container; controller already set landscape */
        @page { size: A4 landscape; margin: 12mm; }
    </style>
</head>
<body>
    @php
        $displayDate = $header->loading_date ?? $header->entry_date ?? null;
        $details = $header->details ?? collect([]);
    @endphp

    {{-- TOP HEADER --}}
    <table class="mb-4">
        <tr>
            <td style="width:25%; text-align:left; vertical-align: middle;">
                {{-- optional logo: ensure file exists in public/images; adjust filename if needed --}}
                @if(file_exists(public_path('images/KPN Corp.jpg')))
                    <img src="{{ public_path('images/KPN Corp.jpg') }}" alt="Logo" class="logo">
                @endif
                <div class="font-bold">PT. PRISCOLIN</div>
                <div class="muted">BEKASI</div>
            </td>

            <td style="width:50%; text-align:center; vertical-align: middle;">
                <h2 class="uppercase" style="margin:0; font-size:16px;">Analytical Result of Out Going Shipment<br>Product by Truck</h2>
            </td>

            <td style="width:25%; text-align:left; vertical-align: middle;">
                <div style="border:1px solid #222; padding:6px; display: inline-block; font-size:11px;">
                    <div><strong>Form No</strong> : {{ $header->form_no ?? 'F/QCO-013' }}</div>
                    <div><strong>Issued Date</strong> : {{ $header->entry_date ? \Carbon\Carbon::parse($header->entry_date)->format('ymd') : '-' }}</div>
                    <div><strong>Rev.</strong> : {{ $header->revision_no ?? '-' }}</div>
                    <div><strong>Rev. Date</strong> : {{ $header->revision_date ? \Carbon\Carbon::parse($header->revision_date)->format('ymd') : '-' }}</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- INFO BOX --}}
    <div class="header-box">
        <table>
            <tr>
                <td style="width:50%;">
                    <table>
                        <tr>
                            <td style="width:120px;"><strong>Loading Date</strong></td>
                            <td>: {{ $displayDate ? \Carbon\Carbon::parse($displayDate)->format('d M Y H:i') : '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Product Name</strong></td>
                            <td>: {{ $header->product_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Quantity</strong></td>
                            <td>: {{ $header->quantity ?? ($header->qty ?? '-') }}</td>
                        </tr>
                    </table>
                </td>

                <td style="width:50%;">
                    <table>
                        <tr>
                            <td style="width:120px;"><strong>Ship's Name</strong></td>
                            <td>: {{ $header->ships_name ?? $header->ship_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Destination</strong></td>
                            <td>: {{ $header->destination ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Load Port</strong></td>
                            <td>: {{ $header->load_port ?? '-' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    {{-- ANALYTICAL TABLE --}}
    <table class="analytical-table mb-4">
        <thead>
            <tr>
                <th>Ship's Tank</th>
                <th>No. Police</th>
                <th>FFA %</th>
                <th>M&amp;I %</th>
                <th>IV</th>
                <th>Lovibond Red</th>
                <th>Lovibond Yellow</th>
                <th>PV</th>
                <th>Other</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            @forelse($details as $d)
                @php
                    // helper fallback keys (same logic as preview_layout)
                    $firstValue = function ($obj, $keys) {
                        foreach ($keys as $k) {
                            if (is_object($obj) && isset($obj->$k) && $obj->$k !== null && $obj->$k !== '') return $obj->$k;
                            if (is_array($obj) && array_key_exists($k, $obj) && $obj[$k] !== null && $obj[$k] !== '') return $obj[$k];
                        }
                        return null;
                    };
                    $toFloat = function ($v) {
                        if ($v === null || $v === '') return null;
                        $s = str_replace([',',' '], ['', ''], (string)$v);
                        return is_numeric($s) ? (float)$s : null;
                    };
                    $fmt = function ($val, $decimals = 3) use ($toFloat) {
                        $n = $toFloat($val);
                        return $n !== null ? number_format($n, $decimals) : '-';
                    };

                    $shipTank = $firstValue($d, ['ships_tank','ship_tank','ship_tank_no','tank']);
                    $noPolice = $firstValue($d, ['no_police','police_no','police']);
                    $ffaRaw   = $firstValue($d, ['ffa','ffa_percent','ffa_pct']);
                    $miRaw    = $firstValue($d, ['m_and_i','m_i','m_and_i_percent','m_i_percent']);
                    $ivRaw    = $firstValue($d, ['iv','iv_value']);
                    $lovRedRaw = $firstValue($d, ['lovibond_color_red','lovibond_red','lov_red']);
                    $lovYelRaw = $firstValue($d, ['lovibond_color_yellow','lovibond_yellow','lov_yel']);
                    $pvRaw    = $firstValue($d, ['pv','peroxide_value']);
                    $other    = $firstValue($d, ['other','notes']);
                    $remark   = $firstValue($d, ['remark','remarks']);
                @endphp
                <tr>
                    <td class="small">{{ $shipTank ?? '-' }}</td>
                    <td class="small">{{ $noPolice ?? '-' }}</td>
                    <td class="text-center small">{{ $fmt($ffaRaw, 3) }}</td>
                    <td class="text-center small">{{ $fmt($miRaw, 3) }}</td>
                    <td class="text-center small">{{ $fmt($ivRaw, 3) }}</td>
                    <td class="text-center small">{{ $fmt($lovRedRaw, 2) }}</td>
                    <td class="text-center small">{{ $fmt($lovYelRaw, 2) }}</td>
                    <td class="text-center small">{{ $fmt($pvRaw, 3) }}</td>
                    <td class="small">{{ $other ?? '-' }}</td>
                    <td class="small">{{ $remark ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center muted">No data available</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- SIGNATURES --}}
    <table style="margin-top: 10px;">
        <tr>
            <td style="width:50%; text-align:center;">
                <div class="font-bold">Corrected by</div>
                <div class="small">(QC Leader)</div>
                <div class="sig-space"></div>
                <div class="font-bold">{{ $header->prepared_by ?? $header->corrected_by ?? '____________________' }}</div>
                <div class="muted small">
                    {{ $header->prepared_date ? \Carbon\Carbon::parse($header->prepared_date)->format('d-m-Y H:i') : ($header->corrected_date ? \Carbon\Carbon::parse($header->corrected_date)->format('d-m-Y H:i') : '') }}
                </div>
            </td>
            <td style="width:50%; text-align:center;">
                <div class="font-bold">Approved by</div>
                <div class="small">(QC Head)</div>
                <div class="sig-space"></div>
                <div class="font-bold">{{ $header->approved_by ?? '____________________' }}</div>
                <div class="muted small">
                    {{ $header->approved_date ? \Carbon\Carbon::parse($header->approved_date)->format('d-m-Y H:i') : '' }}
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
