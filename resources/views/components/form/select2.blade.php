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
            const select = $('select#{{ $id }}')

            // This runs again after every round trip that dispatches
            // select2.hydrate. Rebind the handler instead of stacking a new one
            // on each run.
            select.off('change.livewire')

            select.select2({
                dropdownCssClass: 'text-sm px-0',
            })

            @if ($livewire)
                // Show what the component holds, and refresh only Select2's own
                // display ("change.select2"). Resetting to the placeholder with a
                // plain change event, as this used to, wrote the placeholder back
                // into the model on every round trip and threw the user's choice
                // away.
                const current = @this.get('{{ $model }}')
                const hasOption = current !== null && select.find('option').filter((_, o) => o.value === String(current)).length > 0

                select.val(hasOption ? String(current) : '{{ $placeholder ?? '' }}').trigger('change.select2')

                select.on('change.livewire', () => {
                    // Deferred: the choice travels with the next request rather
                    // than starting one. In Livewire 2 the third argument meant
                    // "defer"; in Livewire 3 it means "live", and a live update
                    // is exactly what re-ran this initialiser.
                    @this.set('{{ $model }}', select.select2('val'), false)
                })
            @elseif ($placeholder)
                select.val('-').trigger('change')
            @endif
        }

        @if ($livewire)
            // Not DOMContentLoaded: under Livewire 3 the component is not
            // registered yet at that point, so @this is undefined and the
            // initialiser, which reads the model, would throw before binding
            // its change handler.
            document.addEventListener('livewire:initialized', () => {
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
    <select @if ($livewire) wire:model="{{ $model }}" @endif id="{{ $id }}" name="{{ $name }}" class="form-control form-control-sm simple-select2-sm input-sm" autocomplete="off">
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
