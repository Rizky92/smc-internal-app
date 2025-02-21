@props([
    'model' => null,
    'options' => [],
    'placeholder' => null,
    'selected' => null,
    'showKey' => false,
])

@php
    if (! $options instanceof \Illuminate\Support\Collection) {
        $options = collect($options);
    }

    $isList = $options->isList();

    $attrs = [
        'class' => 'custom-control custom-select text-sm',
    ];

    if ($model) {
        $attrs['wire:model.defer'] ??= $model;
    }

    $options = $options
        ->when($isList, fn ($c) => $c->mapWithKeys(fn ($v, $k) => [$v => $v]))
        ->when($showKey, fn ($c) => $c->mapWithKeys(fn ($v, $k) => [$k => "{$k} - {$v}"]))
        ->all();
@endphp

<div class="input-group input-group-sm" style="width: max-content">
    <select {{ $attributes->merge($attrs) }}>
        @if ($placeholder)
            <option selected value="{{ $placeholderValue ?? $placeholder }}">
                {{ $placeholder }}
            </option>
        @endif

        @foreach ($options as $key => $value)
            <option value="{{ $key }}" {{ $selected === $key ? 'selected' : null }}>
                {{ $value }}
            </option>
        @endforeach
    </select>
</div>
