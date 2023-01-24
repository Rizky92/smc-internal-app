@props([
    'sortable' => false,
    'columnName' => null,
    'direction' => 'asc',
])

@if ($sortable)
    <th {{ $attributes->merge(['class' => 'py-2']) }}>
        <button
            type="button"
            class="btn btn-link text-decoration-none font-weight-bold text-left text-dark w-100 p-0 m-0"
            wire:click="sortBy('{{ $columnName }}', '{{ $direction }}')">
            {{ $slot }}
        </button>
    </th>
@else
    <th {{ $attributes->merge(['class' => 'py-2']) }}>
        {{ $slot }}
    </th>
@endif
