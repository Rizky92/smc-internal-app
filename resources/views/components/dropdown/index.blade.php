@props([
    'button',
    'menu',
])

<div {{ $attributes->merge(['class' => 'dropdown mb-3']) }}>
    <x-button :attributes="$button->attributes->merge(['class' => 'dropdown-toggle', 'data-toggle' => 'dropdown'])" />
    <div {{ $menu->attributes->merge(['class' => 'dropdown-menu']) }}>
        {{ $menu }}
    </div>
</div>
