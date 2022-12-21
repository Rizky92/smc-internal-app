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
            <i class="{{ $icon }} nav-icon"></i>
            <p>{{ $name }}</p>
        </a>
    </li>
@endif
