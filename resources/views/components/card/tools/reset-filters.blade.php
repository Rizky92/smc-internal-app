@props(['method' => 'resetFilters'])

<button wire:click="{{ $method }}" {{ $attributes->merge([
    'class' => 'btn btn-sm btn-link text-secondary',
    'type' => 'button',
]) }}>
    Reset Filter
</button>
