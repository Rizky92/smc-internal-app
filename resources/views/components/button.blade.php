@props([
    'as' => 'button',
    'title' => null,
    'icon' => null,
    'id' => null,
    'size' => 'default',
    'variant' => 'default',
    'outline' => false,
    'disabled' => false,
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

    $buttonVariants = collect([
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
    ])
        ->when(
            $outline,
            fn ($cols) => $cols->map(fn ($v, $k) => str($v)->prepend('btn-outline-'))->replace(['link' => 'btn-link']),
            fn ($cols) => $cols->map(fn ($v, $k) => str($v)->prepend('btn-'))
        )
        ->all();

    $finalClass = $finalClass
        ->push($buttonSizes[$size])
        ->push($buttonVariants[$variant])
        ->filter()
        ->join(' ');

    $id ??= str($title)
        ->slug()
        ->value();
@endphp

@once
    @push('css')
        <style>
            .btn {
                display: inline-flex !important;
                align-items: center !important;
            }

            .btn::after {
                margin-top: 0.125rem;
            }
        </style>
    @endpush
@endonce

@switch($as)
    @case('button')
        <button
            {{
                $attributes->merge([
                    'class' => $finalClass,
                    'type' => 'button',
                    'id' => $id,
                    'title' => $title,
                    'disabled' => $disabled,
                ])
            }}>
            @if ($icon)
                <i class="{{ $icon }}"></i>
            @endif

            @if ($title)
                <span class="{{ Arr::toCssClasses(['ml-1' => $icon, 'sr-only' => $hideTitle]) }}">
                    {{ $title ?? $slot }}
                </span>
            @endif
        </button>

        @break
    @case('link')
        <a
            {{
                $attributes
                    ->merge(['class' => $finalClass, 'role' => 'button', 'id' => $id, 'title' => $title])
                    ->when($disabled, fn ($attrs) => $attrs->except('href'))
            }}>
            @if ($icon)
                <i class="{{ $icon }}"></i>
            @endif

            @if ($title)
                <span class="{{ Arr::toCssClasses(['ml-1' => $icon, 'sr-only' => $hideTitle]) }}">
                    {{ $title ?? $slot }}
                </span>
            @endif
        </a>

        @break
@endswitch
