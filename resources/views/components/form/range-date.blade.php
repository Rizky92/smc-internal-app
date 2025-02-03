@props([
    'modelStart' => 'tglAwal',
    'modelEnd' => 'tglAkhir',
    'title' => 'Periode:',
])

<div {{ $attributes->merge(['class' => 'd-flex align-items-center']) }}>
    <input
        class="form-control form-control-sm"
        type="date"
        style="width: 9rem"
        wire:model.defer="{{ $modelStart }}"
    />
    <span class="text-sm px-3">sampai</span>
    <input
        class="form-control form-control-sm"
        type="date"
        style="width: 9rem"
        wire:model.defer="{{ $modelEnd }}"
    />
</div>
