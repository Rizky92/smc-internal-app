@props([
    'model' => null,
    'options' => [],
    'placeholder' => null,
    'placeholderValue' => null,
    'selected' => null,
    'showKey' => false,
])

@php
    $isAssoc = Arr::isAssoc($options);
    
    $attrs = [
        'class' => 'custom-control custom-select text-sm',
    ];
    
    if ($model) {
        $attrs['wire:model.defer'] ??= $model;
    }
    
    if (!$isAssoc) {
        $options = collect($options)
            ->mapWithKeys(fn($v, $k) => [$v => $v])
            ->when($showKey, fn ($c) => $c->mapWithKeys(fn ($v, $k) => [$v => "{$v} - {$k}"]))
            ->all();
    }
@endphp

<div class="input-group input-group-sm" style="width: max-content">
    <select {{ $attributes->merge($attrs) }}>
        @if ($placeholder)
            <option value="" disabled selected>{{ $placeholder }}</option>
        @endif
        @foreach ($options as $key => $value)
            <option value="{{ $key }}" {{ $selected === $key ? 'selected' : null }}>{{ $value }}</option>
        @endforeach
    </select>
</div>
