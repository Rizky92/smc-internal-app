@props(['livewire' => false])

<div {{ $attributes->merge(['class' => 'row', 'wire:ignore' => $livewire]) }}>
    {{ $slot }}
</div>
