@props([
    'livewire' => false,
    'model' => null,
    'key' => (string) null,
    'id' => null,
    'prefix' => null,
])

@php
    $id = str($id)
        ->prepend($prefix);

    $wireModel = $model . '.' . $key;
@endphp

<x-table.td :attributes="$attributes">
    <input id="{{ $id }}" type="checkbox" name="{{ $key }}" @if ($livewire) wire:model.defer="{{ $wireModel }}" @endif>
    <label for="{{ $id }}" style="position: absolute; top: 0; bottom: 0; left: 0; right: 0; cursor: pointer; margin: 0"></label>
</x-table.td>
