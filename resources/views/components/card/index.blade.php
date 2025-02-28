@props([
    'useDefaultFilter' => false,
    'useLoading' => false,

    'header' => null,
    'body' => null,
    'footer' => null,

    'loadingTarget' => null,
    'table' => true,
])

<div {{ $attributes->merge(['class' => 'card']) }}>
    @if ($useDefaultFilter)
        <div class="card-body">
            <x-row-col-flex>
                <x-filter.range-date />
                <x-filter.button-export-excel class="ml-auto" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
        </div>
    @else
        <div {{ $header->attributes->merge(['class' => 'card-body']) }}>
            {{ $header }}
        </div>
    @endif
    <div {{
        $body->attributes->class([
            'card-body',
            'p-0' => $table,
        ])
    }}>
        {{ $body }}
    </div>
    @if ($footer)
        <div {{ $footer->attributes->merge(['class' => 'card-footer border-top']) }}>
            {{ $footer }}
        </div>
    @endif

    @if ($useLoading)
        <x-card.loading :target="$loadingTarget" />
    @endif
</div>
