@props([
    'clickable' => false,
])

<td {{ $attributes->whereDoesntStartWith('data-') }}>
    {{ $slot }}
    @if ($clickable)
        <a {{ $attributes->whereStartsWith('data-')->merge([
            'style' => 'position: absolute; left: 0; right: 0; top: 0; bottom: 0',
            'href' => '#',
        ]) }} onclick="loadData(this.dataset)"></a>
    @endif
</td>
