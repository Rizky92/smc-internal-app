@props(['title', 'icon' => null])

<button {{ $attributes->merge(['class' => 'btn btn-sm', 'type' => 'button']) }}>
    @if ($icon)
        <i class="{{ $icon }}"></i>
        <span class="ml-1">{{ $title }}</span>
    @else
        {{ $title }}
    @endif
</button>
