@props([
    'livewire' => false,
    'model' => null,
    'key' => (string) null,
    'id' => null,
    'prefix' => null,
    'obscureCheckbox' => false,
    'onchange' => null,
])

@php
    $id = str($id)->prepend($prefix);

    $wireModel = $model . '.' . $key;
@endphp

<x-table.td :attributes="$attributes->when($obscureCheckbox, fn ($attr) => $attr->merge(['style' => 'position: relative; z-index: 10']))">
    <input id="{{ $id }}" type="checkbox" name="{{ $key }}" @if ($livewire) wire:model.defer="{{ $wireModel }}" @if (! is_null($onchange)) onchange="{{ $onchange }}" @endif @endif />
    <label for="{{ $id }}" style="position: absolute; top: 0; bottom: 0; left: 0; right: 0; cursor: pointer; margin: 0"></label>
</x-table.td>
