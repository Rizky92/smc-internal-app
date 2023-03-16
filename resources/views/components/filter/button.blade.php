@props(['method', 'title', 'icon' => null])

<x-button :attributes="$attributes->merge(['size' => 'sm', 'wire:click.prevent' => $method, 'title' => $title, 'icon' => $icon])" />
