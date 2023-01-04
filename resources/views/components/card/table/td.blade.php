@props([
    'clickable' => null,
])

<td>
    {{ $slot }}
    @if ($clickable)
        <a {{ $clickable->attributes->merge([
            'style' => 'position: absolute; left: 0; right: 0; top: 0; bottom: 0',
            'href' => '#',
        ]) }} onclick="loadData(this.dataset)"></a>
    @endif
</td>
