@props([
    'livewire' => false,
    'name',
    'model' => null,
    'event' => null,
    'options' => [],
    'placeholder' => null,
    'placeholderValue' => null,
    'resetOn' => 'button#reset-filter',
    'selected' => null,
    'showKey' => false,
    'width' => '20rem',
])

@php
    $options = collect($options);

    $isList = $options->isList();

    $id = Str::slug($name);
    $model ??= Str::camel($name);

    $options = $options
        ->when($isList, fn ($c) => $c->mapWithKeys(fn ($v, $k) => [$v => $v]))
        ->when($showKey, fn ($c) => $c->mapWithKeys(fn ($v, $k) => [$k => "{$k} - {$v}"]))
        ->all();

    $varName = '_' . str()->random(10);
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
        let {{ $varName }} = $('select#{{ $id }}')

        $(document).on('DOMContentLoaded', e => {
            {{ $varName }}.select2({
                dropdownCssClass: 'text-sm px-0',
            })

            @if ($livewire)
                Livewire.hook('morph.updated', ({ el, component }) => {
                    {{ $varName }}.select2({
                        dropdownCssClass: 'text-sm px-0',
                    })
                })

                @if ($model)
                    // Deferred: the choice is applied with the next search, not
                    // the moment it is made, because report queries are heavy.
                    // In Livewire 2 the third argument true meant "defer"; in
                    // Livewire 3 it means "live".
                    {{ $varName }}.on('select2:select', e => {
                        @this.set('{{ $model }}', {{ $varName }}.val(), false)
                    })

                    {{ $varName }}.on('select2:unselect', e => {
                        @this.set('{{ $model }}', {{ $varName }}.val(), false)
                    })

                    @if ($event)
                        $(document).on('{{ $event }}', e => {
                            {{ $varName }}.val(e.detail.tanggalTarikan)

                            {{ $varName }}.trigger('change')
                        })
                    @endif
                @endif
            @endif

            @notnull($resetOn)
                $('{{ $resetOn }}').click(e => {
                    {{ $varName }}.val('')

                    {{ $varName }}.trigger('change')
                })
            @endnotnull
        })
    </script>
@endpush

<div wire:ignore {{
    $attributes
        ->only('class')
        ->merge(['style' => 'min-width: 20rem; max-width: ' . $width])
}}>
    <select id="{{ $id }}" name="{{ $name }}" class="form-control form-control-sm simple-select2-sm input-sm" autocomplete="off">
        @if ($placeholder)
            <option value="{{ $placeholderValue ?? '' }}">
                {{ $placeholder }}
            </option>
        @endif

        @foreach ($options as $key => $value)
            <option value="{{ $key }}" {{ $selected === $key ? 'selected' : null }}>
                {{ $value }}
            </option>
        @endforeach
    </select>
</div>
