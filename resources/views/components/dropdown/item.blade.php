@props([
    'as' => 'button',
    'title' => null,
    'icon' => null,
])

@switch($as)
    @case('button')
        <button {{ $attributes->merge(['class' => 'dropdown-item', 'type' => 'button', 'id' => Str::slug($title)]) }}>
            @if ($icon)
                <i class="{{ $icon }}"></i>
                <span class="ml-1">{{ $title ?? $slot }}</span>
            @else
                {{ $title ?? $slot }}
            @endif
        </button>

        @break
    @case('link')
        <a {{ $attributes->merge(['class' => 'dropdown-item', 'role' => 'button', 'id' => Str::slug($title)]) }}>
            @if ($icon)
                <i class="{{ $icon }}"></i>
                <span class="ml-1">{{ $title ?? $slot }}</span>
            @else
                {{ $title ?? $slot }}
            @endif
        </a>

        @break
@endswitch
