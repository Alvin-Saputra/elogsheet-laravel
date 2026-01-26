<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Daily Storage Tank Analytical</title>
<style>
    body { font-size: 9px; font-family: sans-serif; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #444; padding: 3px; text-align: center; }
    th { background: #f3f3f3; }
</style>
</head>
<body>

<div style="text-align:right;font-size:10px;">
    <div><strong>Form No.</strong> : F/QCO-001</div>
    <div><strong>Revision</strong> : 00</div>
</div>

<h3 style="text-align:center;">PT.PRISCOLIN</h3>
<h4 style="text-align:center;">LOGSHEET DAILY STORAGE TANK ANALYTICAL</h4>
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
    <th>Lovibond Color R</th>
    <th>Lovibond Color Y</th>
    <th>IV</th>
    <th>PV</th>
    <th>Slip Melting Point</th>
    <th>Cloud Point</th>
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
    <td>{{ $r->remark ?? '-' }}</td>
</tr>
@empty
<tr>
    <td colspan="21" style="text-align:center;">
        No data available
    </td>
</tr>
@endforelse
</tbody>
</table>

</body>
</html>
