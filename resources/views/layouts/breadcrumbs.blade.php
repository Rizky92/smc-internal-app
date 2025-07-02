@if ($breadcrumbs->isNotEmpty())
    <li class="nav-item">
        <ol class="d-flex justify-content-start align-items-center nav-link" style="list-style: none">
            @foreach ($breadcrumbs as $bc)
                @if ($bc->url && ! $loop->last)
                    <li>
                        <a href="{{ $bc->url ?? null }}" class="text-muted">
                            {{ $bc->title }}
                        </a>
                    </li>
                @elseif ($loop->last)
                    <li class="text-dark font-weight-bold text-uppercase">
                        {{ $bc->title }}
                    </li>
                @else
                    <li class="text-muted">{{ $bc->title }}</li>
                @endif

                @unless ($loop->last)
                    <li class="mx-3 text-dark">/</li>
                @endunless
            @endforeach
        </ol>
    </li>
@endif
