@aware(['livewire', 'selected', 'withPermissions'])

@props([
    'type' => 'pill',
    'id',
    'title',
    'hasPermission' => false,
])

<li class="nav-item my-2">
    <a
        {{
            $attributes->class(['nav-link text-sm', 'active' => $selected === $id, 'disabled' => ! (! $withPermissions && ! $hasPermission xor $withPermissions && $hasPermission)])->merge([
                'id' => "tab-{$id}",
                'data-toggle' => $type,
                'href' => "#content-{$id}",
                'role' => 'tab',
                'aria-controls' => "content-{$id}",
                'aria-selected' => $selected === $id ? 'true' : 'false',
                'wire:ignore.self' => $livewire,
            ])
        }}>
        <span>{{ $title }}</span>
    </a>
</li>
