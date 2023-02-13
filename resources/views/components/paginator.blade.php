@props(['data'])

@if (is_a($data, \Illuminate\Contracts\Pagination\LengthAwarePaginator::class))
    <div {{ $attributes->merge(['class' => 'd-flex align-items-center justify-content-start']) }}>
        <p class="text-muted">Menampilkan {{ $data->count() }} dari total {{ number_format($data->total(), 0, ',', '.') }} item.</p>
        {{ $slot }}
        <div class="ml-auto">
            {{ $data->links() }}
        </div>
    </div>
@endif
