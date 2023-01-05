@props([
    'model',
    'title' => null,
    'options' => [],
    'placeholder' => null,
])

<div {{ $attributes->merge(['class' => 'd-flex justify-content-start align-items-center']) }}>
    @if ($title)
        <span class="text-sm" style="width: 5rem">{{ $title }}</span>
    @endif
    
    <select class="form-control form-control-sm ml-auto" wire:model.defer="{{ $model }}" style="width: 9rem">
        @if ($placeholder)
            <option value="" disabled selected>{{ $placeholder }}</option>
        @endif
        @foreach ($options as $value => $name)
            <option value="{{ $value }}">{{ $name }}</option>
        @endforeach
    </select>
</div>
