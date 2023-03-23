@props([
    'clickable' => false,
    'funcName' => 'loadData',
])

@php($rowspan = $attributes->get('rowspan'))

<td {{ $attributes->whereDoesntStartWith('data-')->when($rowspan == "0", fn ($attr) => $attr->except('rowspan')) }}>
    {{ $slot }}
    @if ($clickable)
        <a {{ $attributes->whereStartsWith('data-')->merge([
            'style' => 'position: absolute; left: 0; right: 0; top: 0; bottom: 0',
            'href' => '#',
        ]) }} onclick="{{ $funcName }}(this)"></a>
    @endif
</td>
