@props(['title', 'collection', 'model' => null, 'placeholder' => null, 'placeholderValue' => null, 'resetOn' => 'button#reset-filter'])

@once
    @push('css')
        <link href="{{ asset('plugins/select2/css/select2.min.css') }}" rel="stylesheet">
        <link href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}" rel="stylesheet">
    @endpush
    @push('js')
        <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
        <script>
            let dropdownSelect2 = $('select#{{ Str::slug($title) }}')

            $(document).ready(() => {
                dropdownSelect2.select2({
                    dropdownCssClass: 'text-sm px-0',
                })

                @notnull($model)
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
                @endnotnull

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

<div wire:ignore style="width: 24rem">
    <select class="form-control form-control-sm simple-select2-sm input-sm" id="{{ Str::slug($title) }}" autocomplete="off" name="{{ Str::camel($title) }}">
        @notnull($placeholder)
            <option value="{{ $placeholderValue }}">{{ $placeholder }}</option>
        @else
            <option value="">&nbsp;</option>
        @endnotnull
        @forelse ($collection as $key => $value)
            <option value="{{ $key }}">{{ $key }} - {{ $value }}</option>
        @empty
            <option disabled>---NO DATA---</option>
        @endforelse
    </select>
</div>
