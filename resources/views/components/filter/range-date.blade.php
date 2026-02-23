@props([
    'modelStart' => 'tglAwal',
    'modelEnd' => 'tglAkhir',
    'title' => 'Periode:',
])

<div {{ $attributes->merge(['class' => 'd-flex align-items-center']) }}>
    @if (! empty($title))
        <span class="text-sm" style="width: 5rem">{{ $title }}</span>
    @endif

    <input class="form-control form-control-sm" type="date" style="width: 9rem" wire:model.defer="{{ $modelStart }}" value="{{ now()->startOfMonth()->toDateString() }}" />
    <span class="text-sm px-3">sampai</span>
    <input class="form-control form-control-sm" type="date" style="width: 9rem" wire:model.defer="{{ $modelEnd }}" value="{{ now()->endOfMonth()->toDateString() }}" />
</div>
