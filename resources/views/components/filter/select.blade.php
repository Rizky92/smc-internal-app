@props([
    'name',
    'model' => null,
    'options' => [],
    'placeholder' => null,
    'placeholderValue' => null,
    'selected' => null,
])

@php
    $id = Str::slug($name);
    $title = Str::camel($name);

    $isAssoc = Arr::isAssoc($options);

    $attrs = [
        'class' => 'custom-control custom-select text-sm',
        'style' => 'width: max-content',
    ];

    if ($model) {
        $attrs['wire:model.defer'] ??= $model;
    }
    
    if (! $isAssoc) {
        $options = collect($options)
            ->mapWithKeys(fn($v, $k) => [$v => $v])
            ->all();
    }
@endphp

<select {{ $attributes->merge($attrs) }}>
    @if ($placeholder)
        <option value="" disabled selected>{{ $placeholder }}</option>
    @endif
    @foreach ($options as $value => $name)
        <option value="{{ $value }}" {{ $selected === $value ? 'selected' : null }}>{{ $isAssoc ? "{$value} - {$name}" : $name }}</option>
    @endforeach
</select>
