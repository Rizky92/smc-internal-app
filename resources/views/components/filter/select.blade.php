@props(['model', 'options' => [], 'placeholder' => null, 'constantWidth' => false])

@php
    $attrs = [
        'class' => 'custom-control custom-select',
        'wire:model.defer' => $model,
    ];
    if ($constantWidth) {
        $attrs = array_merge($attrs, ['style' => 'width: 10rem']);
    }
@endphp

<select {{ $attributes->merge($attrs) }}>
    @if ($placeholder)
        <option value="" disabled selected>{{ $placeholder }}</option>
    @endif
    @foreach ($options as $value => $name)
        <option value="{{ $value }}">{{ $name }}</option>
    @endforeach
</select>
