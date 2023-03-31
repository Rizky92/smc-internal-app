@aware(['livewire', 'selected'])

@props([
    'type' => 'pill',
    'id',
    'title',
])

<li class="nav-item my-2">
    <a {{ $attributes
        ->class(['nav-link', 'active' => $selected === $id])
        ->merge([
            'id' => "tab-{$id}",
            'data-toggle' => $type,
            'href' => "#content-{$id}",
            'role' => 'tab',
            'aria-controls' => "content-{$id}",
            'aria-selected' => $selected === "tab-{$id}" ? 'true' : 'false',
        ])
    }} {{ $livewire ? 'wire:ignore.self' : null }}>
        <span>{{ $title }}</span>
    </a>
</li>
