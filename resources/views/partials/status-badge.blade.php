@php
    $label = $status ?: 'Pending';

    switch ($label) {
        case 'Approved':
            $bg = 'green';
            break;
        case 'Rejected':
            $bg = 'red';
            break;
        case 'Pending':
        case null:
        case '':
            $bg = 'gray';
            break;
        default:
            $bg = 'yellow';
            break;
    }
@endphp

<span class="inline-block px-2 py-1 text-xs font-semibold rounded bg-{{ $bg }}-100 text-{{ $bg }}-800">
    {{ $label }}
</span>
