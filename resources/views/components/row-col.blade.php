@props(['livewire' => false])

<div {{ $attributes->merge(['class' => 'row']) }} {{ $livewire ? 'wire:ignore' : null }}>
    <div class="col-12">
        {{ $slot }}
    </div>
</div>
