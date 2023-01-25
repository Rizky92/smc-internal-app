@aware([
    'sortable' => false,
    'sortColumns' => [],
])

@props([
    'sortable' => false,
    'name' => (string) null,
    'title' => (string) null,
])

@if ($sortable)
    <th {{ $attributes->merge(['class' => 'py-2']) }}>
        <button type="button" class="btn btn-link text-decoration-none font-weight-bold text-left w-100 p-0 m-0" wire:click="sortBy(@js($name), @js((string) optional($sortColumns)[$name]))">
            <span class="text-dark mr-1">{{ $title }}</span>
            @switch(optional($sortColumns)[$name])
                @case('asc')
                    <i class="fas fa-arrow-up"></i>
                @break

                @case('desc')
                    <i class="fas fa-arrow-down"></i>
                @break
            @endswitch
        </button>
    </th>
@else
    <th {{ $attributes->merge(['class' => 'py-2']) }}>
        {{ $slot }}
    </th>
@endif
