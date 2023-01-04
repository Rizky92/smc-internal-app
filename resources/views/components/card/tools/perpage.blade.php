@props([
    'model' => 'perpage'
])

<div {{ $attributes->merge(['class' => 'd-flex align-items-center']) }}>
    <span class="text-sm" style="width: 5rem">Tampilkan:</span>
    <div class="input-group input-group-sm" style="width: 5rem">
        <select class="custom-control custom-select" wire:model.defer="{{ $model }}">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
            <option value="200">200</option>
            <option value="500">500</option>
            <option value="1000">1000</option>
        </select>
    </div>
    <span class="text-sm pl-3">per halaman</span>
</div>
