@props([
    'method' => 'searchData',
    'model' => 'cari',
    'title' => 'Cari',
])

<div {{ $attributes->merge([
    'class' => 'input-group input-group-sm',
    'style' => 'width: 16rem',
]) }}>
    <input class="form-control" type="search" wire:model.defer="{{ $model }}" wire:keydown.enter.stop="{{ $method }}" />
    <div class="input-group-append">
        <button class="btn btn-sm btn-default" type="button" wire:click="{{ $method }}">
            <i class="fas fa-search"></i>
            <span class="ml-1">{{ $title }}</span>
        </button>
    </div>
</div>
