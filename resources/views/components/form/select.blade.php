@props([
    'model' => null,
    'options' => [],
    'placeholder' => null,
    'placeholderValue' => null,
    'showKey' => false,
    'width' => 'max-content',
])

@php
    if (! $options instanceof \Illuminate\Support\Collection) {
        $options = collect($options);
    }

    $isAssoc = $options->isAssoc();

    $attrs = [
        'class' => 'custom-control custom-select text-sm',
    ];

    if ($model) {
        $attrs['wire:model.defer'] ??= $model;
    }

    if (! $isAssoc) {
        $options = $options
            ->mapWithKeys(fn ($v, $k) => [$v => $v])
            ->when($showKey, fn ($c) => $c->mapWithKeys(fn ($v, $k) => [$v => "{$v} - {$k}"]))
            ->all();
    }

    $styles = [
        'full-width' => 'width: 100%',
        'max-content' => 'width: max-content',
    ];
@endphp

<div class="input-group input-group-sm" style="{{ $styles[$width] }}">
    <select {{ $attributes->merge($attrs) }}>
        @if ($placeholder)
            <option hidden selected value="{{ $placeholderValue ?? $placeholder }}">
                {{ $placeholder }}
            </option>
            <option disabled>{{ $placeholder }}</option>
        @endif

        @foreach ($options as $key => $value)
            <option value="{{ $key }}" {{ $this->$model === $key ? 'selected' : null }}>
                {{ $value }}
            </option>
        @endforeach
    </select>
</div>
