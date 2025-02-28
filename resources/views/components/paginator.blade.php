@props([
    'data',
])
<style>
    .pagination-section.print-hidden {
        display: none !important;
    }

    @media print {
        .pagination-section {
            display: none !important;
        }
    }
</style>
<div {{ $attributes->merge(['class' => 'd-flex justify-content-start align-items-center pagination-section']) }}>
    @if ($data instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
        <p class="text-muted p-0 m-0">Menampilkan {{ $data->count() }} dari total {{ number_format($data->total(), 0, ',', '.') }} item.</p>
        {{ $data->links() }}
    @else
        <p class="text-muted p-0 m-0">Menampilkan 0 dari total 0 item.</p>
    @endif
</div>
