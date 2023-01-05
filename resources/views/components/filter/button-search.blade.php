@props([
    'method' => 'searchData',
    'title' => 'Cari',
    'icon' => 'fas fa-search'
])

<div {{ $attributes }}>
    <button class="btn btn-default btn-sm" type="button" wire:click="{{ $method }}">
        <i class="{{ $icon }}"></i>
        <span class="ml-1">{{ $title }}</span>
    </button>
</div>
