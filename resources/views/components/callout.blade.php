@props([
    'variant' => 'info',
    'title' => null,
    'content' => null,
])

<div class="callout callout-danger">
    <h6>
        <i class="fas fa-exclamation-triangle"></i>
        <span class="ml-2">{{ $title }}</span>
    </h6>
    <p>{{ $content }}</p>
</div>