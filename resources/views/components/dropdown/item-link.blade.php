@props([
    'title' => null,
    'icon' => null,
])

<a {{ $attributes->merge(['class' => 'dropdown-item', 'role' => 'button', 'id' => Str::slug($title)]) }}>
    @if ($icon)
        <i class="{{ $icon }}"></i>
        <span class="ml-1">{{ $title ?? $slot }}</span>
    @else
        {{ $title ?? $slot }}
    @endif
</a>