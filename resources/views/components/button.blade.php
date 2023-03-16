@props([
    'as' => 'button',
    'id' => null,
    'title' => null,
    'icon' => null,
    'size' => 'default',
    'variant' => 'default',
    'outline' => false,
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
        // {{-- blade-formatter-disable --}}
        ->when(
            $outline,
            fn($cols) => $cols->map(fn($v, $k) => str($v)->prepend('btn-outline-'))->replace(['link' => 'btn-link']),
            fn($cols) => $cols->map(fn($v, $k) => str($v)->prepend('btn-'))
        )
        // {{-- blade-formatter-enable --}}
        ->all();
    
    $finalClass = $finalClass
        ->push($buttonSizes[$size])
        ->push($buttonVariants[$variant])
        ->filter()
        ->join(' ');

    $id ??= str($title)->slug();
@endphp

@switch($as)
    @case('button')
        <button {{ $attributes->merge(['class' => $finalClass, 'type' => 'button', 'id' => $id]) }}>
            @if ($icon)
                <i class="{{ $icon }}"></i>
            @endif
            @if ($title)
                <span class="{{ Arr::toCssClasses(['ml-1' => $icon]) }}">{{ $title ?? $slot }}</span>
            @endif
        </button>
    @break

    @case('link')
        <a {{ $attributes->merge(['class' => $finalClass, 'role' => 'button', 'id' => $id]) }}>
            @if ($icon)
                <i class="{{ $icon }}"></i>
            @endif
            @if ($title)
                <span class="{{ Arr::toCssClasses(['ml-1' => $icon]) }}">{{ $title ?? $slot }}</span>
            @endif
        </a>
    @break

@endswitch
