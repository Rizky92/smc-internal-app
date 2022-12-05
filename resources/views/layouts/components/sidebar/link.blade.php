@if ($item['hasAnyPermissions'])
    <li class="nav-item">
        <a href="{{ $item['url'] }}" class="nav-link @if (URL::current() === $item['url']) active @endif">
            <i class="{{ $item['icon'] }} nav-icon"></i>
            <p>{{ $item['name'] }}</p>
        </a>
    </li>
@endif
