@php($isActive = collect($item['items'])->flatten()->contains(URL::current()))

@if ($item['hasAnyPermissions'])
    <li class="nav-item">
        <a href="#" class="nav-link {{ $isActive ? 'active' : '' }}">
            <i class="nav-icon {{ $item['icon'] }}"></i>
            <p>
                {{ $item['name'] }}
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            @each('layouts.components.sidebar.link', $item['items'], 'item')
        </ul>
    </li>
@endif
