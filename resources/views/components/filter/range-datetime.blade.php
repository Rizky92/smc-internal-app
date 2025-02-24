@props([
    'modelDateStart' => 'tglAwal',
    'modelDateEnd' => 'tglAkhir',
    'modelTimeStart' => 'jamAwal',
    'modelTimeEnd' => 'jamAkhir',
    'title' => 'Periode:',
])

<div {{ $attributes->merge(['class' => 'd-flex align-items-center']) }}>
    <span class="text-sm" style="width: 5rem">{{ $title }}</span>
    <input class="form-control form-control-sm" type="date" style="width: 9rem" wire:model.defer="{{ $modelDateStart }}" />
    <input class="form-control form-control-sm ml-2" type="time" style="width: 9rem" wire:model.defer="{{ $modelTimeStart }}" />
    <span class="text-sm px-3">sampai</span>
    <input class="form-control form-control-sm" type="date" style="width: 9rem" wire:model.defer="{{ $modelDateEnd }}" />
    <input class="form-control form-control-sm ml-2" type="time" style="width: 9rem" wire:model.defer="{{ $modelTimeEnd }}" />
</div>
