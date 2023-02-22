@props([
    'livewire' => false,
    'collection' => collect(),
])

<div class="timeline" {{ $attributes->merge(['class' => 'timeline']) }} {{ $livewire ? 'wire:ignore' : null }}>
    @forelse ($collection as $date => $timeline)
        <div class="time-label">
            <span class="bg-white text-dark text-sm px-3 border font-weight-bold">{{ carbon($date)->format('d F Y') }}</span>
        </div>

        @foreach ($timeline as $item)
            <div class="{{ Arr::toCssClasses(['mt-3' => $loop->first && !$loop->parent->first]) }}">
                <i class="fas fa-angle-right bg-dark"></i>
                <div class="timeline-item border-0 shadow-none bg-transparent" style="margin-top: -0.2rem">
                    <div class="timeline-body mt-n1 d-flex justify-content-start align-items-start">
                        <span class="badge badge-secondary text-xs" style="margin-inline-end: 1.25rem">12:05</span>
                        <div class="mt-n1">
                            <h6 style="margin-top: 0.2rem; margin-bottom: 0.25rem">
                                Mengunjungi <a href="#">Jurnal PO Supplier</a>
                            </h6>
                            <ul class="nav justify-content-start" style="row-gap: 2rem">
                                <li class="mr-1 nav-item">Dashboard</li>
                                <li class="mx-1 nav-item">/</li>
                                <li class="mx-1 nav-item">Keuangan</li>
                                <li class="mx-1 nav-item">/</li>
                                <li class="ml-1 nav-item active">Jurnal PO Supplier</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

    @empty

    @endforelse

    <div>
        <i class="fas fa-clock fa-fw bg-gray"></i>
    </div>
</div>
