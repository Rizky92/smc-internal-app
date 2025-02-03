@props([
    'livewire' => false,

    'id' => null,
    'name' => null,
    'lookup' => null,
    'model' => null,
    'method' => null,
])

@push('js')
    <script>
        $(document).on('DOMContentLoaded', e => {
            $('#{{ $id }}').change(e => {
                let isChecked = e.target.checked
                let els = $('input[type=checkbox][id*={{ $lookup }}]')

                let checkboxes = new Map()

                els.each((i, el) => {
                    el.checked = isChecked

                    checkboxes.set(el.name, isChecked)
                })

                if (!isChecked) {
                    checkboxes.clear()
                }

                @if ($livewire)
                    @if (!is_null($method) && is_null($model))
                        @this.{{ $method }}(isChecked)
                    @endif
                    @if (is_null($method))
                        @this.set('{{ $model }}', Object.fromEntries(checkboxes), true)
                    @endif
                @endif
            })

            $('#{{ $id }}').on('clear-selected', e => {

            })
        })
    </script>
@endpush

<x-table.th :attributes="$attributes">
    <input id="{{ $id }}" type="checkbox" name="{{ $name }}" />
    <label for="{{ $id }}"></label>
</x-table.th>
