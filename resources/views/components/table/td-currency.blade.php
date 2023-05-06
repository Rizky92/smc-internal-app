@props(['currency' => 'Rp.', 'value' => null, 'default' => null])

<x-table.td style="width: 2ch">{{ $currency }}</x-table.td>
<x-table.td class="text-right">{{ !is_null($value) ? number_format($value, 0, ',', '.') : $default }}</x-table.td>