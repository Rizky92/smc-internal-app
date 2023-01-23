@props([
    'sort' => false,
    'columnName' => 'null',
    'direction' => 'asc',
])

<th {{ $attributes->merge(['class' => 'py-2']) }} style="position: relative">
    {{ $slot }}
    @if ($sort)    
        <button type="button" >

        </button>
    @endif
</th>