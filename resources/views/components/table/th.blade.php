@aware([
    'sortable' => false,
    'sortColumns' => [],
])

@props([
    'name' => null,
    'title' => null,
    'align' => 'left',
])

@if ($sortable && ! empty($name))
    @php
        $direction = (string) optional($sortColumns)[$name];

        $alignButtonClass = Arr::toCssClasses([
            'flex-row text-left' => $align === 'left',
            'text-right flex-row-reverse' => $align === 'right',
        ]);

        $alignTitleClass = Arr::toCssClasses([
            'ml-1' => $align === 'right',
            'mr-1' => $align === 'left',
        ]);
    @endphp

    <th {{ $attributes->class(['py-2']) }}>
        <button type="button" class="btn btn-link text-decoration-none font-weight-bold w-100 p-0 m-0 {{ $alignButtonClass }}" wire:click="sortBy(@js($name), @js($direction))">
            <span class="text-dark {{ $alignTitleClass }}">{{ $title }}</span>

            @switch($direction)
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
    <th {{ $attributes->class(['py-2', 'text-right' => $align === 'right']) }}>
        {{ $title ?? $slot }}
    </th>
@endif
