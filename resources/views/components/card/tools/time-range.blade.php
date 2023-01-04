@props([
    'modelStart' => 'jamAwal',
    'modelEnd' => 'jamAkhir',
])

<div {{ $attributes->merge(['class' => 'd-flex align-items-center']) }}>
    <span class="text-sm" style="width: 5rem">Jam:</span>
    <input class="form-control form-control-sm" type="time" style="width: 8rem" wire:model.defer="{{ $modelStart }}" />
    <span class="text-sm px-3">sampai</span>
    <input class="form-control form-control-sm" type="time" style="width: 8rem" wire:model.defer="{{ $modelEnd }}" />
</div>
