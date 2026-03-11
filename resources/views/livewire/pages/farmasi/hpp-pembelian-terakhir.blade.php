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
                    <x-table.th name="no_faktur" title="No Faktur" />
                    <x-table.th name="tgl_pesan" title="Tanggal" />
                    <x-table.th name="nm_bangsal" title="Ruangan" />
                    <x-table.th name="kode_brng" title="Kode" />
                    <x-table.th name="nama_brng" title="Nama" />
                    <x-table.th name="kode_satbesar" title="Satuan Besar" />
                    <x-table.th name="isi" title="Isi" />
                    <x-table.th name="kode_sat" title="Satuan Kecil" />
                    <x-table.th name="kapasitas" title="Kapasitas" />
                    <x-table.th name="stok" title="Stok" />
                    <x-table.th name="h_pesan" title="Harga Beli" />
                    <x-table.th name="dis" title="Diskon %" />
                    <x-table.th name="harga_satuan" title="Harga Satuan" />
                    <x-table.th name="hpp" title="HPP" />
                    <x-table.th name="total_nilai_stok" title="Total Nilai Stok" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->collection as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->no_faktur ?? '-' }}</x-table.td>
                            <x-table.td>{{ $item->tgl_pesan ?? '-' }}</x-table.td>
                            <x-table.td>{{ $item->nm_bangsal }}</x-table.td>
                            <x-table.td>{{ $item->kode_brng }}</x-table.td>
                            <x-table.td>{{ $item->nama_brng }}</x-table.td>
                            <x-table.td>{{ $item->kode_satbesar }}</x-table.td>
                            <x-table.td>{{ $item->isi }}</x-table.td>
                            <x-table.td>{{ $item->kode_sat }}</x-table.td>
                            <x-table.td>{{ $item->kapasitas }}</x-table.td>
                            <x-table.td>{{ $item->stok }}</x-table.td>
                            <x-table.td>{{ rp($item->h_pesan) }}</x-table.td>
                            <x-table.td>{{ $item->dis }}%</x-table.td>
                            <x-table.td>{{ rp($item->harga_satuan) }}</x-table.td>
                            <x-table.td>{{ rp($item->hpp) }}</x-table.td>
                            <x-table.td>{{ rp($item->total_nilai_stok) }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="15" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->collection" />
        </x-slot>
    </x-card>
</div>
