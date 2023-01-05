@props(['method', 'title', 'icon' => null])

<div {{ $attributes }}>
    <button class="btn btn-sm btn-default" type="button" wire:click="{{ $method }}">
        @if ($icon)
            <i class="{{ $icon }}"></i>
            <span class="ml-1">{{ $title }}</span>
        @else
            {{ $title }}
        @endif
    </button>
</div>
