@props(['reportId', 'approveRoute', 'rejectRoute', 'userRole', 'disableApprove' => false, 'disableReject' => false])

<div class="flex justify-center gap-2" x-data="{ showApprove: false, showReject: false }">

    {{-- Approve Button --}}
    <button @click="showApprove = true"
        class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 shadow
        {{ $disableApprove ? 'opacity-50 cursor-not-allowed' : '' }}"
        {{ $disableApprove ? 'disabled' : '' }}>
        Approve
    </button>

    {{-- Reject Button --}}
    <button @click="showReject = true"
        class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 shadow
        {{ $disableReject ? 'opacity-50 cursor-not-allowed' : '' }}"
        {{ $disableReject ? 'disabled' : '' }}>
        Reject
    </button>

    {{-- Modal Approve --}}
    <div x-show="showApprove" x-transition
        class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50"
        style="display:none;">
        <div class="bg-white p-6 rounded shadow-lg max-w-sm w-full mx-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Konfirmasi Approve</h2>
            <p class="text-sm text-gray-600 mb-6">Apakah Anda yakin ingin <b>Approve</b> tiket ini?</p>
            <div class="flex justify-end space-x-2">
                <button @click="showApprove = false"
                    class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                    Batal
                </button>
                <form method="POST" action="{{ $approveRoute }}" class="inline">
                    @csrf
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-green-600 text-white rounded hover:bg-green-700">
                        Approve
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Reject --}}
    <div x-show="showReject" x-transition
        class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50"
        style="display:none;">
        <div class="bg-white p-6 rounded shadow-lg max-w-sm w-full mx-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Konfirmasi Reject</h2>
            <p class="text-sm text-gray-600 mb-4">Silakan masukkan alasan reject tiket ini:</p>
            <form method="POST" action="{{ $rejectRoute }}" class="space-y-4">
                @csrf
                {{-- Textarea alasan reject --}}
                <textarea name="remark" rows="3"
                    class="w-full border rounded p-2 text-sm focus:ring-red-500 focus:border-red-500"
                    placeholder="Tuliskan alasan reject..."></textarea>

                <div class="flex justify-end space-x-2">
                    <button type="button" @click="showReject = false"
                        class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-red-600 text-white rounded hover:bg-red-700">
                        Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
