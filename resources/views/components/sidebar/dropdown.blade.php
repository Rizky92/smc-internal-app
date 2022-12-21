@props([
    'hasPermissions' => false,
    'isActive' => false,
    'icon' => 'far fa-circle',
    'name',
])

@if ($hasPermissions)
    <li class="nav-item">
        <a href="#" class="nav-link {{ $isActive ? 'active' : '' }}">
            <i class="nav-icon {{ $icon }}"></i>
            <p>
                {{ $name }}
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            {{ $slot }}
        </ul>
    </li>
@endif
