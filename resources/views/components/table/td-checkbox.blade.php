@props([
    'model' => null,
    'modelKey' => null,
    'title' => null,
    'prefix' => null,
])

@php
    $id = str($title)
        ->kebab()
        ->prepend($prefix);

    $wireModel = $model . '.' . $modelKey;
@endphp

<x-table.td>
    <input id="{{ $id }}" type="checkbox" wire:model.defer="{{ $wireModel }}">
    <label for="{{ $id }}" style="position: absolute; top: 0; bottom: 0; left: 0; right: 0; cursor: pointer; margin: 0"></label>
</x-table.td>
