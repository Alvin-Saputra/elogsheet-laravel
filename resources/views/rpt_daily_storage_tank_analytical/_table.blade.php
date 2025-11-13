<div class="overflow-x-auto mb-6">
    <table class="min-w-full border border-gray-400 text-center text-xs">
        <thead class="bg-gray-100">
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
            @foreach ($rows as $row)
                <tr>
                    <td class="border p-1">{{ $row->tank_no}}</td>
                    <td class="border p-1">{{ $row->oil_type}}</td>
                    <td class="border p-1">{{ $row->transaction_date}}</td>
                    <td class="border p-1">{{ $row->kapasitas_tanki }}</td>
                    <td class="border p-1">{{ $row->quantity}}</td>
                    <td class="border p-1">{{ $row->empty_space}}</td>
                    <td class="border p-1">{{ $row->suhu}}</td>
                    <td class="border p-1">{{ $row->qp_ffa}}</td>
                    <td class="border p-1">{{ $row->qp_moisture}}</td>
                    <td class="border p-1">{{ $row->qp_lovibond_color_r}}</td>
                    <td class="border p-1">{{ $row->qp_lovibond_color_y}}</td>
                    <td class="border p-1">{{ $row->qp_iv}}</td>
                    <td class="border p-1">{{ $row->qp_pv}}</td>
                    <td class="border p-1">{{ $row->qp_slip_melting_point}}</td>
                    <td class="border p-1">{{ $row->qp_cloud_point}}</td>
                    <td class="border p-1">{{ $row->qp_anv}}</td>
                    <td class="border p-1">{{ $row->qp_beta_carotene}}</td>
                    <td class="border p-1">{{ $row->qp_p}}</td>
                    <td class="border p-1">{{ $row->qp_dobi}}</td>
                    <td class="border p-1">{{ $row->qp_totox}}</td>
                    <td class="border p-1">{{ $row->qp_odor}}</td>
                    <td class="border p-1">{{ $row->qp_remark}}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>