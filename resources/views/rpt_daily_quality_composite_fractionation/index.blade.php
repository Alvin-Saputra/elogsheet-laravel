@extends('layouts.app')

@section('page_title', 'Laporan Daily Quality Composite Fractionation')

@section('content')
<div class="bg-white p-6 rounded shadow-md">

    {{-- ================= HEADER ================= --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path d="M3 3v18h18M16 8l-4 4-4-4M16 16l-4 4-4-4" />
            </svg>
            <div>
                <h2 class="text-lg font-semibold text-gray-800">
                    Daily Quality Composite Fractionation
                </h2>
                <span class="inline-block px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded">
                    F/QCO-003
                </span>
            </div>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('daily-quality-composite-fractionation.export.view', request()->query()) }}"
                class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg">
                View Layout
            </a>
            <a href="{{ route('daily-quality-composite-fractionation.export.pdf', request()->query()) }}"
                class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg">
                Download PDF
            </a>
        </div>
    </div>

    {{-- ================= FILTER ================= --}}
    <form method="GET" action="{{ route('daily-quality-composite-fractionation.index') }}"
        class="bg-gray-50 p-4 rounded mb-4 flex flex-wrap gap-4 items-end">

        <div>
            <label class="block text-sm font-medium">Tanggal Operasional</label>
            <input type="date" name="filter_tanggal" value="{{ $tanggal }}"
                class="border rounded px-2 py-1 text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium">Jam</label>
            <select name="filter_jam" class="border rounded px-2 py-1 text-sm">
                <option value="">Semua</option>
                @for ($i = 0; $i < 24; $i++)
                    @php $j = sprintf('%02d:00', $i); @endphp
                    <option value="{{ $j }}" {{ $jam === $j ? 'selected' : '' }}>
                        {{ $j }}
                    </option>
                @endfor
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium">Work Center</label>
            <select name="filter_work_center" class="border rounded px-2 py-1 text-sm">
                <option value="">Semua</option>
                @foreach ($listWorkCenters as $wc)
                    <option value="{{ $wc->work_center }}"
                        {{ $workCenter === $wc->work_center ? 'selected' : '' }}>
                        {{ $wc->label }}
                    </option>
                @endforeach
            </select>
        </div>

        <button class="px-4 py-2 bg-gray-800 text-white text-sm rounded">
            Filter
        </button>
    </form>

    {{-- ================= INFO ================= --}}
    <div class="mb-4 p-3 rounded bg-blue-50 text-blue-700 text-sm">
        Hari Operasional:
        {{ \Carbon\Carbon::parse($tanggal)->format('d/m/Y') }} 08:00 –
        {{ \Carbon\Carbon::parse($tanggal)->addDay()->format('d/m/Y') }} 07:59
    </div>

    {{-- ================= TABLE ================= --}}
    <div class="overflow-x-auto">
        <table class="min-w-full border text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-2 py-1">No</th>
                    <th class="border px-2 py-1">ID</th>
                    <th class="border px-2 py-1">Tanggal</th>
                    <th class="border px-2 py-1">Jam</th>
                    <th class="border px-2 py-1">Work Center</th>
                    <th class="border px-2 py-1">Crystalizer</th>
                    <th class="border px-2 py-1">Prepared</th>
                    <th class="border px-2 py-1">Checked</th>
                    <th class="border px-2 py-1">Action</th>
                    <th class="border px-2 py-1">Detail</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($data as $item)
                <tr class="hover:bg-gray-50">
                    <td class="border px-2 py-1">{{ $loop->iteration }}</td>
                    <td class="border px-2 py-1">{{ $item->id }}</td>
                    <td class="border px-2 py-1">
                        {{ \Carbon\Carbon::parse($item->transaction_date)->format('d/m/Y') }}
                    </td>
                    <td class="border px-2 py-1">{{ $item->time }}</td>
                    <td class="border px-2 py-1">{{ $item->work_center }}</td>
                    <td class="border px-2 py-1">{{ $item->crystalizer }}</td>

                    {{-- STATUS --}}
                    <td class="border px-2 py-1 text-center">
                        {{ $item->prepared_status ?? 'Pending' }}
                    </td>
                    <td class="border px-2 py-1 text-center">
                        {{ $item->checked_status ?? 'Pending' }}
                    </td>

                    {{-- ================= ACTION ================= --}}
                    <td class="border px-2 py-1 text-center">
                        <div x-data="{ a:false, r:false }" class="flex justify-center gap-1">

                            {{-- LEAD --}}
                            @if (
                                in_array(auth()->user()->roles, ['LEAD','LEAD_QC'])
                                && is_null($item->prepared_status)
                            )
                                <button @click="a=true"
                                    class="bg-green-600 text-white px-2 py-1 text-xs rounded">
                                    Approve
                                </button>
                                <button @click="r=true"
                                    class="bg-red-600 text-white px-2 py-1 text-xs rounded">
                                    Reject
                                </button>
                            @endif

                            {{-- MANAGER --}}
                            @if (
                                in_array(auth()->user()->roles, ['MGR','MGR_PROD','ADM'])
                                && $item->prepared_status === 'Approved'
                                && is_null($item->checked_status)
                            )
                                <button @click="a=true"
                                    class="bg-green-600 text-white px-2 py-1 text-xs rounded">
                                    Approve
                                </button>
                                <button @click="r=true"
                                    class="bg-red-600 text-white px-2 py-1 text-xs rounded">
                                    Reject
                                </button>
                            @endif

                            {{-- APPROVE MODAL --}}
                            <div x-show="a" x-cloak
                                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                                <div class="bg-white p-4 rounded">
                                    <p>Approve ticket #{{ $item->id }}?</p>
                                    <form method="POST"
                                        action="{{ route('daily-quality-composite-fractionation.approveReport', $item->id) }}">
                                        @csrf
                                        <div class="mt-3 flex gap-2 justify-end">
                                            <button type="button" @click="a=false">Cancel</button>
                                            <button class="bg-green-600 text-white px-3 py-1 rounded">
                                                Approve
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            {{-- REJECT MODAL --}}
                            <div x-show="r" x-cloak
                                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                                <div class="bg-white p-4 rounded w-80">
                                    <form method="POST"
                                        action="{{ route('daily-quality-composite-fractionation.rejectReport', $item->id) }}">
                                        @csrf
                                        <textarea name="remark" required
                                            class="w-full border rounded p-1 text-sm"
                                            placeholder="Reason"></textarea>
                                        <div class="mt-3 flex gap-2 justify-end">
                                            <button type="button" @click="r=false">Cancel</button>
                                            <button class="bg-red-600 text-white px-3 py-1 rounded">
                                                Reject
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                        </div>
                    </td>

                    <td class="border px-2 py-1 text-center">
                        <a href="{{ route('daily-quality-composite-fractionation.show', $item->id) }}"
                            class="text-blue-600">
                            Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-6 text-gray-500">
                        No data available
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
