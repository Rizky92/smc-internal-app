@props([
    'method' => 'exportToExcel',
    'title' => 'Export ke Excel',
    'icon' => 'fas fa-file-excel',
])

<x-button :attributes="$attributes->merge([
    'size' => 'sm',
    'variant' => 'dark',
    'outline' => true,
    'title' => $title,
    'icon' => $icon,
    'wire:click.prevent' => $method,
])" />
