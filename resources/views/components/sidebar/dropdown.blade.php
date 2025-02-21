@props([
    'hasPermissions' => false,
    'isActive' => false,
    'icon' => 'far fa-circle',
    'name',
])

@if ($hasPermissions)
    <li class="nav-item {{ $isActive ? 'menu-is-opening menu-open' : null }}">
        <a href="#" class="nav-link {{ $isActive ? 'active' : '' }}">
            <i class="{{ $icon }} text-sm m-0 nav-icon" style="text-align: center; width: 1.25em"></i>
            <p style="margin-left: 0.375rem">
                {{ $name }}
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            {{ $slot }}
        </ul>
    </li>
@endif
