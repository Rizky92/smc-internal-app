@props(['target' => null])

<div wire:loading.delay.longest.class="overlay light" {{ $target ? "wire:target=\"{$target}\"" : null }}>
    <div class="d-none justify-content-center align-items-center" wire:loading.delay.longest.class="d-flex flex-column" wire:loading.delay.longest.class.remove="d-none" {{ $target ? "wire:target=\"{$target}\"" : null }}>
        <i class="fas fa-spinner fa-3x fa-spin"></i>
        <p class="mt-3 text-lg">Tunggu sebentar...</p>
    </div>
</div>
