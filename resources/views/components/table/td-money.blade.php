@props(['currency' => 'Rp.', 'value' => null])

<x-table.td style="width: 2ch">{{ $currency }}</x-table.td>
<x-table.td class="text-right" style="max-width: max-content">
    {{ money($value, 2) }}
</x-table.td>
