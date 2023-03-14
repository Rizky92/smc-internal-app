@props(['variant' => 'secondary'])

@php
    $class = collect(['badge']);

    $variants = [
        'primary' => 'badge-primary',
        'secondary' => 'badge-secondary',
        'success' => 'badge-success',
        'info' => 'badge-info',
        'warning' => 'badge-warning',
        'danger' => 'badge-danger',
        'light' => 'badge-light',
        'dark' => 'badge-dark',
    ];

    $class = $class->push($variants[$variant])->join(' ');
@endphp

<span {{ $attributes->merge(['class' => $class]) }}>{{ $slot }}</span>