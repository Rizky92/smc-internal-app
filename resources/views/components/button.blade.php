@props([
    'as' => 'button',
    'title' => null,
    'icon' => null,
    'size' => 'default',
    'variant' => 'default',
    'outline' => false,

    'hideTitle' => false,
])

@php
    $finalClass = collect(['btn']);
    
    $buttonSizes = [
        'xs' => 'btn-xs',
        'sm' => 'btn-sm',
        'default' => null,
        'lg' => 'btn-lg',
    ];
    
    $buttonVariants = [
        'default' => 'default',
        'primary' => 'primary',
        'secondary' => 'secondary',
        'success' => 'success',
        'info' => 'info',
        'warning' => 'warning',
        'danger' => 'danger',
        'light' => 'light',
        'dark' => 'dark',
        'link' => 'link',
    ];
    
    $buttonVariants = collect($buttonVariants)
        ->when(
            $outline,
            fn($cols) => $cols->map(fn($v, $k) => str($v)->prepend('btn-outline-'))->replace(['link' => 'btn-link']),
            fn($cols) => $cols->map(fn($v, $k) => str($v)->prepend('btn-'))
        )
        ->all();
    
    $finalClass = $finalClass
        ->push($buttonSizes[$size])
        ->push($buttonVariants[$variant])
        ->filter()
        ->join(' ');

    $id ??= str($title)->slug();
@endphp

@once
    @push('css')
        <style>
            .btn {
                display: inline-flex !important;
                align-items: center !important;
            }

            .btn::after {
                margin-top: 0.125rem
            }
        </style>
    @endpush
@endonce

@switch($as)
    @case('button')
        <button {{ $attributes->merge(['class' => $finalClass, 'type' => 'button', 'id' => $id, 'title' => $title]) }}>
            @if ($icon)
                <i class="{{ $icon }}"></i>
            @endif
            @if ($title)
                <span class="{{ Arr::toCssClasses(['ml-1' => $icon, 'sr-only' => $hideTitle]) }}">{{ $title ?? $slot }}</span>
            @endif
        </button>
    @break

    @case('link')
        <a {{ $attributes->merge(['class' => $finalClass, 'role' => 'button', 'id' => $id, 'title' => $title]) }}>
            @if ($icon)
                <i class="{{ $icon }}"></i>
            @endif
            @if ($title)
                <span class="{{ Arr::toCssClasses(['ml-1' => $icon, 'sr-only' => $hideTitle]) }}">{{ $title ?? $slot }}</span>
            @endif
        </a>
    @break
@endswitch
