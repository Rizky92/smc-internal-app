@props(['livewire' => false])

<div {{ $attributes->merge(['class' => 'row']) }} {{ $livewire ? 'wire:ignore' : null }}>
    {{ $slot }}
</div>
