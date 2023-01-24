@aware(['livewire' => false])

@props([
    'selected' => false,
    'type' => 'pill',
    'id',
    'title',
])

<li class="nav-item my-2">
    <a class="nav-link {{ $selected ? 'active' : null }}" id="tab-{{ $id }}" data-toggle="{{ $type }}" href="#content-{{ $id }}" role="tab" aria-controls="content-{{ $id }}" aria-selected="{{ $selected ? 'true' : 'false' }}" {{ $livewire ? 'wire:ignore': null }}>
        <span>{{ $title }}</span>
    </a>
</li>
