@props([
    'table' => true,
    'loading' => false,
    'filter' => true,

    'header' => null,
    'body' => null,
    'footer' => null,
])

<div class="card">
    <div class="card-body">
        @if ($filter)
            <x-card.tools>
                <x-card.tools.date-range />
                <x-card.tools.export-to-excel class="ml-auto" />
            </x-card.tools>
            <x-card.tools class="mt-2">
                <x-card.tools.perpage />
                <x-card.tools.reset-filters class="ml-auto" />
                <x-card.tools.search class="ml-2" />
            </x-card.tools>
        @else
            {{ $header }}
        @endif
    </div>
    <div class="card-body {{ $table ? 'table-responsive p-0' : null }}">
        {{ $body }}
    </div>
    <div class="card-footer">
        {{ $footer }}
    </div>
    @if ($loading)
        <x-card.loading />
    @endif
</div>
