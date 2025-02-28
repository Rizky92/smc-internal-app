@props([
    'id',
    'name' => null,
    'model' => null,
    'options' => [],
    'placeholder' => null,
    'resetOn' => 'button#reset-filter',
    'showKey' => false,
    'width' => 'max-content',
])

@php
    if (! $options instanceof \Illuminate\Support\Collection) {
        $options = collect($options);
    }

    $livewire = ! is_null($model);

    $isList = $options->isList();

    $options = $options
        ->when($isList, fn ($c) => $c->mapWithKeys(fn ($v, $k) => [$v => $v]))
        ->when($showKey, fn ($c) => $c->mapWithKeys(fn ($v, $k) => [$k => "{$k} - {$v}"]));

    $styles = [
        'max-content' => ['style' => 'width: max-content'],
        'full-width' => ['style' => 'width: 100%'],
    ];
@endphp

@push('css')
    @once
        <link href="{{ asset('css/select2.min.css') }}" rel="stylesheet" />
        <link href="{{ asset('css/select2-bootstrap4.min.css') }}" rel="stylesheet" />
        <style>
            .select2-selection__arrow {
                top: 0 !important;
            }

            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 2rem !important;
            }

            .select2-container .select2-selection--single .select2-selection__rendered {
                padding-left: 0 !important;
                margin-left: -0.125rem !important;
            }
        </style>
    @endonce
@endpush

@push('js')
    @once
        <script src="{{ asset('js/select2.full.min.js') }}"></script>
    @endonce

    <script>
        window.select2 = () => {
            $('select#{{ $id }}').select2({
                dropdownCssClass: 'text-sm px-0',
            }).on('change', () => {
                let data = $('select#{{ $id }}').select2("val")

                @if ($livewire)
                    @this.set('{{ $model }}', data, true)
                @endif
            })

            @if ($placeholder)
                $('select#{{ $id }}').val('-')
                $('select#{{ $id }}').trigger('change')
            @endif
        }

        @if ($livewire)
            $(document).on('livewire:load', () => {
                select2()

                Livewire.on('select2.hydrate', () => {
                    select2()
                })
            })
        @endif
    </script>
@endpush

<div wire:ignore {{
    $attributes
        ->only('class')
        ->merge($styles[$width])
}}>
    <select @if ($livewire) wire:model.defer="{{ $model }}" @endif id="{{ $id }}" name="{{ $name }}" class="form-control form-control-sm simple-select2-sm input-sm" autocomplete="off">
        @if ($placeholder)
            <option disabled {{ $options->has($this->$model) ? null : 'selected' }}>
                {{ $placeholder }}
            </option>
        @endif

        @foreach ($options->all() as $key => $value)
            <option value="{{ $key }}" {{ $this->$model === $key ? 'selected' : null }}>
                {{ $value }}
            </option>
        @endforeach
    </select>
</div>
