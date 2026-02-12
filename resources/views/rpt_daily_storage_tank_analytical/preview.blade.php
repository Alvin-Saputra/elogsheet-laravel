@extends('layouts.app')

@section('page_title', 'Laporan Daily Storage Tank Analytical')

@section('content')
    <div class="bg-white p-6 rounded shadow-md text-sm relative">

        {{-- HEADER META --}}
        <div class="absolute top-4 right-6 text-xs leading-tight text-left">
            <div><strong>Form No.</strong> : F/QCO-001</div>
            <div><strong>Date Issued</strong> : -</div>
            <div><strong>Revision</strong> : 00</div>
            <div><strong>Rev. Date</strong> : -</div>
        </div>

        {{-- TITLE --}}
        <div class="text-center mb-4">
            <h2 class="text-lg font-bold uppercase">PT.PRISCOLIN</h2>
            <h3 class="text-xl font-bold uppercase">LOGSHEET DAILY STORAGE TANK ANALYTICAL</h3>
            <div class="mt-1">Date: {{ \Carbon\Carbon::parse($tanggal)->format('d-m-Y') }}</div>
        </div>

        {{-- TABLE --}}
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-300 text-xs">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-2 py-1">Tank No</th>
                        <th class="border px-2 py-1">Oil Type</th>
                        <th class="border px-2 py-1">Analysis Date</th>
                        <th class="border px-2 py-1">Kapasitas Tanki</th>
                        <th class="border px-2 py-1">Quantity</th>
                        <th class="border px-2 py-1">Empty Space</th>
                        <th class="border px-2 py-1">Suhu</th>
                        <th class="border px-2 py-1">FFA</th>
                        <th class="border px-2 py-1">Moisture</th>
                        <th class="border px-2 py-1">Lovibond Color R</th>
                        <th class="border px-2 py-1">Lovibond Color Y</th>
                        <th class="border px-2 py-1">IV</th>
                        <th class="border px-2 py-1">PV</th>
                        <th class="border px-2 py-1">Slip Melting Point</th>
                        <th class="border px-2 py-1">Cloud Point</th>
                        <th class="border px-2 py-1">AnV</th>
                        <th class="border px-2 py-1">B-Carotene</th>
                        <th class="border px-2 py-1">P</th>
                        <th class="border px-2 py-1">DOBI</th>
                        <th class="border px-2 py-1">Totox</th>
                        <th class="border px-2 py-1">Odor</th>
                        <th class="border px-2 py-1">Remark</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $tank)
                        @php
                            $isHighlighted = false;

                            if ($tank->analysis_date && $tanggal) {
                                $trxDate = \Carbon\Carbon::parse($tank->analysis_date)->toDateString();
                                $filterDate = \Carbon\Carbon::parse($tanggal)->toDateString();

                                $isHighlighted = $trxDate === $filterDate;
                            }
                        @endphp
                       <tr class="border-t hover:bg-gray-50 {{ $isHighlighted ? 'bg-yellow-100' : '' }}">
                            <td class="border px-2 py-1">{{ $tank->tank_no }}</td>
                            <td class="border px-2 py-1">{{ $tank->oil_type ?? '-' }}</td>
                            <td class="border px-2 py-1">
                                {{ $tank->analysis_date ? \Carbon\Carbon::parse($tank->analysis_date)->format('d-m-Y H:i') : '-' }}
                            </td>
                            <td class="border px-2 py-1">{{ $tank->capacity ?? '-' }}</td>
                            <td class="border px-2 py-1">{{ $tank->quantity ?? '-' }}</td>
                            <td class="border px-2 py-1">{{ $tank->empty_space ?? '-' }}</td>
                            <td class="border px-2 py-1">{{ $tank->suhu ?? '-' }}</td>
                            <td class="border px-2 py-1">{{ $tank->ffa ?? '-' }}</td>
                            <td class="border px-2 py-1">{{ $tank->moisture ?? '-' }}</td>
                            <td class="border px-2 py-1">{{ $tank->lov_r ?? '-' }}</td>
                            <td class="border px-2 py-1">{{ $tank->lov_y ?? '-' }}</td>
                            <td class="border px-2 py-1">{{ $tank->iv ?? '-' }}</td>
                            <td class="border px-2 py-1">{{ $tank->pv ?? '-' }}</td>
                            <td class="border px-2 py-1">{{ $tank->smp ?? '-' }}</td>
                            <td class="border px-2 py-1">{{ $tank->cloud ?? '-' }}</td>
                            <td class="border px-2 py-1">{{ $tank->anv ?? '-' }}</td>
                            <td class="border px-2 py-1">{{ $tank->beta_carotene ?? '-' }}</td>
                            <td class="border px-2 py-1">{{ $tank->p ?? '-' }}</td>
                            <td class="border px-2 py-1">{{ $tank->dobi ?? '-' }}</td>
                            <td class="border px-2 py-1">{{ $tank->totox ?? '-' }}</td>
                            <td class="border px-2 py-1">{{ $tank->odor ?? '-' }}</td>
                            <td class="border px-2 py-1"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- SIGN --}}
        <div class="flex justify-center gap-24 mt-10 text-xs text-center">
            <div>
                Prepared By<br><br><br>
                {{-- Use $sign instead of $tank --}}
                {{ optional($sign->preparedByUser ?? null)->roles ?? 'QC Operator' }}<br>

                <span class="font-bold underline">
                    {{ optional($sign->preparedByUser ?? null)->fullname ?? ($sign->prepared_by ?? '________________') }}
                </span>
                <br>

                {{-- Use $sign->prepared_date --}}
                <small>
                    {{ isset($sign->prepared_date) ? \Carbon\Carbon::parse($sign->prepared_date)->format('d M Y H:i') : '-' }}
                </small>
            </div>

            <div>
                Approved By<br><br><br>
                {{-- Use $sign instead of $tank --}}
                {{ optional($sign->approvedByUser ?? null)->roles ?? 'QC Supervisor' }}<br>

                <span class="font-bold underline">
                    {{ optional($sign->approvedByUser ?? null)->fullname ?? ($sign->approved_by ?? '________________') }}
                </span>
                <br>

                {{-- Use $sign->approved_date --}}
                <small>
                    {{ isset($sign->approved_date) ? \Carbon\Carbon::parse($sign->approved_date)->format('d M Y H:i') : '-' }}
                </small>
            </div>
        </div>

        <div class="mt-6 text-center text-xs italic text-gray-600">
            Dokumen ini telah disetujui secara elektronik melalui sistem E-Logsheet.
        </div>
    </div>
@endsection
