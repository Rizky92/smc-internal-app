@props([
    'method' => 'exportToExcel',
    'title' => 'Export ke Excel',
])

<div {{ $attributes }}>
    <button class="btn btn-outline-dark btn-sm" type="button" wire:click="{{ $method }}">
        <i class="fas fa-file-excel"></i>
        <span class="ml-1">{{ $title }}</span>
    </button>
</div>
