@props(['data'])

@if ($data instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
    <div {{ $attributes->merge(['class' => 'd-flex justify-content-start align-items-center']) }}>
        <p class="text-muted p-0 m-0">Menampilkan {{ $data->count() }} dari total {{ number_format($data->total(), 0, ',', '.') }} item.</p>        
        {{ $data->links() }}
    </div>
@endif
