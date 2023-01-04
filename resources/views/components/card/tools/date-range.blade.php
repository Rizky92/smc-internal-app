@props([
    'modelStart' => 'periodeAwal',
    'modelEnd' => 'periodeAkhir',
])

<div {{ $attributes->merge(['class' => 'd-flex align-items-center']) }}>
    <span class="text-sm" style="width: 5rem">Periode:</span>
    <input class="form-control form-control-sm" type="date" style="width: 8rem" wire:model.defer="{{ $modelStart }}" />
    <span class="text-sm px-3">sampai</span>
    <input class="form-control form-control-sm" type="date" style="width: 8rem" wire:model.defer="{{ $modelEnd }}" />
</div>
