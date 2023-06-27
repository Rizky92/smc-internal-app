@props([
    'constantWidth' => false,
])

@php
    $attrs = ['class' => 'text-sm'];

    if ($constantWidth) {
        $attrs = array_merge($attrs, ['style' => 'width: 5rem']);
    }
@endphp

<span {{ $attributes->merge($attrs) }}>{{ $slot }}</span>
