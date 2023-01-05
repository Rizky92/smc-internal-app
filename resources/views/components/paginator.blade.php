@props([
    'count',
    'total',
])

<div {{ $attributes->merge(['class' => 'd-flex align-items center justify-content-start']) }}>
    <p class="text-muted">Menampilkan {{ $count }} dari total {{ number_format($total, 0, ',', '.') }} item.</p>
    <div class="ml-auto">
        {{ $slot }}
    </div>
</div>