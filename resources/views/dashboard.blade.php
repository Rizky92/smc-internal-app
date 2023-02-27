<x-base-layout title="Dashboard">
    <x-flash />

    <div class="px-5 mt-3">
        <div class="row">
            <div class="col-12">
                <h5 class="font-weight-normal">Selamat Datang, <span class="font-weight-bold">{{ Str::of(auth()->user()->nama)->title() }}!</span></h5>
            </div>
        </div>
        <div class="row"></div>
        <div class="row">
            @once
                @push('js')
                    <script>
                        const notSelectedTable = document.querySelector('table#not-selected')
                        const selectedTable = document.querySelector('table#selected')

                        const selectedBody = selectedTable.querySelector('tbody')
                        const notSelectedBody = notSelectedTable.querySelector('tbody')

                        function loadData(e) {
                            let dataset = e.dataset
                            let rowParent = e.parentElement.parentElement

                            let isSelected = selectedTable.contains(rowParent)
                            let isNotSelected = notSelectedTable.contains(rowParent)

                            if (isSelected) {
                                selectedBody.removeChild(rowParent)
                                notSelectedBody.appendChild(rowParent)
                            } else if (isNotSelected) {
                                notSelectedBody.removeChild(rowParent)
                                selectedBody.appendChild(rowParent)
                            }
                        }
                    </script>
                @endpush
            @endonce
            <div class="col-12 d-flex justify-content-start align-items-start" style="row-gap: 1rem">
                <div class="table-responsive">
                    <x-table id="not-selected">
                        <x-slot name="columns">
                            <x-table.th style="width: 25%" title="Nama Field" />
                            <x-table.th style="width: 75%" title="Judul Menu" />
                        </x-slot>
                        <x-slot name="body">
                            <x-table.tr>
                                <x-table.td clickable data-field="hello" data-judul="world">Hello</x-table.td>
                                <x-table.td>World</x-table.td>
                            </x-table.tr>
                            <x-table.tr>
                                <x-table.td clickable data-field="john" data-judul="connor">john</x-table.td>
                                <x-table.td>connor</x-table.td>
                            </x-table.tr>
                            <x-table.tr>
                                <x-table.td clickable data-field="will" data-judul="smith">will</x-table.td>
                                <x-table.td>smith</x-table.td>
                            </x-table.tr>
                        </x-slot>
                    </x-table>
                </div>
                <div class="table-responsive">
                    <x-table id="selected">
                        <x-slot name="columns">
                            <x-table.th style="width: 25%" title="Nama Field" />
                            <x-table.th style="width: 75%" title="Judul Menu" />
                        </x-slot>
                        <x-slot name="body">

                        </x-slot>
                    </x-table>
                </div>
            </div>
        </div>
    </div>
</x-base-layout>
