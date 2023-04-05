@props([
    'livewire' => false,

    'id' => null,
    'name' => null,
    'lookup' => null,
    'model' => null,
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

                    checkboxes.set(el.value, isChecked)
                })

                if (!isChecked) {
                    checkboxes.clear()
                }

                @if ($livewire)
                    @this.set('{{ $model }}', Object.fromEntries(checkboxes), true)
                @endif
            })
        })
    </script>
@endpush

<x-table.th :attributes="$attributes">
    <input id="{{ $id }}" type="checkbox" name="{{ $name }}">
    <label for="{{ $id }}"></label>
</x-table.th>
