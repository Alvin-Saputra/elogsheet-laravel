<div class="overflow-x-auto mb-6">
    <table class="min-w-full border border-gray-400 text-center text-xs">
        <thead class="bg-gray-100">
            <tr>
                <th class="border p-1">Crystalizier (Batch #)</th>
                <th class="border p-1">Filling Start Time</th>
                <th class="border p-1">Filling End Time</th>
                <th class="border p-1">Colling Start Time</th>
                <th class="border p-1">Initial Oil Level (%)</th>
                <th class="border p-1">Initial Tank</th>
                <th class="border p-1">Feed IV</th>
                <th class="border p-1">Agitator Speed (Hz)</th>
                <th class="border p-1">Water Pump Press (Bar)</th>
                <th class="border p-1">Crystal Start Time</th>
                <th class="border p-1">Crystal Temp</th>
                <th class="border p-1">Filtration Start Time</th>
                <th class="border p-1">Filtration Temp</th>
                <th class="border p-1">Filtration Cycle No</th>
                <th class="border p-1">Filtration Oil Level (%)</th>
                <th class="border p-1">Olein IV RED</th>
                <th class="border p-1">Olein Cloud Point</th>
                <th class="border p-1">Stearin IV</th>
                <th class="border p-1">Stearin Slep Point Red</th>
                <th class="border p-1">Olein Yield (%)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td class="border p-1">{{ $row->crystalizier }}</td>
                    <td class="border p-1">{{ $row->filling_start_time }}</td>
                    <td class="border p-1">{{ $row->filling_end_time }}</td>
                    <td class="border p-1">{{ $row->colling_start_time }}</td>
                    <td class="border p-1">{{ $row->initial_oil_level }}</td>
                    <td class="border p-1">{{ $row->initial_tank }}</td>
                    <td class="border p-1">{{ $row->feed_iv }}</td>
                    <td class="border p-1">{{ $row->agitator_speed }}</td>
                    <td class="border p-1">{{ $row->water_pump_press }}</td>
                    <td class="border p-1">{{ $row->crystal_start_time }}</td>
                    <td class="border p-1">{{ $row->crystal_temp }}</td>
                    <td class="border p-1">{{ $row->filtration_start_time }}</td>
                    <td class="border p-1">{{ $row->filtration_temp }}</td>
                    <td class="border p-1">{{ $row->filtration_cycle_no }}</td>
                    <td class="border p-1">{{ $row->filtration_oil_level }}</td>
                    <td class="border p-1">{{ $row->olein_iv_red }}</td>
                    <td class="border p-1">{{ $row->olein_cloud_point }}</td>
                    <td class="border p-1">{{ $row->stearin_iv }}</td>
                    <td class="border p-1">{{ $row->stearin_slep_point_red }}</td>
                    <td class="border p-1">{{ $row->olein_yield }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

