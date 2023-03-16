@props([
    'method' => 'resetFilters',
    'title' => 'Reset Filter',
])

<x-button :attributes="$attributes->merge([
    'size' => 'sm',
    'variant' => 'link',
    'class' => 'text-secondary',
    'title' => $title,
    'wire:click.prevent' => $method,
])" />
