@props([
    'status',
])

@php
    $styles = [
        'open' => 'primary',
        'progress' => 'warning',
        'waiting' => 'info',
        'resolved' => 'success',
        'closed' => 'secondary',
    ];
    $labels = \App\Models\Helpdesk\Ticket::STATUS_LABELS;
    $variant = $styles[$status] ?? 'secondary';
@endphp

<span class="badge badge-{{ $variant }}">
    {{ $labels[$status] ?? $status }}
</span>
