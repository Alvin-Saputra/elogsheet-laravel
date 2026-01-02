@extends('layouts.app')

@section('page_title', 'Analytical Result Outgoing Shipment - Preview')

@section('content')

@php
    // display date fallback
    $displayDate = $header->loading_date ?? $header->entry_date ?? null;

    // convert value to float if possible (handles strings with commas)
    $toFloat = function ($v) {
        if ($v === null || $v === '') return null;
        $s = str_replace([',', ' '], ['', ''], (string) $v);
        return is_numeric($s) ? (float)$s : null;
    };

    // pick first non-empty property from a list of keys
    $firstValue = function ($obj, array $keys) {
        foreach ($keys as $k) {
            // prefer property access, then array access (in case detail is array)
            if (is_object($obj) && isset($obj->$k) && $obj->$k !== null && $obj->$k !== '') {
                return $obj->$k;
            }
            if (is_array($obj) && array_key_exists($k, $obj) && $obj[$k] !== null && $obj[$k] !== '') {
                return $obj[$k];
            }
        }
        return null;
    };

    // format helper: returns '-' when null
    $fmt = function ($val, $decimals = 3) use ($toFloat) {
        $n = $toFloat($val);
        return $n !== null ? number_format($n, $decimals) : '-';
    };

    $details = $header->details ?? collect([]);
@endphp

<style>
    .analytical-table th,
    .analytical-table td {
        border: 1px solid #222;
        padding: 6px;
        font-size: 12px;
    }
    .analytical-table thead th {
        background: #efefef;
        text-align: center;
        font-weight: 600;
    }
    .small { font-size: 12px; }
</style>

<div class="max-w-4xl mx-auto bg-white p-6 rounded shadow-sm">

    {{-- HEADER --}}
    <table class="w-full mb-3">
        <tr>
            <td class="w-1/4 align-top">
                <div class="font-bold text-lg">PT. PRISCOLIN</div>
                <div class="text-sm">BEKASI</div>
            </td>

            <td class="w-1/2 text-center align-top">
                <h2 class="text-xl font-bold uppercase leading-tight">
                    Analytical Result of Out Going Shipment<br>
                    Product by Truck
                </h2>
            </td>

            <td class="w-1/4 align-top text-right small">
                <div class="border p-2 inline-block text-left">
                    <div><strong>Form No</strong> : {{ $header->form_no ?? 'F/QCO-013' }}</div>
                    <div><strong>Issued Date</strong> : {{ $header->entry_date ? \Carbon\Carbon::parse($header->entry_date)->format('ymd') : '-' }}</div>
                    <div><strong>Rev.</strong> : {{ $header->revision_no ?? '-' }}</div>
                    <div><strong>Rev. Date</strong> : {{ $header->revision_date ? \Carbon\Carbon::parse($header->revision_date)->format('ymd') : '-' }}</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- INFO --}}
    <div class="border border-gray-400 p-3 rounded mb-4 small">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <div class="flex"><strong class="w-36">Loading Date</strong>
                    <span class="ml-2">{{ $displayDate ? \Carbon\Carbon::parse($displayDate)->format('d M Y H:i') : '-' }}</span>
                </div>
                <div class="flex mt-1"><strong class="w-36">Product Name</strong>
                    <span class="ml-2">{{ $header->product_name ?? '-' }}</span>
                </div>
                <div class="flex mt-1"><strong class="w-36">Quantity</strong>
                    <span class="ml-2">{{ $header->quantity ?? ($header->qty ?? '-') }}</span>
                </div>
            </div>

            <div>
                <div class="flex"><strong class="w-36">Ship's Name</strong>
                    <span class="ml-2">{{ $header->ships_name ?? $header->ship_name ?? '-' }}</span>
                </div>
                <div class="flex mt-1"><strong class="w-36">Destination</strong>
                    <span class="ml-2">{{ $header->destination ?? '-' }}</span>
                </div>
                <div class="flex mt-1"><strong class="w-36">Load Port</strong>
                    <span class="ml-2">{{ $header->load_port ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- TABLE --}}
    <table class="w-full analytical-table mb-4">
        <thead>
            <tr>
                <th>Ship's Tank</th>
                <th>No. Police</th>
                <th>FFA %</th>
                <th>M&I %</th>
                <th>IV</th>
                <th>Lovibond Red</th>
                <th>Lovibond Yellow</th>
                <th>PV</th>
                <th>Other</th>
                <th>Remark</th>
            </tr>
        </thead>

        <tbody>
            @forelse($details as $d)
                @php
                    // ships_tank column in DB is "ships_tank" (note plural)
                    $shipTank = $firstValue($d, ['ships_tank', 'ship_tank', 'ship_tank_no', 'tank']);
                    $noPolice = $firstValue($d, ['no_police', 'police_no', 'police']);
                    $ffaRaw   = $firstValue($d, ['ffa', 'ffa_percent', 'ffa_pct']);
                    $miRaw    = $firstValue($d, ['m_and_i', 'm_i', 'm_and_i_percent', 'm_i_percent']);
                    $ivRaw    = $firstValue($d, ['iv', 'iv_value']);
                    // lovibond actual DB columns: lovibond_color_red, lovibond_color_yellow
                    $lovRedRaw = $firstValue($d, ['lovibond_color_red', 'lovibond_red', 'lov_red', 'lovibond_r']);
                    $lovYelRaw = $firstValue($d, ['lovibond_color_yellow', 'lovibond_yellow', 'lov_yel', 'lovibond_y']);
                    $pvRaw    = $firstValue($d, ['pv', 'peroxide_value']);
                    $other    = $firstValue($d, ['other', 'notes']);
                    $remark   = $firstValue($d, ['remark', 'remarks']);
                @endphp

                <tr>
                    <td class="px-2 py-1 small">{{ $shipTank ?? '-' }}</td>
                    <td class="px-2 py-1 small">{{ $noPolice ?? '-' }}</td>

                    <td class="px-2 py-1 text-center small">{{ $fmt($ffaRaw, 3) }}</td>
                    <td class="px-2 py-1 text-center small">{{ $fmt($miRaw, 3) }}</td>
                    <td class="px-2 py-1 text-center small">{{ $fmt($ivRaw, 3) }}</td>

                    {{-- Lovibond colors usually displayed with 2 decimals --}}
                    <td class="px-2 py-1 text-center small">{{ $fmt($lovRedRaw, 2) }}</td>
                    <td class="px-2 py-1 text-center small">{{ $fmt($lovYelRaw, 2) }}</td>

                    <td class="px-2 py-1 text-center small">{{ $fmt($pvRaw, 3) }}</td>
                    <td class="px-2 py-1 small">{{ $other ?? '-' }}</td>
                    <td class="px-2 py-1 small">{{ $remark ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center py-4 italic text-gray-600 small">No data available</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- SIGNATURES --}}
    <div class="grid grid-cols-2 gap-6 mt-6 text-center small">
        <div>
            <div class="font-semibold">Corrected by</div>
            <div class="text-[12px]">(QC Leader)</div>
            <div class="h-12"></div>
            <div class="font-medium">{{ $header->prepared_by ?? $header->corrected_by ?? '____________________' }}</div>
            <div class="text-xs text-gray-600">
                {{ $header->prepared_date ? \Carbon\Carbon::parse($header->prepared_date)->format('d-m-Y H:i') : ($header->corrected_date ? \Carbon\Carbon::parse($header->corrected_date)->format('d-m-Y H:i') : '') }}
            </div>
        </div>

        <div>
            <div class="font-semibold">Approved by</div>
            <div class="text-[12px]">(QC Head)</div>
            <div class="h-12"></div>
            <div class="font-medium">{{ $header->approved_by ?? '____________________' }}</div>
            <div class="text-xs text-gray-600">
                {{ $header->approved_date ? \Carbon\Carbon::parse($header->approved_date)->format('d-m-Y H:i') : '' }}
            </div>
        </div>
    </div>

</div>

@endsection
