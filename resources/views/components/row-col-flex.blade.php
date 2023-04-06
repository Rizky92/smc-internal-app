@props([
    'justifyContent' => 'start',
])

@php
    $justifyContentPosition = [
        'start' => 'justify-content-start',
        'end' => 'justify-content-end',
        'between' => 'justify-content-between',
        'around' => 'justify-content-around',
        'center' => 'justify-content-center',
    ];

    $class = Arr::toCssClasses([
        'd-flex',
        $justifyContentPosition[$justifyContent],
        'align-items-center',
    ]);
@endphp

<x-row-col :attributes="$attributes">
    <div class="{{ $class }}">
        {{ $slot }}
    </div>
</x-row-col>