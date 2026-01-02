<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>AROIP Fuel Report - {{ $header->id }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td,
        th {
            padding: 4px;
            vertical-align: top;
        }

        .bordered th,
        .bordered td {
            border: 1px solid black;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .mb-4 {
            margin-bottom: 15px;
        }

        .page-break {
            page-break-before: always;
        }

        /* Header specific */
        .logo {
            height: 50px;
        }

        .header-box {
            border: 1px solid black;
            padding: 5px;
            margin-bottom: 15px;
        }

        /* Table Header Background */
        thead tr {
            background-color: #e5e7eb;
        }
    </style>
</head>

<body>
    @php
        $displayDate = $header->date ?? $header->entry_date ?? null;
    @endphp

    {{-- ================= PAGE 1 ================= --}}

    {{-- Top Header --}}
    <table class="mb-4">
        <tr>
            <td style="width: 20%; text-align: center;">
                <img src="{{ public_path('images/KPN Corp.jpg') }}" class="logo" alt="Logo">
                <div class="font-bold">BEKASI</div>
            </td>
            <td style="width: 60%; text-align: center; vertical-align: middle;">
                <h2 class="uppercase" style="margin: 0; font-size: 16px;">Analytical Result of Incoming Plant
                    Fuel<br>Solar / Coal</h2>
            </td>
            <td style="width: 20%;">
                <table class="bordered" style="font-size: 10px;">
                    <tr>
                        <td>Form No</td>
                        <td>: {{ $header->form_no }}</td>
                    </tr>
                    <tr>
                        <td>Issued</td>
                        <td>:
                            {{ $header->date_issued ? \Carbon\Carbon::parse($header->date_issued)->format('ymd') : '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td>Rev</td>
                        <td>: {{ $header->revision_no }}</td>
                    </tr>
                    <tr>
                        <td>Rev Date</td>
                        <td>:
                            {{ $header->revision_date ? \Carbon\Carbon::parse($header->revision_date)->format('ymd') : '-' }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Info Box --}}
    <div class="header-box">
        <table>
            <tr>
                <td style="width: 50%;">
                    <table>
                        <tr>
                            <td width="80"><strong>Date</strong></td>
                            <td>: {{ $displayDate ? \Carbon\Carbon::parse($displayDate)->format('d/m/Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Material</strong></td>
                            <td>: {{ $header->material }}</td>
                        </tr>
                        <tr>
                            <td><strong>Quantity</strong></td>
                            <td>: {{ $header->quantity }}</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 50%;">
                    <table>
                        <tr>
                            <td width="80"><strong>Supplier</strong></td>
                            <td>: {{ $header->supplier }}</td>
                        </tr>
                        <tr>
                            <td><strong>Police No</strong></td>
                            <td>: {{ $header->police_no }}</td>
                        </tr>
                        <tr>
                            <td><strong>Analyst</strong></td>
                            <td>: {{ $header->analyst ?? $header->entry_by }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    {{-- Analytical Table --}}
    <table class="bordered mb-4">
        <thead>
            <tr>
                <th>Parameter</th>
                <th>Result</th>
                <th>Specification</th>
                <th>OK</th>
                <th>NOK</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            @forelse($header->details as $d)
                <tr>
                    <td>{{ $d->parameter }}</td>
                    <td class="text-center">{{ $d->result }}</td>
                    <td class="text-center">{{ $d->specification }}</td>
                    <td class="text-center">{{ in_array($d->status_ok, ['1', 'Y', 'OK']) ? '✓' : '' }}</td>
                    <td class="text-center">{{ in_array($d->status_ok, ['N', 'NO', 'NOT OK']) ? '✓' : '' }}</td>
                    <td>{{ $d->remark }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Signatures Page 1 --}}
    <table style="margin-top: 30px;">
        <tr>
            <td class="text-center" style="width: 50%;">
                <div class="font-bold">Corrected by</div>
                <div style="font-size: 10px;">(QC Leader)</div>
                <div style="height: 50px;"></div>
                <div class="font-bold" style="text-decoration: underline;">
                    {{ $header->prepared_by ?? '_________________' }}</div>
                <div style="font-size: 10px;">
                    {{ $header->prepared_date ? \Carbon\Carbon::parse($header->prepared_date)->format('d-m-Y H:i') : '' }}
                </div>
            </td>
            <td class="text-center" style="width: 50%;">
                <div class="font-bold">Approved by</div>
                <div style="font-size: 10px;">(QC Head)</div>
                <div style="height: 50px;"></div>
                <div class="font-bold" style="text-decoration: underline;">
                    {{ $header->approved_by ?? '_________________' }}</div>
                <div style="font-size: 10px;">
                    {{ $header->approved_date ? \Carbon\Carbon::parse($header->approved_date)->format('d-m-Y H:i') : '' }}
                </div>
            </td>
        </tr>
    </table>

    <div class="page-break"></div>

    {{-- ================= PAGE 2 ================= --}}
    @php $roa = $header->roa; @endphp

    <h2 class="text-center uppercase mb-4" style="font-size: 16px;">Report of Analysis</h2>

    {{-- ROA Info --}}
    <div class="header-box">
        <table>
            <tr>
                <td width="50%">
                    <table>
                        <tr>
                            <td width="120"><strong>Report No</strong></td>
                            <td width="10">:</td>
                            <td>{{ $roa->report_no }}</td>
                        </tr>
                        <tr>
                            <td><strong>Shipper</strong></td>
                            <td>:</td>
                            <td>{{ $roa->shipper }}</td>
                        </tr>
                        <tr>
                            <td><strong>Buyer</strong></td>
                            <td>:</td>
                            <td>{{ $roa->buyer }}</td>
                        </tr>
                    </table>
                </td>
                <td width="50%">
                    <table>
                        <tr>
                            <td width="120"><strong>Date Received</strong></td>
                            <td width="10">:</td>
                            <td>{{ $roa->date_received ? \Carbon\Carbon::parse($roa->date_received)->format('F d, Y') : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Date Analyzed</strong></td>
                            <td>:</td>
                            <td>{{ $roa->date_analyzed_start ? \Carbon\Carbon::parse($roa->date_analyzed_start)->format('F d, Y') : '-' }}
                                up to
                                {{ $roa->date_analyzed_end ? \Carbon\Carbon::parse($roa->date_analyzed_end)->format('F d, Y') : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Reported</strong></td>
                            <td>:</td>
                            <td>{{ $roa->date_reported ? \Carbon\Carbon::parse($roa->date_reported)->format('F d, Y') : '-' }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    {{-- Analysis Result Header --}}
    <table class="bordered mb-4">
        <thead>
            <tr>
                <th colspan="2" style="text-align: left;">Analysis Result</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="40%">Lab Sample ID</td>
                <td>{{ $roa->lab_sample_id }}</td>
            </tr>
            <tr>
                <td>Customer Sample ID</td>
                <td>{{ $roa->customer_sample_id }}</td>
            </tr>
            <tr>
                <td>Seal No</td>
                <td>{{ $roa->seal_no }}</td>
            </tr>
            <tr>
                <td>Weight of Received Sample</td>
                <td>{{ $roa->weight_of_received_sample }}</td>
            </tr>
            <tr>
                <td>Top Size of Received Sample</td>
                <td>{{ $roa->top_size_of_received_sample }}</td>
            </tr>
            <tr>
                <td>Hardgrove Grindability Index</td>
                <td>{{ $roa->hardgrove_grindability_index }}</td>
            </tr>
        </tbody>
    </table>

    {{-- ROA Details --}}
    <table class="bordered mb-4">
        <thead>
            <tr>
                <th>Parameter</th>
                <th>Unit</th>
                <th>Basis</th>
                <th>Result</th>
            </tr>
        </thead>
        <tbody>
            @forelse($roa->details as $rd)
                <tr>
                    <td>{{ $rd->parameter }}</td>
                    <td class="text-center">{{ $rd->unit }}</td>
                    <td class="text-center">{{ $rd->basis }}</td>
                    <td class="text-center">{{ $rd->result }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">No ROA detail</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Signature Page 2 --}}
    <table style="margin-top: 30px;">
        <tr>
            <td style="width: 60%;"></td>
            <td class="text-center" style="width: 40%;">
                <div class="font-bold">Authorized By</div>
                <div style="height: 60px;"></div>
                <div class="font-bold" style="text-decoration: underline;">
                    {{ $roa->authorized_by ?? '_____________________' }}</div>
            </td>
        </tr>
    </table>
</body>

</html>