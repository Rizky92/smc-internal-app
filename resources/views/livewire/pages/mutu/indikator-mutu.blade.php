<div>
    <x-flash />

    <x-card>
        <x-slot name="header">
            <x-row-col-flex class="mt-2">
                @can('mutu.indikator-mutu.create')
                    <x-filter.label constant-width>Unit :</x-filter.label>
                    <x-filter.select2 name="Unit" livewire :options="$this->unit" placeholder="SEMUA" />
                    <x-button variant="primary" size="sm" title="Tambah" icon="fas fa-plus" class="ml-auto" />
                @endcan

                <x-filter.button-export-excel class="ml-2" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2 mb-3">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="id" title="Nomor" />
                    <x-table.th title="Jenis Indikator" />
                    <x-table.th title="Indikator" />
                    <x-table.th title="Standar" />
                    <x-table.th title="Status" />
                </x-slot>
                <x-slot name="body">
                    {{--
                        @forelse ($this->collectionProperty as $item)
                        <x-table.tr>
                        <x-table.td>{{ $item->id }}</x-table.td>
                        <x-table.td>{{ $item->jenis_indikator }}</x-table.td>
                        <x-table.td>{{ $item->indikator }}</x-table.td>
                        <x-table.td>{{ $item->standar }}</x-table.td>
                        <x-table.td>{{ $item->status }}</x-table.td>
                        </x-table.tr>
                        @empty
                        <x-table.tr-empty colspan="5" padding />
                        @endforelse
                    --}}
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            {{-- <x-paginator :data="$this->collectionProperty" /> --}}
        </x-slot>
    </x-card>
</div>
