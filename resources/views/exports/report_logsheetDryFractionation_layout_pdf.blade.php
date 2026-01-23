<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Dry Fractionation</title>
    
    {{-- INTERNAL CSS FOR PDF GENERATION --}}
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .container {
            background-color: #ffffff;
            padding: 20px;
        }

        /* --- Header Info (Top Right) --- */
        .header-info {
            text-align: right;
            font-size: 10px;
            line-height: 1.4;
            margin-bottom: 10px;
        }

        /* --- Title Section --- */
        .title-section {
            text-align: center;
            margin-bottom: 30px;
            margin-top: 0px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 0;
        }
        .report-name {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
            margin: 5px 0;
        }
        .date-range {
            font-weight: bold;
            margin-top: 5px;
        }

        /* --- Table Styles --- */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 20px;
        }
        
        th, td {
            border: 1px solid #000000; /* Pure black for PDF sharpness */
            padding: 4px;
            text-align: center;
            vertical-align: middle;
        }

        thead th {
            background-color: #f3f4f6;
            font-weight: bold;
        }

        /* Detail Columns Background */
        .bg-blue-light {
            background-color: #eff6ff;
        }

        /* Parent Header Cells (White background) */
        .bg-white {
            background-color: #ffffff;
        }

        /* --- Signature Section --- */
        .signature-table {
            width: 100%;
            margin-top: 20px;
            border: none;
        }
        .signature-table td {
            border: none;
            text-align: center;
            vertical-align: top;
            padding: 0 20px;
        }
        .sign-line {
            border-bottom: 1px solid black;
            display: inline-block;
            min-width: 150px;
            margin-top: 40px;
            margin-bottom: 5px;
        }

        /* --- Footer --- */
        .footer-note {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #6b7280;
            font-style: italic;
        }

        /* --- Page Break Utilities --- */
        .page-break {
            page-break-after: always;
        }
        .page-break-avoid {
            page-break-inside: avoid;
        }
        
        .date-header {
            font-weight: bold;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 10px;
            font-size: 13px;
            text-align: left;
        }
    </style>
</head>
<body>

<div class="container">

    {{-- ================= HEADER INFORMATION ================= --}}
    {{-- Using a table for header alignment is safer for PDF than absolute positioning --}}
    <table style="border: none; margin-bottom: 0;">
        <tr>
            <td style="border: none; text-align: left; width: 60%;"></td>
            <td style="border: none; text-align: right; width: 40%;">
                <div class="header-info">
                    <div><strong>Form No.</strong> : {{ $formInfoFirst->form_no ?? 'F/RFA-010' }}</div>
                    <div><strong>Date Issued</strong> : {{ $formInfoFirst ? optional($formInfoFirst->date_issued)->format('ymd') : '210101' }}</div>
                    <div><strong>Revision</strong> : {{ $formInfoLast ? sprintf('%02d', $formInfoLast->revision_no) : '01' }}</div>
                    <div><strong>Rev. Date</strong> : {{ $formInfoLast ? optional($formInfoLast->revision_date)->format('ymd') : '210901' }}</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- ================= TITLE SECTION ================= --}}
    <div class="title-section">
        <h2 class="company-name">PT. PRISCOLIN</h2>
        <h3 class="report-name">LOGSHEET DRY FRACTIONATION</h3>

        <div class="date-range">
            Date:
            @if ($startDate == $endDate)
                {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }}
            @else
                {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}
                <span>-</span>
                {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
            @endif
        </div>
    </div>

    {{-- ================= MAIN CONTENT LOOP ================= --}}
    <div>
        @forelse ($groupedData as $dateKey => $headers)
            {{-- Container per Date --}}
            <div class="page-break-avoid" style="margin-bottom: 30px;">

                {{-- Sub-header per date --}}
                @if ($startDate != $endDate)
                    <div class="date-header">
                        {{ \Carbon\Carbon::parse($dateKey)->format('l, d F Y') }}
                    </div>
                @endif

                {{-- ================= TABLE START ================= --}}
                <table>
                    <thead>
                        <tr>
                            {{-- HEADER COLUMNS --}}
                            <th style="width: 8%;">Crystallizer<br>(Batch #)</th>
                            <th>Filling<br>Start</th>
                            <th>Filling<br>End</th>
                            <th>Cooling<br>Start</th>
                            <th>Oil Level<br>(%)</th>
                            <th>Agitator<br>(Hz)</th>
                            <th>Pump<br>(Bar)</th>

                            {{-- DETAIL COLUMNS --}}
                            <th class="bg-blue-light">Cycle #</th>
                            <th class="bg-blue-light">Filt. Temp</th>
                            <th class="bg-blue-light">Start Filt</th>
                            <th class="bg-blue-light">End Filt</th>
                            <th class="bg-blue-light">Load (%)</th>
                            <th class="bg-blue-light">Olein IV</th>
                            <th class="bg-blue-light">Olein CP</th>
                            <th class="bg-blue-light">Olein FFA</th>
                            <th class="bg-blue-light">Olein Color</th>
                            <th class="bg-blue-light">Stearin IV</th>
                            <th class="bg-blue-light">Stearin FFA</th>
                            <th class="bg-blue-light">Stearin Color</th>
                            <th class="bg-blue-light">Stearin PV</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($headers as $header)
                            @php
                                $rowCount = $header->details->count();
                            @endphp

                            @if ($rowCount > 0)
                                {{-- Case A: Details Exist --}}
                                @foreach ($header->details as $index => $detail)
                                    <tr>
                                        {{-- Parent Info (First Row Only) --}}
                                        @if ($index === 0)
                                            <td class="bg-white" rowspan="{{ $rowCount }}">{{ $header->crystallizer }}</td>
                                            <td class="bg-white" rowspan="{{ $rowCount }}">{{ \Carbon\Carbon::parse($header->filling_start_time)->format('H:i') }}</td>
                                            <td class="bg-white" rowspan="{{ $rowCount }}">{{ \Carbon\Carbon::parse($header->filling_end_time)->format('H:i') }}</td>
                                            <td class="bg-white" rowspan="{{ $rowCount }}">{{ \Carbon\Carbon::parse($header->cooling_start_time)->format('H:i') }}</td>
                                            <td class="bg-white" rowspan="{{ $rowCount }}">{{ $header->initial_oil_level }}</td>
                                            <td class="bg-white" rowspan="{{ $rowCount }}">{{ $header->agitator_speed }}</td>
                                            <td class="bg-white" rowspan="{{ $rowCount }}">{{ $header->water_pump_pres }}</td>
                                        @endif

                                        {{-- Detail Info --}}
                                        <td class="bg-blue-light">{{ $detail->filtration_cycle_number }}</td>
                                        <td class="bg-blue-light">{{ $detail->filtration_temp }}°C</td>
                                        <td class="bg-blue-light">{{ $detail->time_start_filtration ? \Carbon\Carbon::parse($detail->time_start_filtration)->format('H:i') : '-' }}</td>
                                        <td class="bg-blue-light">{{ $detail->time_end_filtration ? \Carbon\Carbon::parse($detail->time_end_filtration)->format('H:i') : '-' }}</td>
                                        <td class="bg-blue-light">{{ $detail->load }}</td>
                                        <td class="bg-blue-light">{{ $detail->olein_iv }}</td>
                                        <td class="bg-blue-light">{{ $detail->olein_cp }}</td>
                                        <td class="bg-blue-light">{{ $detail->olein_ffa }}</td>
                                        <td class="bg-blue-light">{{ $detail->olein_color_red }}</td>
                                        <td class="bg-blue-light">{{ $detail->stearin_iv }}</td>
                                        <td class="bg-blue-light">{{ $detail->stearin_ffa }}</td>
                                        <td class="bg-blue-light">{{ $detail->stearin_color_red }}</td>
                                        <td class="bg-blue-light">{{ $detail->stearin_pv }}</td>
                                    </tr>
                                @endforeach
                            @else
                                {{-- Case B: No Details (Fallback) --}}
                                <tr>
                                    <td>{{ $header->crystallizer }}</td>
                                    <td>{{ $header->filling_start_time }}</td>
                                    <td>{{ $header->filling_end_time }}</td>
                                    <td>{{ $header->cooling_start_time }}</td>
                                    <td>{{ $header->initial_oil_level }}</td>
                                    <td>{{ $header->agitator_speed }}</td>
                                    <td>{{ $header->water_pump_pres }}</td>
                                    <td class="bg-blue-light" colspan="13" style="font-style: italic; color: #9ca3af;">No Filtration Data</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
                {{-- ================= TABLE END ================= --}}

                {{-- ================= SIGNATURE SECTION ================= --}}
                <table class="signature-table">
                    <tr>
                        <td width="50%">
                            <strong>Prepared By:</strong><br>
                            <span class="sign-line">{{ $headers->first()->prepared_by ?? '________________' }}</span><br>
                            (Leader Shift)
                        </td>
                        <td width="50%">
                            <strong>Checked by:</strong><br>
                            <span class="sign-line">{{ $headers->first()->checked_by ?? '________________' }}</span><br>
                            (SPV / Manager)
                        </td>
                    </tr>
                </table>

            </div>

            {{-- Force Page Break between days (optional) --}}
            @if (!$loop->last)
                <div class="page-break"></div>
            @endif

        @empty
            {{-- Empty State --}}
            <div style="text-align: center; padding: 40px; border: 2px dashed #ccc; color: #999;">
                No data found for this date range.
            </div>
        @endforelse
    </div>

    {{-- ================= FOOTER DISCLAIMER ================= --}}
    <div class="footer-note">
        Dokumen ini telah disetujui secara elektronik melalui sistem [E-Logsheet],
        sehingga tidak memerlukan tanda tangan asli basah.
    </div>
</div>
</body>
</html>