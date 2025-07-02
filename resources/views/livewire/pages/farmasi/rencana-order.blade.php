<div wire:init="loadProperties">
    <x-flash />

    <x-card use-loading>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.button-export-excel class="ml-auto" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap style="width: 140rem">
                <x-slot name="columns">
                    <x-table.th name="kode_brng" title="Kode" style="width: 13ch" />
                    <x-table.th name="nama_brng" title="Nama" style="width: 50ch" />
                    <x-table.th name="satuan_kecil" title="Satuan" style="width: 12ch" />
                    <x-table.th name="kategori" title="Kategori" style="width: 25ch" />
                    <x-table.th name="stokminimal" title="Stok minimal" align="right" style="width: 24ch" />
                    <x-table.th name="stok_sekarang_ifa" title="Stok Farmasi A Sekarang" align="right" style="width: 11ch" />
                    <x-table.th name="stok_sekarang_ap" title="Stok Farmasi B Sekarang" align="right" style="width: 11ch" />
                    <x-table.th name="stok_sekarang_ifi" title="Stok Farmasi RWI Sekarang" align="right" style="width: 11ch" />
                    <x-table.th name="stok_sekarang_ifg" title="Stok Farmasi IGD Sekarang" align="right" style="width: 11ch" />
                    <x-table.th name="stok_keluar_medis_14_hari" title="Stok Keluar Medis (14 Hari)" align="right" style="width: 11ch" />
                    <x-table.th name="saran_order" title="Saran order" align="right" style="width: 15ch" />
                    <x-table.th name="nama_industri" title="Supplier" style="width: 40ch" />
                    <x-table.th name="harga_beli" colspan="2" title="Harga Per Unit" align="right" style="width: 18ch" />
                    <x-table.th name="harga_beli_total" colspan="2" title="Total Harga" align="right" style="width: 15ch" />
                    <x-table.th name="harga_beli_terakhir" colspan="2" title="Harga Beli Terakhir" align="right" style="width: 25ch" />
                    <x-table.th name="diskon_terakhir" title="Diskon Terakhir (%)" align="right" style="width: 24ch" />
                    <x-table.th name="supplier_terakhir" title="Supplier Terakhir" style="width: 40ch" />
                    <x-table.th name="ke_pasien_14_hari" title="Jumlah Ke Pasien (14 Hari)" align="right" style="width: 40ch" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->stokDaruratObat as $obat)
                        <x-table.tr>
                            <x-table.td>{{ $obat->kode_brng }}</x-table.td>
                            <x-table.td>{{ $obat->nama_brng }}</x-table.td>
                            <x-table.td>
                                {{ $obat->satuan_kecil }}
                            </x-table.td>
                            <x-table.td>{{ $obat->kategori }}</x-table.td>
                            <x-table.td class="text-right">
                                {{ $obat->stokminimal }}
                            </x-table.td>
                            <x-table.td class="text-right">
                                {{ $obat->stok_sekarang_ifa }}
                            </x-table.td>
                            <x-table.td class="text-right">
                                {{ $obat->stok_sekarang_ap }}
                            </x-table.td>
                            <x-table.td class="text-right">
                                {{ $obat->stok_sekarang_ifi }}
                            </x-table.td>
                            <x-table.td class="text-right">
                                {{ $obat->stok_sekarang_ifg }}
                            </x-table.td>
                            <x-table.td class="text-right">
                                {{ $obat->stok_keluar_medis_14_hari }}
                            </x-table.td>
                            <x-table.td class="text-right">
                                {{ $obat->saran_order }}
                            </x-table.td>
                            <x-table.td>
                                {{ $obat->nama_industri }}
                            </x-table.td>
                            <x-table.td-money :value="$obat->harga_beli" />
                            <x-table.td-money :value="$obat->harga_beli_total" />
                            <x-table.td-money :value="$obat->harga_beli_terakhir" />
                            <x-table.td class="text-right">
                                {{ $obat->diskon_terakhir }}
                            </x-table.td>
                            <x-table.td>
                                {{ $obat->supplier_terakhir }}
                            </x-table.td>
                            <x-table.td class="text-right">
                                {{ $obat->ke_pasien_14_hari }}
                            </x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="25" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->stokDaruratObat" />
        </x-slot>
    </x-card>
</div>
