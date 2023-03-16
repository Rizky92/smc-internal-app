@props([
    'method' => 'searchData',
    'title' => 'Cari',
    'icon' => 'fas fa-search',
])

<x-button :attributes="$attributes->merge([
    'size' => 'sm',
    'title' => $title,
    'icon' => $icon,
    'wire:click.prevent' => $method,
])" />
