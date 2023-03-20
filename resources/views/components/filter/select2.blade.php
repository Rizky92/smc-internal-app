@props([
    'name',
    'options' => [],
    'placeholder' => null,
    'placeholderValue' => null,
    'resetOn' => 'button#reset-filter',
    'selected' => null,

    'livewire' => false,
    'showKey' => false,
])

@php
    $isAssoc = Arr::isAssoc($options);

    $id = Str::slug($name);
    $title = Str::camel($name);
    $model = Str::camel($name);

    $options = collect($options);

    if (! $isAssoc) {
        $options = $options->mapWithKeys(fn($v, $k) => [$v => $v]);
    }

    if ($showKey) {
        $options = $options->mapWithKeys(fn ($v, $k) => [$k => "{$k} - {$v}"]);
    }

    $options = $options->all();
@endphp

@once
    @push('css')
        <link href="{{ asset('plugins/select2/css/select2.min.css') }}" rel="stylesheet">
        <link href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}" rel="stylesheet">
    @endpush
    @push('js')
        <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
        <script>
            let dropdownSelect2 = $('select#{{ $id }}')

            $(document).on('DOMContentLoaded', e => {
                dropdownSelect2.select2({
                    dropdownCssClass: 'text-sm px-0',
                })

                @if($livewire)
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
@endonce

<div wire:ignore {{ $attributes->whereDoesntStartWith('wire:')->only('class')->merge(['style' => 'width: max-content']) }}>
    <select id="{{ $id }}" name="{{ $name }}" class="form-control form-control-sm simple-select2-sm input-sm" autocomplete="off">
        @if ($placeholder)
            <option value="{{ $placeholderValue }}">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $key => $value)
            <option value="{{ $key }}" {{ $selected === $key ? 'selected' : null }}>{{ $value }}</option>
        @endforeach
    </select>
</div>
