@props([
    'variant' => 'info',
    'title' => null,
    'content' => null,
    'icon' => null,
])

@php
    $calloutClass = ['callout'];
    $iconClass = ['fas'];

    $colorVariants = [
        'primary' => 'callout-primary',
        'secondary' => 'callout-secondary',
        'success' => 'callout-success',
        'info' => 'callout-info',
        'warning' => 'callout-warning',
        'danger' => 'callout-danger',
    ];

    $iconVariants = [
        'primary' => 'fa-info text-primary',
        'secondary' => 'fa-info text-secondary',
        'success' => 'fa-check-circle text-success',
        'info' => 'fa-info-circle text-info',
        'warning' => 'fa-exclamation-triangle text-warning',
        'danger' => 'fa-times-circle text-danger',
    ];

    if (! array_key_exists($variant, $colorVariants)) {
        $calloutClass[] = $variant;
    } else {
        $calloutClass[] = $colorVariants[$variant];
    }

    if (! is_null($icon)) {
        $iconClass = $icon;
    } else {
        $iconClass[] = $iconVariants[$variant];
    }

    if (is_array($iconClass)) {
        $iconClass = Arr::toCssClasses($iconClass);
    }

    $calloutClass = Arr::toCssClasses($calloutClass);
@endphp

<div {{ $attributes->merge(['class' => $calloutClass]) }}>
    <h6 {{ $title->attributes }}>
        <i class="{{ $iconClass }}"></i>
        <span class="ml-2">{{ $title }}</span>
    </h6>
    <p {{ $content->attributes }}>{{ $content }}</p>
</div>