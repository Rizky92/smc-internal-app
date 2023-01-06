@props([
    'useDefaultFilter' => false,
    'useLoading' => false,

    'header' => null,
    'body' => null,
    'footer' => null,
])

<div class="card">
    @if ($useDefaultFilter || $header)
        <div class="card-body">
            @if ($useDefaultFilter)
                <x-card.row>
                    <x-filter.range-date />
                    <x-filter.button-export-excel class="ml-auto" />
                </x-card.row>
                <x-card.row class="mt-2">
                    <x-filter.select-perpage />
                    <x-filter.button-reset-filters class="ml-auto" />
                    <x-filter.search class="ml-2" />
                </x-card.row>
            @else
                {{ $header }}
            @endif
        </div>
    @endif
    <div {{ $body->attributes->merge(['class' => 'card-body']) }}>
        {{ $body }}
    </div>
    @if ($footer)
        <div class="card-footer">
            {{ $footer }}
        </div>
    @endif
    @if ($useLoading)
        <x-card.loading />
    @endif
</div>
