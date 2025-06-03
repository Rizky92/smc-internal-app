@aware(['livewire', 'selected', 'withPermissions'])

@props([
    'id',
    'hasPermission' => false,
])

<div @class(['tab-pane', 'show active' => $selected === $id]) id="content-{{ $id }}" role="tabpanel" {{ $livewire ? 'wire:ignore.self' : null }}>
    @if (! $withPermissions && ! $hasPermission xor $withPermissions && $hasPermission)
        <div {{ $attributes->except(['selected', 'id', 'title', 'livewire']) }}>
            {{ $slot }}
        </div>
    @else
        <div class="px-3 py-5 bg-light border-top">
            <p class="text-center text-sm m-0 p-0">Anda tidak memiliki akses untuk melihat konten tab ini!</p>
        </div>
    @endif
</div>
