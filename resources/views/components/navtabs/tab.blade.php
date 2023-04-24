@aware(['livewire', 'selected', 'withPermissions'])

@props([
    'type' => 'pill',
    'id',
    'title',
    'hasPermission' => false,
])

<li class="nav-item my-2">
    <a {{ $attributes
        ->class(['nav-link text-sm', 'active' => $selected === $id])
        ->merge([
            'id' => "tab-{$id}",
            'data-toggle' => $type,
            'href' => "#content-{$id}",
            'role' => 'tab',
            'aria-controls' => "content-{$id}",
            'aria-selected' => $selected === $id ? 'true' : 'false',
        ])
    }} {{ $livewire ? 'wire:ignore.self' : null }}>
        <span>{{ $title }}</span>
    </a>
</li>
