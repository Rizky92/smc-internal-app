@props([
    'modelStart' => 'jamAwal',
    'modelEnd' => 'jamAkhir',
    'title' => 'Jam:',
])

<div {{ $attributes->merge(['class' => 'd-flex align-items-center']) }}>
    <span class="text-sm" style="width: 5rem">{{ $title }}</span>
    <input
        class="form-control form-control-sm"
        type="time"
        style="width: 9rem"
        wire:model.defer="{{ $modelStart }}"
    />
    <span class="text-sm px-3">sampai</span>
    <input
        class="form-control form-control-sm"
        type="time"
        style="width: 9rem"
        wire:model.defer="{{ $modelEnd }}"
    />
</div>
