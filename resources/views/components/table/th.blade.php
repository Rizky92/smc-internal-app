@aware([
    'sortable' => false,
    'sortColumns' => [],
])

@props([
    'name' => null,
    'title' => null,
])

@if ($sortable && !empty($name))
    <th {{ $attributes->merge(['class' => 'py-2']) }}>
        <button type="button" class="btn btn-link text-decoration-none font-weight-bold text-left w-100 p-0 m-0" wire:click="sortBy(@js($name), @js((string) optional($sortColumns)[$name]))">
            <span class="text-dark mr-1">{{ $title }}</span>
            @switch(optional($sortColumns)[$name])
                @case('asc')
                    <i class="fas fa-arrow-up" style="margin-top: 0.0625rem"></i>
                @break

                @case('desc')
                    <i class="fas fa-arrow-down" style="margin-top: 0.0625rem"></i>
                @break
            @endswitch
        </button>
    </th>
@else
    <th {{ $attributes->merge(['class' => 'py-2']) }}>
        {{ $title ?? $slot }}
    </th>
@endif
