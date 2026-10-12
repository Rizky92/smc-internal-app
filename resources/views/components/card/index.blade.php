@props([
    'useDefaultFilter' => false,
    // The default filter row carries an "Export ke Excel" button. Turn it off
    // on a page that has no export: pressing it there only reaches an empty
    // dataPerSheet().
    'useExport' => true,
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
                @if ($useExport)
                    <x-filter.button-export-excel class="ml-auto" />
                @endif
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
