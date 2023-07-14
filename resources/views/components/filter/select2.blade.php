@props([
    'livewire' => false,

    'name',
    'options' => collect(),
    'placeholder' => null,
    'placeholderValue' => null,
    'resetOn' => 'button#reset-filter',
    'selected' => null,
    'showKey' => false
])

@php
    $isList = $options->isList();
    
    $id = Str::slug($name);
    $model = Str::camel($name);
    
    $options = $options
        ->when($isList, fn ($c) => $c->mapWithKeys(fn ($v, $k) => [$v => $v]))
        ->when($showKey, fn ($c) => $c->mapWithKeys(fn ($v, $k) => [$k => "{$k} - {$v}"]))
        ->all();
@endphp

@push('css')
    @once
        <link href="{{ asset('css/select2.min.css') }}" rel="stylesheet">
        <link href="{{ asset('css/select2-bootstrap4.min.css') }}" rel="stylesheet">
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
        let dropdownSelect2 = $('select#{{ $id }}')

        $(document).on('DOMContentLoaded', e => {
            dropdownSelect2.select2({
                dropdownCssClass: 'text-sm px-0',
            })

            @if ($livewire)
                Livewire.hook('element.updated', (el, component) => {
                    dropdownSelect2.select2({
                        dropdownCssClass: 'text-sm px-0',
                    })
                })

                dropdownSelect2.on('select2:select', e => {
                    @this.set('{{ $model }}', dropdownSelect2.val(), true)
                })

                dropdownSelect2.on('select2:unselect', e => {
                    @this.set('{{ $model }}', dropdownSelect2.val(), true)
                })
            @endif

            @notnull($resetOn)
                $('{{ $resetOn }}').click(e => {
                    dropdownSelect2.val('')

                    dropdownSelect2.trigger('change')
                })
            @endnotnull
        })
    </script>
@endpush

<div wire:ignore {{ $attributes
    ->only('class')
    ->merge(['style' => 'width: max-content'])
}}>
    <select id="{{ $id }}" name="{{ $name }}" class="form-control form-control-sm simple-select2-sm input-sm" autocomplete="off">
        @if ($placeholder)
            <option value="{{ $placeholderValue ?? '' }}">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $key => $value)
            <option value="{{ $key }}" {{ $selected === $key ? 'selected' : null }}>{{ $value }}</option>
        @endforeach
    </select>
</div>
