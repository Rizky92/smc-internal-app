@props([
    'useDefaultFilter' => false,
    'useLoading' => false,

    'header' => null,
    'body' => null,
    'footer' => null,
])

<div class="card">
    @if ($useDefaultFilter)
        <div class="card-body">
            <x-card.row-col>
                <x-filter.range-date />
                <x-filter.button-export-excel class="ml-auto" />
            </x-card.row-col>
            <x-card.row-col class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-card.row-col>
        </div>
    @else
        <div class="card-body">
            {{ $header }}
        </div>
    @endif
    <div {{ $body->attributes->merge(['class' => 'card-body p-0']) }}>
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
