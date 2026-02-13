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
                    @php 
                        // Cari baris data asli untuk ambil nama refinery, jika tidak ada pakai nama WC
                        $firstRow = $rows->first(fn($r) => isset($r->refinery_name)); 
                    @endphp
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
    </div>

    @if ($groupedData->isNotEmpty())
        @foreach ($groupedData as $wc => $rows)
            @php
                $isRef01 = $wc === 'REF-01';
            @endphp
            <table style="margin-bottom: 20px;">
                <thead>
                    <tr>
                        <th rowspan="2">Time</th>
                        <th rowspan="2">Oil Type</th>
                        <th rowspan="2">Finish Good</th>
                        <th rowspan="2">By Product</th>
                        <th rowspan="2">From Tank No.</th>
                        <th rowspan="2">Flow Rate</th>
                        <th colspan="10">RAW MATERIAL</th>
                        <th colspan="4">Bleaching Oil</th>
                        <th colspan="{{ $isRef01 ? 9 : 10 }}">Finish Good</th>
                        <th colspan="3">By Prodcut</th>
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
                        <th>{{ $isRef01 ? 'M&I' : 'Moist' }}</th>
                        @if (!$isRef01)
                            <th>IMP</th>
                        @endif
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
                            <td>{{ $row->oil_type ?? '-' }}</td>
                            <td>{{ $row->oil_type_fg ?? '-' }}</td>
                            <td>{{ $row->oil_type_bp ?? '-' }}</td>
                            
                            {{-- PERBAIKAN DI SINI: Menggunakan ?? '' --}}
                            <td>{{ $row->rm_tank_source ?? '' }}</td>
                            <td>{{ $row->rm_flowrate ?? '' }}</td>
                            
                            <td>{{ $row->rm_ffa ?? '' }}</td>
                            <td>{{ $row->{'rm_m&i'} ?? '' }}</td>
                            <td>{{ $row->rm_dobi ?? '' }}</td>
                            <td>{{ $row->rm_iv ?? '' }}</td>
                            <td>{{ $row->rm_pv ?? '' }}</td>
                            <td>{{ $row->rm_av ?? '' }}</td>
                            <td>{{ $row->rm_totox ?? '' }}</td>
                            <td>{{ $row->rm_color_r ?? '' }}</td>
                            <td>{{ $row->rm_color_y ?? '' }}</td>
                            <td>{{ $row->rm_color_b ?? '' }}</td>

                            <td>{{ $row->bo_color_r ?? '' }}</td>
                            <td>{{ $row->bo_color_y ?? '' }}</td>
                            <td>{{ $row->bo_color_b ?? '' }}</td>
                            <td>{{ $row->bo_break_test ?? '' }}</td>

                            <td>{{ $row->fg_ffa ?? '' }}</td>
                            <td>{{ $row->fg_moisture ?? '' }}</td>
                            @if (!$isRef01)
                                <td>{{ $row->fg_impurities ?? '' }}</td>
                            @endif
                            <td>{{ $row->fg_iv ?? '' }}</td>
                            <td>{{ $row->fg_pv ?? '' }}</td>
                            <td>{{ $row->fg_color_r ?? '' }}</td>
                            <td>{{ $row->fg_color_y ?? '' }}</td>
                            <td>{{ $row->fg_color_b ?? '' }}</td>
                            <td>{{ $row->fg_tank_to ?? '' }}</td>
                            <td>{{ $row->fg_tank_to_others_remarks ?? '' }}</td>
                            
                            <td>{{ $row->bp_ffa ?? '' }}</td>
                            <td>{{ $row->{'bp_m&i'} ?? '' }}</td>
                            <td>{{ $row->bp_to_tank ?? '' }}</td>
                            
                            <td>{{ $row->{'w_sbe_m&i'} ?? '' }}</td>
                            <td>{{ $row->w_sbe_qc ?? '' }}</td>
                            <td>{{ $row->remarks ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @else
        {{-- TABEL KEDUA (JIKA GROUPED DATA KOSONG / LOGIC LAIN) --}}
        <table style="margin-bottom: 20px;">
            <thead>
                <tr>
                    <th rowspan="2">Time</th>
                    <th rowspan="2">Oil Type</th>
                    <th rowspan="2">Finish Good</th>
                    <th rowspan="2">By Product</th>
                    <th rowspan="2">From Tank No.</th>
                    <th rowspan="2">Flow Rate</th>
                    <th colspan="10">RAW MATERIAL</th>
                    <th colspan="4">BPO</th>
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
                    <th>IMP</th>
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
                        <td>{{ $row->oil_type ?? '' }}</td>
                        <td>{{ $row->oil_type_fg ?? '' }}</td>
                        <td>{{ $row->oil_type_bp ?? '' }}</td>

                        {{-- PERBAIKAN DI SINI JUGA --}}
                        <td>{{ $row->rm_tank_source ?? '' }}</td>
                        <td>{{ $row->rm_flowrate ?? '' }}</td>

                        <td>{{ $row->rm_ffa ?? '' }}</td>
                        <td>{{ $row->{'rm_m&i'} ?? '' }}</td>
                        <td>{{ $row->rm_dobi ?? '' }}</td>
                        <td>{{ $row->rm_iv ?? '' }}</td>
                        <td>{{ $row->rm_pv ?? '' }}</td>
                        <td>{{ $row->rm_av ?? '' }}</td>
                        <td>{{ $row->rm_totox ?? '' }}</td>
                        <td>{{ $row->rm_color_r ?? '' }}</td>
                        <td>{{ $row->rm_color_y ?? '' }}</td>
                        <td>{{ $row->rm_color_b ?? '' }}</td>

                        <td>{{ $row->bo_color_r ?? '' }}</td>
                        <td>{{ $row->bo_color_y ?? '' }}</td>
                        <td>{{ $row->bo_color_b ?? '' }}</td>
                        <td>{{ $row->bo_break_test ?? '' }}</td>

                        <td>{{ $row->fg_ffa ?? '' }}</td>
                        <td>{{ $row->fg_moisture ?? '' }}</td>
                        {{-- Asumsi tabel kedua ini logic Ref01-nya dihandle di luar atau dianggap tidak Ref01 untuk amannya saya beri col IMP --}}
                        <td>{{ $row->fg_impurities ?? '' }}</td> 
                        
                        <td>{{ $row->fg_iv ?? '' }}</td>
                        <td>{{ $row->fg_pv ?? '' }}</td>
                        <td>{{ $row->fg_color_r ?? '' }}</td>
                        <td>{{ $row->fg_color_y ?? '' }}</td>
                        <td>{{ $row->fg_color_b ?? '' }}</td>
                        <td>{{ $row->fg_tank_to ?? '' }}</td>
                        <td>{{ $row->fg_tank_to_others_remarks ?? '' }}</td>

                        <td>{{ $row->bp_ffa ?? '' }}</td>
                        <td>{{ $row->{'bp_m&i'} ?? '' }}</td>
                        <td>{{ $row->bp_to_tank ?? '' }}</td>

                        <td>{{ $row->{'w_sbe_m&i'} ?? '' }}</td>
                        <td>{{ $row->w_sbe_qc ?? '' }}</td>
                        <td>{{ $row->remarks ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    
    @php
        $production = null;
        $productionList = [];

        // Helper function untuk mencari data asli (bukan dummy stdClass)
        $findRealRow = function($collection) {
            return $collection->first(function($item) {
                return isset($item->dailyProduction);
            });
        };

        // Skenario 1: Jika User memfilter 1 Work Center spesifik
        if (!empty($workCenter) && !empty($data) && $data->count() > 0) {
            $realRow = $findRealRow($data);
            if ($realRow) {
                $production = $realRow->dailyProduction;
                $productionList[$workCenter] = $production;
            }
        } 
        // Skenario 2: Grouped Data
        elseif (!empty($groupedData)) {
            foreach ($groupedData as $wc => $rows) {
                if ($rows->count() > 0) {
                    $realRow = $findRealRow($rows);
                    if ($realRow) {
                        $productionList[$wc] = $realRow->dailyProduction;
                        if (is_null($production)) {
                            $production = $realRow->dailyProduction;
                        }
                    } else {
                        $productionList[$wc] = null;
                    }
                }
            }
        }
    @endphp

    {{-- Footer Box Loop --}}
    @foreach ($productionList as $wcKey => $production)
        <div style="margin-top: 20px; width: 100%;">
            <div style="font-weight: bold; text-decoration: underline; margin-bottom: 5px;">
                Refinery Data: {{ $wcKey }}
            </div>

            <div style="width: 100%;">
                {{-- Box Kiri --}}
                <div class="section-table" style="margin-top: 0; width: 48%; float: left; margin-right: 2%;">
                    <div style="padding: 5px; font-weight: bold; background-color: #f3f3f3; border-bottom: 0.5px solid #444;">
                        Daily Chemical Usage
                    </div>
                    <table style="width: 100%; border: none;">
                        <tr>
                            <td style="border: none; width: 50%; text-align: left;">Bleaching Earth</td>
                            <td style="border: none; text-align: left;">: {{ $production->be_ref_qty ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="border: none; text-align: left;">Phosphoric Acid</td>
                            <td style="border: none; text-align: left;">: {{ $production->pa_ref_qty ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="border: none; text-align: left;">RPO Usage</td>
                            <td style="border: none; text-align: left;">: {{ $production->oil_type_rm_total ?? '-' }}</td>
                        </tr>
                    </table>
                </div>

                {{-- Box Kanan --}}
                <div class="section-table" style="margin-top: 0; width: 48%; float: left;">
                    <div style="padding: 5px; font-weight: bold; background-color: #f3f3f3; border-bottom: 0.5px solid #444;">
                        Theoretical Yield
                    </div>
                    <table style="width: 100%; border: none;">
                        <tr>
                            <td style="border: none; width: 50%; text-align: left;">RPO</td>
                            <td style="border: none; text-align: left;">: {{ $production->oil_type_fg_total ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="border: none; text-align: left;">PFAD</td>
                            <td style="border: none; text-align: left;">: {{ $production->bp_total ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="border: none; text-align: left;">Losses</td>
                            <td style="border: none; text-align: left;">: {{ $production->uu_yield_percent ?? '-' }}</td>
                        </tr>
                    </table>
                </div>

                <div style="clear: both;"></div>
            </div>
        </div>
    @endforeach

    @if (empty($productionList))
        <div style="margin-top: 20px; text-align: center; border: 0.5px solid #444; padding: 10px; color: #666;">
            Data Produksi (Interlock) belum tersedia.
        </div>
    @endif

    <div style="margin-top: 30px;"></div>

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

</body>
</html>