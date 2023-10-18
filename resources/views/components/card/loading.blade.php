@props([
    'target' => null,
    'delay' => 'longest',
])

<div wire:loading.delay.{{ $delay }}.class="overlay light align-items-start" style="padding-top: min(1rem, 10rem)" {{ $target ? "wire:target=\"{$target}\"" : null }}>
    <div class="d-none justify-content-center align-items-center" wire:loading.delay.{{ $delay }}.class="d-flex flex-column" wire:loading.delay.{{ $delay }}.class.remove="d-none" {{ $target ? "wire:target=\"{$target}\"" : null }}>
        <i class="fas fa-spinner fa-3x fa-spin"></i>
        <p class="mt-3 text-lg">Tunggu sebentar...</p>
    </div>
</div>
