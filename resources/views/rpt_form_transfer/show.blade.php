@extends('layouts.app')

@section('page_title', 'Detail Form Transfer: ' . $transfer->id)

@section('content')
    <div class="bg-white p-6 rounded shadow-md max-w-6xl mx-auto">
        <div class="mb-4">
            <a href="{{ route('report.form-transfer.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 text-sm font-semibold rounded-lg shadow transition">
                &larr; Back to List
            </a>
        </div>

        <h2 class="text-2xl font-semibold text-gray-800 mb-4">
            Transfer ID: <span class="text-blue-600">{{ $transfer->id }}</span>
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 text-sm">
            <div>
                <strong class="text-gray-600 block">Transaction Date:</strong>
                <span>{{ $transfer->transaction_date ? \Carbon\Carbon::parse($transfer->transaction_date)->format('Y-m-d') : '-' }}</span>
            </div>
            <div>
                <strong class="text-gray-600 block">Company:</strong>
                <span>{{ $transfer->company ?? '-' }}</span>
            </div>
            <div>
                <strong class="text-gray-600 block">Plant:</strong>
                <span>{{ $transfer->plant ?? '-' }}</span>
            </div>
            <div>
                <strong class="text-gray-600 block">From Dept:</strong>
                <span>{{ $transfer->from_dept ?? '-' }}</span>
            </div>
            <div>
                <strong class="text-gray-600 block">To Dept:</strong>
                <span>{{ $transfer->to_dept ?? '-' }}</span>
            </div>
            <div>
                <strong class="text-gray-600 block">Form No:</strong>
                <span>{{ $transfer->form_no ?? '-' }}</span>
            </div>
            <div>
                <strong class="text-gray-600 block">Date Issued:</strong>
                <span>{{ $transfer->date_issued ? \Carbon\Carbon::parse($transfer->date_issued)->format('Y-m-d') : '-' }}</span>
            </div>
            <div>
                <strong class="text-gray-600 block">Revision No:</strong>
                <span>{{ $transfer->revision_no ?? '-' }}</span>
            </div>
            <div>
                <strong class="text-gray-600 block">Revision Date:</strong>
                <span>{{ $transfer->revision_date ? \Carbon\Carbon::parse($transfer->revision_date)->format('Y-m-d') : '-' }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 text-sm">
            <div>
                <strong class="text-gray-600 block mb-1">Verified Status (Lead):</strong>
                @include('partials.status-badge', ['status' => $transfer->prepared_status])
            </div>
            <div>
                <strong class="text-gray-600 block mb-1">Approved Status (Manager):</strong>
                @include('partials.status-badge', ['status' => $transfer->approved_status])
            </div>
        </div>

        <div class="overflow-x-auto">
            @include('rpt_form_transfer._table', ['details' => $transfer->details])
        </div>
    </div>
@endsection
