@props([
    'method' => 'exportToExcel',
    'title' => 'Export ke Excel',
])

<div {{ $attributes }}>
    <button class="btn btn-default btn-sm" type="button" wire:click="{{ $method }}">
        <i class="fas fa-file-excel"></i>
        <span class="ml-1">{{ $title }}</span>
    </button>
</div>
