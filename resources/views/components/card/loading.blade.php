@props([
    'target' => null,
    'delay' => 'longest',
])

@once
    @push('css')
        <style>
            .overlay {
                align-items: flex-start !important;
                padding-top: min(10rem, 2rem);
            }
        </style>
    @endpush
@endonce

<div wire:loading.delay.{{ $delay }}.class="overlay light" {{ $target ? "wire:target=\"{$target}\"" : null }}>
    <div
        class="d-none justify-content-center align-items-center"
        wire:loading.delay.{{ $delay }}.class="d-flex flex-column"
        wire:loading.delay.{{ $delay }}.class.remove="d-none"
        {{ $target ? "wire:target=\"{$target}\"" : null }}>
        <i class="fas fa-spinner fa-3x fa-spin"></i>
        <p class="mt-3 text-lg">Mohon tunggu...</p>
    </div>
</div>
