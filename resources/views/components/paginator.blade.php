@props(['data'])

@if (is_a($data, \Illuminate\Contracts\Pagination\LengthAwarePaginator::class))
    <div {{ $attributes->merge(['class' => 'd-flex justify-content-start align-items-center']) }}>
        <p class="text-muted p-0 m-0">Menampilkan {{ $data->count() }} dari total {{ number_format($data->total(), 0, ',', '.') }} item.</p>        
        {{ $data->links() }}
    </div>
@endif
