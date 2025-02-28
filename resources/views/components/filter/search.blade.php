@props([
    'method' => 'searchData',
    'model' => 'cari',
    'title' => 'Cari',
])

<div {{
    $attributes->merge([
        'class' => 'input-group input-group-sm',
        'style' => 'width: 16rem',
    ])
}}>
    <input class="form-control" type="search" wire:model.defer="{{ $model }}" wire:keydown.enter.stop="{{ $method }}" />
    <div class="input-group-append">
        <x-filter.button-refresh :method="$method" :title="$title" icon="fas fa-search" />
    </div>
</div>
