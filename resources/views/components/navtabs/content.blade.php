@aware(['livewire'])

@props([
    'selected' => false,
    'id',
])

<div @class(['tab-pane', 'show active' => $selected]) id="content-{{ $id }}" role="tabpanel" {{ $livewire ? 'wire:ignore.self' : null }}>
    <div {{ $attributes->except(['selected', 'id', 'title', 'livewire']) }}>
        {{ $slot }}
    </div>
</div>
