{{-- File: rpt_daily_production/fractionation/_table_dailyPFra.blade.php --}}

{{-- 1. Product Table (Raw Material, Finished Goods Stearin, By Product/Olein) --}}
{{-- Note: This table is very wide and might require landscape A4 or A3 paper size for proper rendering. --}}
<div style="font-weight: bold; font-size: 10px; margin: 10px 0 5px 0; text-align: left;">1. Detail Produksi Per Ticket
</div>
<table>
    <thead>
        <tr>
            <th rowspan="3" style="width: 3%;">Shift</th>
            <th colspan="9" style="background-color: #fffac2;">Raw Material</th>
            <th colspan="9" style="background-color: #e6ffe6;">Finished Goods Stearin (FGS)</th>
            <th colspan="8" style="background-color: #e6f7ff;">By Product (FGH/Olein)</th>
        </tr>
        <tr>
            <th rowspan="2" style="background-color: #fffac2; width: 4%;">NO</th>
            <th rowspan="2" style="background-color: #fffac2; width: 3%;">CR</th>
            <th rowspan="2" style="background-color: #fffac2; width: 6%;">Oil Type</th>
            <th rowspan="2" style="background-color: #fffac2; width: 5%;">From Tank</th>
            <th colspan="2" style="background-color: #fffac2;">Awal</th>
            <th colspan="2" style="background-color: #fffac2;">Akhir</th>
            <th rowspan="2" style="background-color: #fffac2; width: 6%;">Total (KG)</th>

            <th rowspan="2" style="background-color: #e6ffe6; width: 4%;">NO</th>
            <th rowspan="2" style="background-color: #e6ffe6; width: 3%;">CR</th>
            <th rowspan="2" style="background-color: #e6ffe6; width: 6%;">Oil Type</th>
            <th colspan="2" style="background-color: #e6ffe6;">Awal</th>
            <th colspan="2" style="background-color: #e6ffe6;">Akhir</th>
            <th rowspan="2" style="background-color: #e6ffe6; width: 6%;">Total (KG)</th>
            <th rowspan="2" style="background-color: #e6ffe6; width: 5%;">To Tank</th>

            <th rowspan="2" style="background-color: #e6f7ff; width: 4%;">NO</th>
            <th rowspan="2" style="background-color: #e6f7ff; width: 6%;">Oil Type</th>
            <th colspan="2" style="background-color: #e6f7ff;">Awal</th>
            <th colspan="2" style="background-color: #e6f7ff;">Akhir</th>
            <th rowspan="2" style="background-color: #e6f7ff; width: 6%;">Total (KG)</th>
            <th rowspan="2" style="background-color: #e6f7ff; width: 5%;">To Tank</th>
        </tr>
        <tr>
            <th style="background-color: #fffac2; width: 3%;">Jam</th>
            <th style="background-color: #fffac2; width: 5%;">Flowmeter</th>
            <th style="background-color: #fffac2; width: 3%;">Jam</th>
            <th style="background-color: #fffac2; width: 5%;">Flowmeter</th>

            <th style="background-color: #e6ffe6; width: 3%;">Jam</th>
            <th style="background-color: #e6ffe6; width: 5%;">Flowmeter</th>
            <th style="background-color: #e6ffe6; width: 3%;">Jam</th>
            <th style="background-color: #e6ffe6; width: 5%;">Flowmeter</th>

            <th style="background-color: #e6f7ff; width: 3%;">Jam</th>
            <th style="background-color: #e6f7ff; width: 5%;">Flowmeter</th>
            <th style="background-color: #e6f7ff; width: 3%;">Jam</th>
            <th style="background-color: #e6f7ff; width: 5%;">Flowmeter</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $row)
            <tr>
                <td style="text-align: center; font-weight: bold;">{{ $row->shift }}</td>

                {{-- Raw Material --}}
                <td>{{ $row->oil_type_rm_no }}</td>
                <td>{{ $row->oil_type_rm_cr }}</td>
                <td>{{ $row->oil_type_rm }}</td>
                <td style="text-align: center;">{{ $row->oil_type_rm_from_tank }}</td>
                <td style="text-align: center;">
                    {{ optional($row->oil_type_rm_awal_jam)->format('H:i') ?? $row->oil_type_rm_awal_jam }}</td>
                <td style="text-align: right;">{{ number_format($row->oil_type_rm_awal_flowmeter, 0) }}</td>
                <td style="text-align: center;">
                    {{ optional($row->oil_type_rm_akhir_jam)->format('H:i') ?? $row->oil_type_rm_akhir_jam }}</td>
                <td style="text-align: right;">{{ number_format($row->oil_type_rm_akhir_flowmeter, 0) }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($row->oil_type_rm_total, 0) }}</td>

                {{-- Finished Goods Stearin (FGS) --}}
                <td>{{ $row->oil_type_fgs_no }}</td>
                <td>{{ $row->oil_type_fgs_cr }}</td>
                <td>{{ $row->oil_type_fgs }}</td>
                <td style="text-align: center;">
                    {{ optional($row->oil_type_fgs_awal_jam)->format('H:i') ?? $row->oil_type_fgs_awal_jam }}</td>
                <td style="text-align: right;">{{ number_format($row->oil_type_fgs_awal_flowmeter, 0) }}</td>
                <td style="text-align: center;">
                    {{ optional($row->oil_type_fgs_akhir_jam)->format('H:i') ?? $row->oil_type_fgs_akhir_jam }}</td>
                <td style="text-align: right;">{{ number_format($row->oil_type_fgs_akhir_flowmeter, 0) }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($row->oil_type_fgs_total, 0) }}</td>
                <td style="text-align: center;">{{ $row->oil_type_fgs_to_tank }}</td>

                {{-- By Product Olein (FGH) --}}
                <td>{{ $row->oil_type_fgh_no }}</td>
                <td>{{ $row->oil_type_fgh }}</td>
                <td style="text-align: center;">
                    {{ optional($row->oil_type_fgh_awal_jam)->format('H:i') ?? $row->oil_type_fgh_awal_jam }}</td>
                <td style="text-align: right;">{{ number_format($row->oil_type_fgh_awal_flowmeter, 0) }}</td>
                <td style="text-align: center;">
                    {{ optional($row->oil_type_fgh_akhir_jam)->format('H:i') ?? $row->oil_type_fgh_akhir_jam }}</td>
                <td style="text-align: right;">{{ number_format($row->oil_type_fgh_akhir_flowmeter, 0) }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($row->oil_type_fgh_total, 0) }}</td>
                <td style="text-align: center;">{{ $row->oil_type_fgh_to_tank }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="27" style="padding: 10px; text-align: center; color: #555;">
                    Tidak ada data Raw Material, Finished Goods, atau By Product untuk Work Center ini.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

{{-- Add a page break if there's more content for a cleaner layout
<div class="page-break"></div> --}}

{{-- 2. Utilities Usage Table --}}
<div style="font-weight: bold; font-size: 10px; margin: 10px 0 5px 0; text-align: left;">2. Utilities Usage</div>
<table>
    <thead>
        <tr>
            <th rowspan="2" style="width: 10%;">Shift</th>
            <th colspan="4" style="background-color: #f3e6ff;">Flowmeter</th>
            <th colspan="2" style="background-color: #f5f5f5;">Usage</th>
        </tr>
        <tr>
            <th style="background-color: #f3e6ff;">Awal</th>
            <th style="background-color: #f3e6ff;">Akhir</th>
            <th style="background-color: #f3e6ff;">Total</th>
            <th style="background-color: #f3e6ff;">Yield(%)</th>
            <th style="background-color: #f5f5f5;">Listrik</th>
            <th style="background-color: #f5f5f5;">Air</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $row)
            <tr>
                <td style="text-align: center; font-weight: bold;">{{ $row->shift }}</td>

                {{-- Utilities Flowmeter --}}
                <td style="text-align: right;">{{ number_format($row->uu_flowmeter_before, 0) }}</td>
                <td style="text-align: right;">{{ number_format($row->uu_flowmeter_after, 0) }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($row->uu_flowmeter_total, 0) }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($row->uu_yield_percent, 2) }}</td>

                {{-- Utilities Usage --}}
                <td style="text-align: right;">{{ number_format($row->uu_listrik, 0) }}</td>
                <td style="text-align: right;">{{ number_format($row->uu_air, 0) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="padding: 10px; text-align: center; color: #555;">
                    Tidak ada data Utilities Usage untuk tanggal ini.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

{{-- 3. Remarks Table --}}
<div style="font-weight: bold; font-size: 10px; margin: 10px 0 5px 0; text-align: left;">3. Remarks</div>
<table style="margin-bottom: 20px;">
    <thead>
        <tr>
            <th style="width: 10%;">Shift</th>
            <th style="width: 90%;">Remarks</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $row)
            <tr>
                <td style="text-align: center; font-weight: bold;">{{ $row->shift }}</td>
                <td style="text-align: left;">{{ $row->remarks }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="2" style="padding: 10px; text-align: center; color: #555;">
                    Tidak ada catatan/remarks untuk shift ini.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
