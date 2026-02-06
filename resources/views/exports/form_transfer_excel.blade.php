<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Form Transfer Export</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
            font-size: 11px;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .header-info {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="header-info">
        <h3>Form Transfer Report</h3>
        <p>Period: {{ $startDate }} to {{ $endDate }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>From Dept</th>
                <th>To Dept</th>
                <th>Oil Type</th>
                <th>Quantity</th>
                <th>M&I (%)</th>
                <th>FFA (%)</th>
                <th>Color R</th>
                <th>Color Y</th>
                <th>CP Temp</th>
                <th>SMP</th>
                <th>PV</th>
                <th>IV</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $header)
                @forelse($header->details as $detail)
                    <tr>
                        <td>{{ $header->id }}</td>
                        <td>{{ $header->transaction_date->format('Y-m-d') }}</td>
                        <td>{{ $header->from_dept }}</td>
                        <td>{{ $header->to_dept }}</td>
                        <td>{{ $detail->oil_type }}</td>
                        <td>{{ $detail->quantity }}</td>
                        <td>{{ $detail->quality_m_and_i }}</td>
                        <td>{{ $detail->quality_ffa }}</td>
                        <td>{{ $detail->quality_lov_color_r }}</td>
                        <td>{{ $detail->quality_lov_color_y }}</td>
                        <td>{{ $detail->quality_cp_temp }}</td>
                        <td>{{ $detail->quality_smp }}</td>
                        <td>{{ $detail->quality_pv }}</td>
                        <td>{{ $detail->quality_iv }}</td>
                        <td>
                            @if($header->approved_status == 'APPROVED')
                                Approved
                            @elseif($header->checked_status == 'APPROVED')
                                Checked
                            @elseif($header->prepared_status == 'APPROVED')
                                Prepared
                            @else
                                Draft
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td>{{ $header->id }}</td>
                        <td>{{ $header->transaction_date->format('Y-m-d') }}</td>
                        <td>{{ $header->from_dept }}</td>
                        <td>{{ $header->to_dept }}</td>
                        <td colspan="10">No details</td>
                        <td>
                            @if($header->approved_status == 'APPROVED')
                                Approved
                            @elseif($header->checked_status == 'APPROVED')
                                Checked
                            @elseif($header->prepared_status == 'APPROVED')
                                Prepared
                            @else
                                Draft
                            @endif
                        </td>
                    </tr>
                @endforelse
            @endforeach
        </tbody>
    </table>
</body>
</html>
