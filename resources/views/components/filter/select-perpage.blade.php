@props([
    'model' => 'perpage',
    'title' => 'Per halaman:',
    'steps' => [10, 25, 50, 100, 200, 500, 1000],
])

<div {{ $attributes->merge(['class' => 'd-flex align-items-center']) }}>
    <span class="text-sm" style="width: 5rem">{{ $title }}</span>

    <select class="custom-control custom-select" style="width: 5rem" wire:model.defer="{{ $model }}">
        @foreach ($steps as $step)
            <option value="{{ $step }}">{{ $step }}</option>
        @endforeach
    </select>
</div>
