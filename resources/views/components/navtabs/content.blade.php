@aware(['livewire' => false])

@props([
    'selected' => false,
    'id',
    'title',
])

<div class="tab-pane {{ $selected ? 'show active' : null }}" id="content-{{ $id }}" role="tabpanel" aria-label="Tab {{ $title }}" {{ $livewire ? 'wire:ignore.self' : null }}>
    <div {{ $attributes->except(['selected', 'id', 'title', 'livewire']) }}>
        {{ $slot }}
    </div>
</div>
