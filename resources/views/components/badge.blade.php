@props(['variant' => 'secondary'])

@php
    $class = 'badge ';

    $class .= [
        'primary' => 'badge-primary',
        'secondary' => 'badge-secondary',
        'success' => 'badge-success',
        'info' => 'badge-info',
        'warning' => 'badge-warning',
        'danger' => 'badge-danger',
        'light' => 'badge-light',
        'dark' => 'badge-dark',
    ][$variant];
@endphp

<span {{ $attributes->merge(['class' => $class]) }}>{{ $slot }}</span>
