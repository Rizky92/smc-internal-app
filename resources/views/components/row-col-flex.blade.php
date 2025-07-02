@props([
    'justifyContent' => 'start',
    'colGap' => 0,
    'rowGap' => 0,
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

    $gap = collect();

    if ($colGap > 0) {
        $gap->push("column-gap: {$colGap}");
    }

    if ($rowGap > 0) {
        $gap->push("row-gap: {$rowGap}");
    }
@endphp

<x-row-col :attributes="$attributes">
    <div class="{{ $class }}" @if ($gap->isNotEmpty()) style="{{ $gap->join('; ') }}" @endif>
        {{ $slot }}
    </div>
</x-row-col>
