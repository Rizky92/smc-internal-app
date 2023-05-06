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

                console.log({isChecked})

                let checkboxes = new Map()

                els.each((i, el) => {
                    el.checked = isChecked

                    checkboxes.set(el.name, isChecked)

                    console.log({checkboxes, name: el.name, checked: el.checked})
                })

                if (!isChecked) {
                    checkboxes.clear()
                }

                console.log(checkboxes)

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
