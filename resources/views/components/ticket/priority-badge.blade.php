@props([
    'priority',
])

@php
    $styles = [
        'critical' => 'danger',
        'high' => 'warning',
        'medium' => 'primary',
        'low' => 'secondary',
    ];
    $labels = \App\Models\Helpdesk\Ticket::PRIORITY_LABELS;
    $variant = $styles[$priority] ?? 'secondary';
@endphp

<span class="badge badge-{{ $variant }}">
    {{ $labels[$priority] ?? $priority }}
</span>
