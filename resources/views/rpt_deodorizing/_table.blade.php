<div class="overflow-x-auto mb-6">
    <table class="min-w-full border border-gray-400 text-center text-xs">
        <thead class="bg-gray-100">
            <tr>
                <th rowspan="3" class="border p-1">Time</th>
                <th rowspan="3">Oil Type</th>
                <th rowspan="2">FIT701</th>
                <th colspan="2" class="border p-1">D 701</th>
                <th rowspan="2" class="border p-1">E702</th>
                <th colspan="2" class="border p-1">Thermopac</th>
                <th colspan="3" class="border p-1">D702</th>
                <th colspan="2" class="border p-1">Sparging</th>
                <th colspan="1" class="border p-1">E 703</th>
                <th colspan="1" class="border p-1">Steam</th>
                <th rowspan="2" class="border p-1">PISH 706</th>
                <th rowspan="2" class="border p-1">TIWH 706</th>
                <th colspan="3" class="border p-1">F702</th>
                <th rowspan="3" class="border p-1">Oil Type FG</th>
                <th rowspan="2" class="border p-1">FIT704</th>
                <th rowspan="2" class="border p-1">E 704</th>
                <th rowspan="3" class="border p-1">Oil Type BP</th>
                <th rowspan="2" class="border p-1">FIT705</th>
                <th rowspan="2" class="border p-1">E 705</th>
                <th rowspan="3" class="border p-1">Clarity</th>
                <th rowspan="3" class="border p-1">Remarks</th>
            </tr>
            <tr>
                <th class="border p-1">Vacum</th>
                <th class="border p-1">T D701</th>
                <th class="border p-1">Inlet</th>
                <th class="border p-1">Outlet</th>
                <th class="border p-1">Inlet</th>
                <th class="border p-1">Outlet</th>
                <th class="border p-1">Vacum</th>
                <th class="border p-1">A</th>
                <th class="border p-1">B</th>
                <th class="border p-1">Inlet</th>
                <th class="border p-1">Inlet</th>
                <th class="border p-1">A</th>
                <th class="border p-1">B</th>
                <th class="border p-1">C</th>
            </tr>
            <tr>
                <th class="border p-1">tph</th>
                <th class="border p-1">cmHg</th>
                <th class="border p-1">(°C)</th>
                <th class="border p-1">(°C)</th>
                <th class="border p-1">(°C)</th>
                <th class="border p-1">(°C)</th>
                <th class="border p-1">(°C)</th>
                <th class="border p-1">(°C)</th>
                <th class="border p-1">mbar</th>
                <th class="border p-1">bar</th>
                <th class="border p-1">bar</th>
                <th class="border p-1">(°C)</th>
                <th class="border p-1">bar</th>
                <th class="border p-1">bar</th>
                <th class="border p-1">(°C)</th>
                <th class="border p-1">bar</th>
                <th class="border p-1">bar</th>
                <th class="border p-1">bar</th>
                <th class="border p-1">tph</th>
                <th class="border p-1">(°C)</th>
                <th class="border p-1">tph</th>
                <th class="border p-1">(°C)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                       <tr>
                        <td class="border p-1">{{ \Carbon\Carbon::parse($row->time)->format('H:i') }}</td>
                        <td class="border p-1">{{ $row->oil_type ?? '' }}</td>
                        <td class="border p-1">{{ $row->fit701_bpo ?? '' }}</td>
                        <td class="border p-1">{{ $row->d701_vacum ?? '' }}</td>
                        <td class="border p-1">{{ $row->d701_td701 ?? '' }}</td>
                        <td class="border p-1">{{ $row->e702 ?? '' }}</td>
                        <td class="border p-1">{{ $row->thermopac_inlet ?? '' }}</td>
                        <td class="border p-1">{{ $row->thermopac_outlet ?? '' }}</td>
                        <td class="border p-1">{{ $row->d702_inlet ?? '' }}</td>
                        <td class="border p-1">{{ $row->d702_outlet ?? '' }}</td>
                        <td class="border p-1">{{ $row->d702_vacum ?? '' }}</td>
                        <td class="border p-1">{{ $row->sparging_a ?? '' }}</td>
                        <td class="border p-1">{{ $row->sparging_b ?? '' }}</td>
                        <td class="border p-1">{{ $row->e730_inlet ?? '' }}</td>
                        <td class="border p-1">{{ $row->steam_inlet ?? '' }}</td>
                        <td class="border p-1">{{ $row->pish_706 ?? '' }}</td>
                        <td class="border p-1">{{ $row->tiwh_706 ?? '' }}</td>
                        <td class="border p-1">{{ $row->f702_a ?? '' }}</td>
                        <td class="border p-1">{{ $row->f702_b ?? '' }}</td>
                        <td class="border p-1">{{ $row->f702_c ?? '' }}</td>
                        <td class="border p-1">{{ $row->oil_type_fg ?? '' }}</td>
                        <td class="border p-1">{{ $row->fit704_rpo ?? '' }}</td>
                        <td class="border p-1">{{ $row->e704 ?? '' }}</td>
                        <td class="border p-1">{{ $row->oil_type_bp ?? '' }}</td>
                        <td class="border p-1">{{ $row->fit_705_pfad ?? '' }}</td>
                        <td class="border p-1">{{ $row->e705 ?? '' }}</td>
                        <td class="border p-1">{{ $row->clarity ?? '' }}</td>
                        <td class="border p-1">{{ $row->remarks ?? '' }}</td>
                    </tr>
            @endforeach
        </tbody>
    </table>
</div>
