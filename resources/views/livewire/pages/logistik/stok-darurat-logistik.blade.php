<div wire:init="loadProperties">
    <x-flash />

    <x-card use-loading>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.toggle model="tampilkanSaranOrderNol" title="Tampilkan Saran Order Nol" />
                <x-filter.button-export-excel class="ml-auto" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="kode_brng" title="Kode" />
                    <x-table.th name="nama_brng" title="Nama" />
                    <x-table.th name="satuan" title="Satuan" />
                    <x-table.th name="jenis" title="Jenis" />
                    <x-table.th name="nama_supplier" title="Supplier" />
                    <x-table.th name="stokmin" title="Min" />
                    <x-table.th name="stokmax" title="Max" />
                    <x-table.th name="stok" title="Saat ini" />
                    <x-table.th name="saran_order" title="Saran order" />
                    <x-table.th name="harga" title="Harga Per Unit" />
                    <x-table.th name="total_harga" title="Total Harga" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->stokDaruratLogistik as $barang)
                        <x-table.tr>
                            <x-table.td>
                                {{ $barang->kode_brng }}
                            </x-table.td>
                            <x-table.td>
                                {{ $barang->nama_brng }}
                            </x-table.td>
                            <x-table.td>{{ $barang->satuan }}</x-table.td>
                            <x-table.td>{{ $barang->jenis }}</x-table.td>
                            <x-table.td>
                                {{ $barang->nama_supplier }}
                            </x-table.td>
                            <x-table.td>{{ $barang->stokmin }}</x-table.td>
                            <x-table.td>{{ $barang->stokmax }}</x-table.td>
                            <x-table.td>{{ $barang->stok }}</x-table.td>
                            <x-table.td>
                                {{ $barang->saran_order }}
                            </x-table.td>
                            <x-table.td>
                                {{ rp($barang->harga) }}
                            </x-table.td>
                            <x-table.td>
                                {{ rp($barang->total_harga) }}
                            </x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="11" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->stokDaruratLogistik" />
        </x-slot>
    </x-card>
</div>
