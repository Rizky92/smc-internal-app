<div wire:init="loadProperties">
    <x-flash />

    <x-card use-loading>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.label constant-width>Ruangan :</x-filter.label>
                <x-filter.select2 livewire name="kodeBangsal" placeholder="-" :options="$this->bangsal" />
                <x-filter.button-export-excel class="ml-auto" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                <x-filter.select-perpage :constantWidth="true" />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="nm_bangsal" title="Ruangan" />
                    <x-table.th name="kategori" title="Kategori" />
                    <x-table.th name="jumlah_kategori" title="Jumlah Kategori" />
                    <x-table.th name="total_harga" title="Total Harga" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->collection as $obat)
                        <x-table.tr>
                            <x-table.td>{{ $obat->nm_bangsal }}</x-table.td>
                            <x-table.td>{{ $obat->kategori }}</x-table.td>
                            <x-table.td>{{ $obat->jumlah_kategori }}</x-table.td>
                            <x-table.td>{{ rp($obat->total_harga) }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="4" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->collection" />
        </x-slot>
    </x-card>
</div>
