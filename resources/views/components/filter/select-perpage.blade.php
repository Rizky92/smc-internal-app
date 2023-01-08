@props([
    'model' => 'perpage',
    'titleStart' => 'Tampilkan:',
    'titleEnd' => 'per halaman',
    'steps' => [10, 25, 50, 100, 200, 500, 1000],
    'constantWidth' => true,
])

<x-filter.label :attributes="$attributes->merge(['constantWidth' => $constantWidth])">{{ $titleStart }}</x-filter.label>

<div class="input-group input-group-sm" style="width: 4.25rem">
    <select class="custom-control custom-select" wire:model.defer="{{ $model }}">
        @foreach ($steps as $step)
            <option value="{{ $step }}">{{ $step }}</option>
        @endforeach
    </select>
</div>

<x-filter.label class="pl-3">{{ $titleEnd }}</x-filter.label>