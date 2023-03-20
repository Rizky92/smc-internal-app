@props([
    'emit',
    'params' => null,
])

<x-table.td>
    {{ $slot }}
    <a href="#" style="position: absolute; left: 0; right: 0; top: 0; bottom: 0;" wire:click.prevent="$emit('{{ $emit }}', {{ \Illuminate\Support\Js::from($params) }})"></a>
</x-table.td>
