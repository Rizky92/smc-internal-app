@props([
    'method' => 'resetFilters',
    'title' => 'Reset Filter',
])

<div {{ $attributes }}>
    <button class="btn btn-sm btn-link text-secondary" type="button" id="{{ Str::of($title)->plural()->kebab() }}" wire:click="{{ $method }}">
        {{ $title }}
    </button>
</div>
