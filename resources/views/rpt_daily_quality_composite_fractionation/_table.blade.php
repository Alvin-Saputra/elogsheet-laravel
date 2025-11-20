<div class="overflow-x-auto mb-6">
    <table class="min-w-full border border-gray-400 text-center text-xs">
        <thead class="bg-gray-100">
            <tr>
                <th rowspan="2" class="border px-2 py-1">Time</th>
                <th rowspan="2" class="border px-2 py-1 bg-yellow-300">Crystalizer</th>

                <th colspan="6" class="border px-2 py-1 bg-green-100">RBDPO</th>
                <th colspan="10" class="border px-2 py-1 bg-teal-100">OLEIN</th>
                    <th colspan="9" class="border px-2 py-1 bg-purple-100">STEARIN</th>
                <th rowspan="2" class="border px-2 py-1">Remarks</th>
            </tr>
            <tr>
                <th class="border p-1">M&I</th>
                <th class="border p-1">IV</th>
                <th class="border p-1">Colour R</th>
                <th class="border p-1">Colour Y</th>
                <th class="border p-1">Colour W</th>
                <th class="border p-1">Colour B</th>

                <th class="border p-1">FFA</th>
                <th class="border p-1">MNI</th>
                <th class="border p-1">IV</th>
                <th class="border p-1">Colour R</th>
                <th class="border p-1">Colour Y</th>
                <th class="border p-1">Colour W</th>
                <th class="border p-1">Colour B</th>
                <th class="border p-1">CP</th>
                <th class="border p-1">Clarity</th>
                <th class="border p-1">To Tank</th>

                <th class="border p-1">FFA</th>
                <th class="border p-1">MNI</th>
                <th class="border p-1">IV</th>
                <th class="border p-1">PV</th>
                <th class="border p-1">Colour R</th>
                <th class="border p-1">Colour Y</th>
                <th class="border p-1">Colour W</th>
                <th class="border p-1">Colour B</th>
                <th class="border p-1">To Tank</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td class="border p-1">{{ $row->time}}</td>
                    <td class="border p-1">{{ $row->crystalizer}}</td>
                    <td class="border p-1">{{ $row->rm_mni}}</td>
                    <td class="border p-1">{{ $row->rm_iv}}</td>
                    <td class="border p-1">{{ $row->rm_color_r}}</td>
                    <td class="border p-1">{{ $row->rm_color_y}}</td>
                    <td class="border p-1">{{ $row->rm_color_w}}</td>
                    <td class="border p-1">{{ $row->rm_color_b}}</td>

                    <td class="border p-1">{{ $row->fg_ffa}}</td>
                    <td class="border p-1">{{ $row->fg_mni}}</td>
                    <td class="border p-1">{{ $row->fg_iv}}</td>
                    <td class="border p-1">{{ $row->fg_color_r}}</td>
                    <td class="border p-1">{{ $row->fg_color_y}}</td>
                    <td class="border p-1">{{ $row->fg_color_w}}</td>
                    <td class="border p-1">{{ $row->fg_color_b}}</td>
                    <td class="border p-1">{{ $row->fg_cp}}</td>
                    <td class="border p-1">{{ $row->fg_clarity}}</td>
                    <td class="border p-1">{{ $row->fg_to_tank}}</td>
                    <td class="border p-1">{{ $row->bp_ffa}}</td>
                    <td class="border p-1">{{ $row->bp_mni}}</td>
                    <td class="border p-1">{{ $row->bp_iv}}</td>
                    <td class="border p-1">{{ $row->bp_pv}}</td>
                    <td class="border p-1">{{ $row->bp_color_r}}</td>
                    <td class="border p-1">{{ $row->bp_color_y}}</td>
                    <td class="border p-1">{{ $row->bp_color_w}}</td>
                    <td class="border p-1">{{ $row->bp_color_b}}</td>
                    <td class="border p-1">{{ $row->bp_to_tank}}</td>
                    <td class="border p-1">{{ $row->remarks}}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>