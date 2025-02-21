@props([
    'hasPermissions' => false,
    'url' => '#',
    'current' => URL::current(),
    'icon' => 'far fa-circle',
    'name',
])

@if ($hasPermissions)
    <li class="nav-item">
        <a href="{{ $url }}" class="nav-link @if ($current === $url) active @endif">
            <i class="{{ $icon }} text-sm m-0 nav-icon" style="text-align: center; width: 1.25em"></i>
            <p style="margin-left: 0.375rem">{{ $name }}</p>
        </a>
    </li>
@endif
