<div>
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
                    <x-table.th name="kode_brng" title="Kode" />
                    <x-table.th name="nama_brng" title="Nama" />
                    <x-table.th name="kategori" title="Kategori" />
                    <x-table.th name="satuan" title="Satuan" />
                    <x-table.th name="stok" title="Stok saat ini" />
                    <x-table.th name="h_beli" title="Harga" />
                    <x-table.th name="projeksi_harga" title="Projeksi Harga" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->collection as $obat)
                        <x-table.tr>
                            <x-table.td>
                                {{ $obat->nm_bangsal }}
                            </x-table.td>
                            <x-table.td>{{ $obat->kode_brng }}</x-table.td>
                            <x-table.td>{{ $obat->nama_brng }}</x-table.td>
                            <x-table.td>{{ $obat->kategori }}</x-table.td>
                            <x-table.td>{{ $obat->satuan }}</x-table.td>
                            <x-table.td>{{ $obat->stok }}</x-table.td>
                            <x-table.td>
                                {{ rp($obat->h_beli) }}
                            </x-table.td>
                            <x-table.td>
                                {{ rp($obat->projeksi_harga) }}
                            </x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="8" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->collection" />
        </x-slot>
    </x-card>
</div>
