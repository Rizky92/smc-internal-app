@props([
    'model',
    'title',
    'id' => null,
])

<div {{ $attributes->merge(['class' => 'custom-control custom-switch']) }}>
    <input class="custom-control-input" id="{{ $id ?? Str::slug($title) }}" type="checkbox" wire:model.defer="{{ $model }}" />
    <label class="custom-control-label text-sm" for="{{ $id ?? Str::slug($title) }}">
        {{ $title }}
    </label>
</div>
