@aware(['types'])

@props([
    'type' => null,
])

<span class="badge" {{ $attributes->merge(['class' => 'badge']) }}>
    {{ $types[$type] }}
</span>
