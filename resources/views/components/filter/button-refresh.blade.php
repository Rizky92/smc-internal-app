@props([
    'method' => '$refresh',
    'title' => 'Refresh',
    'icon' => 'fas fa-sync-alt',
])

<x-button :attributes="$attributes->merge([
    'size' => 'sm',
    'title' => $title,
    'icon' => $icon,
    'wire:click.prevent' => $method,
])" />
