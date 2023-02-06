@props([
    'method' => 'resetFilters',
    'title' => 'Reset Filter',
])

<div {{ $attributes }}>
    <x-button class="btn-sm btn-link text-secondary" title="{{ $title }}" wire:click="{{ $method }}" />
</div>
