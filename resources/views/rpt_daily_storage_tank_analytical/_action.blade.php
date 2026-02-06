@if ($item->report_id)
@php
    $role = auth()->user()->roles;

    // PERBAIKAN LOGIKA DI SINI
    // Tombol Lead hanya muncul jika prepared_status masih KOSONG (null)
    $canPrepared =
        in_array($role, ['LEAD','LEAD_QC']) &&
        $item->prepared_status === null; 

    // Tombol Mgr hanya muncul jika Lead sudah Approve DAN approved_status masih KOSONG (null)
    $canApproved =
        in_array($role, ['MGR','MGR_QC','ADM']) &&
        $item->prepared_status === 'Approved' &&
        $item->approved_status === null; 
@endphp

@if ($canPrepared || $canApproved)
<div class="flex justify-center gap-2"
     x-data="{ showApprove: false, showReject: false }">

    {{-- BUTTON TRIGGER --}}
    <button type="button" @click="showApprove = true"
        class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">
        Approve
    </button>

    <button type="button" @click="showReject = true"
        class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">
        Reject
    </button>

    {{-- MODAL APPROVE (Dengan x-teleport agar tidak terpotong tabel) --}}
    <template x-teleport="body">
        <div x-show="showApprove" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white p-6 rounded shadow w-80" @click.away="showApprove = false">
                <h3 class="font-semibold mb-4 text-lg">
                    Approve Ticket #{{ $item->report_id }}?
                </h3>

                <div class="flex justify-end gap-2">
                    <button type="button" @click="showApprove = false"
                        class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400">
                        Cancel
                    </button>

                    <form method="POST"
                          action="{{ route('daily-storage-tank-analytical.approveReport', $item->report_id) }}">
                        @csrf
                        <button class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">
                            Approve
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </template>

    {{-- MODAL REJECT (Dengan x-teleport agar tidak terpotong tabel) --}}
    <template x-teleport="body">
        <div x-show="showReject" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white p-6 rounded shadow w-96" @click.away="showReject = false">
                <h3 class="font-semibold mb-3 text-lg">
                    Reject Ticket #{{ $item->report_id }}
                </h3>

                <form method="POST"
                      action="{{ route('daily-storage-tank-analytical.rejectReport', $item->report_id) }}">
                    @csrf

                    <textarea name="remark" required
                        class="w-full border rounded p-2 mb-3 focus:ring focus:ring-red-200"
                        placeholder="Reason for rejection"></textarea>

                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showReject = false"
                            class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400">
                            Cancel
                        </button>
                        <button class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700">
                            Reject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

</div>
@else
    {{-- Tampilkan status teks jika user tidak bisa melakukan aksi --}}
    <span class="text-gray-400 text-xs italic">
        @if($role == 'LEAD' || $role == 'LEAD_QC')
            {{ $item->prepared_status ?? '-' }}
        @elseif(in_array($role, ['MGR','MGR_QC','ADM']))
            {{ $item->approved_status ?? '-' }}
        @else
            -
        @endif
    </span>
@endif

@else
<span class="text-gray-400 text-xs italic">-</span>
@endif