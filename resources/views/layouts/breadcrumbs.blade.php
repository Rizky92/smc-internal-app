@unless($breadcrumbs->isEmpty())
    <li class="nav-item">
        <ol class="d-flex justify-content-start align-items-center nav-link" style="list-style: none">
            @foreach ($breadcrumbs as $bc)
                @if ($bc->url && !$loop->last)
                    <li class="text-muted">{{ $bc->title }}</li>
                @endif
            @endforeach
            <li class="mx-3">/</li>
            <li class="text-muted">Farmasi</li>
            <li class="mx-3">/</li>
            <li class="text-dark font-weight-bold text-uppercase">Darurat Stok</li>
        </ol>
    </li>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb text-sm">
            @foreach ($breadcrumbs as $breadcrumb)
                @if ($breadcrumb->url && !$loop->last)
                    <li class="breadcrumb-item"><a href="{{ $breadcrumb->url }}">{{ $breadcrumb->title }}</a></li>
                @else
                    <li class="breadcrumb-item active">{{ $breadcrumb->title }}</li>
                @endif
            @endforeach
        </ol>
    </nav>
@endunless
