@if ($item->report_id)
@php
    $role = auth()->user()->roles;

    $canPrepared =
        in_array($role, ['LEAD','LEAD_QC']) &&
        $item->prepared_status !== 'Approved';

    $canApproved =
        in_array($role, ['MGR','MGR_PROD','ADM']) &&
        $item->prepared_status === 'Approved' &&
        $item->approved_status !== 'Approved';
@endphp

@if ($canPrepared || $canApproved)
<div class="flex justify-center gap-2"
     x-data="{ showApprove: false, showReject: false }">

    {{-- BUTTON --}}
    <button @click="showApprove = true"
        class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">
        Approve
    </button>

    <button @click="showReject = true"
        class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">
        Reject
    </button>

    {{-- MODAL APPROVE --}}
    <div x-show="showApprove" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white p-6 rounded shadow w-80">
            <h3 class="font-semibold mb-4">
                Approve Ticket #{{ $item->report_id }}?
            </h3>

            <div class="flex justify-end gap-2">
                <button @click="showApprove = false"
                    class="px-3 py-1 bg-gray-300 rounded">
                    Cancel
                </button>

                <form method="POST"
                      action="{{ route('daily-storage-tank-analytical.approveReport', $item->report_id) }}">
                    @csrf
                    <button class="px-3 py-1 bg-green-600 text-white rounded">
                        Approve
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL REJECT --}}
    <div x-show="showReject" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white p-6 rounded shadow w-96">
            <h3 class="font-semibold mb-3">
                Reject Ticket #{{ $item->report_id }}
            </h3>

            <form method="POST"
                  action="{{ route('daily-storage-tank-analytical.rejectReport', $item->report_id) }}">
                @csrf

                <textarea name="remark" required
                    class="w-full border rounded p-2 mb-3"
                    placeholder="Reason for rejection"></textarea>

                <div class="flex justify-end gap-2">
                    <button type="button" @click="showReject = false"
                        class="px-3 py-1 bg-gray-300 rounded">
                        Cancel
                    </button>
                    <button class="px-3 py-1 bg-red-600 text-white rounded">
                        Reject
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@else
    <span class="text-gray-400 text-xs italic">-</span>
@endif

@else
<span class="text-gray-400 text-xs italic">-</span>
@endif
