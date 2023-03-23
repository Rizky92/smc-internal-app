@aware(['livewire', 'selected'])

@props(['id'])

<div @class(['tab-pane', 'show active' => $selected === $id]) id="content-{{ $id }}" role="tabpanel" {{ $livewire ? 'wire:ignore.self' : null }}>
    <div {{ $attributes->except(['selected', 'id', 'title', 'livewire']) }}>
        {{ $slot }}
    </div>
</div>
