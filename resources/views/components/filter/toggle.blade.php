@props([
    'model',
    'title',
])

<div {{ $attributes->merge(['class' => 'custom-control custom-switch']) }}>
    <input class="custom-control-input" id="{{ $model }}" type="checkbox" wire:model.defer="{{ $model }}">
    <label class="custom-control-label text-sm" for="{{ $model }}">{{ $title }}</label>
</div>
