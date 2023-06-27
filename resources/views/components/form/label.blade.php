@props([
    'constantWidth' => false,
])

@php
    $attrs = ['class' => 'text-sm'];

    if ($constantWidth) {
        $attrs = array_merge($attrs, ['style' => 'width: 10rem']);
    }
@endphp

<span {{ $attributes->merge($attrs) }}>{{ $slot }}</span>
