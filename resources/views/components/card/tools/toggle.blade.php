@props([
    'model',
    'name',
])

<div class="ml-4 custom-control custom-switch">
    <input class="custom-control-input" id="{{ $model }}" type="checkbox" wire:model.defer="{{ $model }}">
    <label class="custom-control-label text-sm" for="{{ $model }}">{{ $name }}</label>
</div>
