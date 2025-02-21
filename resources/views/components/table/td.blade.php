@props([
    'clickable' => false,
    'funcName' => 'loadData',
])

@php($rowspan = $attributes->get('rowspan'))

<td {{ $attributes->whereDoesntStartWith('data-')->when($rowspan == '0', fn ($attr) => $attr->except('rowspan')) }}>
    {{ $slot }}
    @if ($clickable)
        <button
            {{
                $attributes->whereStartsWith('data-')->merge([
                    'style' => 'position: absolute; inset: 0; background-color: transparent; border-width: 0',
                    'type' => 'button',
                ])
            }}
            onclick="{{ $funcName }}(this); document.documentElement.scrollTop = 0;"></button>
    @endif
</td>
