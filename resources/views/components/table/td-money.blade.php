@props(['currency' => 'Rp.', 'value' => null])

<x-table.td {{ $attributes->merge(['style' => 'width: 2ch']) }}>{{ $currency }}</x-table.td>
<x-table.td {{ $attributes->merge(['style' => 'max-width: max-content'])->class('text-right') }}>{{ money($value, 2) }}</x-table.td>
