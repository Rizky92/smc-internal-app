<div wire:init="loadProperties">
    <x-flash />

    <x-card use-default-filter use-loading>
        <x-slot name="body">
            <x-navtabs livewire selected="obat">
                <x-slot name="tabs">
                    <x-navtabs.tab id="obat" title="Obat" />
                    <x-navtabs.tab id="alkes" title="Alkes" />
                </x-slot>
                <x-slot name="contents">
                    <x-navtabs.content id="obat">
                        <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                            <x-slot name="columns">
                                <x-table.th title="Kode Barang" />
                                <x-table.th title="Nama Barang" />
                                <x-table.th title="Harga Satuan" />
                                <x-table.th title="Total Pesanan" />
                                <x-table.th title="Total Harga" />
                                <x-table.th title="Total Pesanan Bulan Lalu" />
                                <x-table.th title="Total Harga Bulan Lalu" />
                                <x-table.th title="Selisih Pesanan" />
                                <x-table.th name="selisih_harga" title="Selisih Harga" />
                            </x-slot>
                            <x-slot name="body">
                                @forelse ($this->rincianPerbandinganBarangPO as $obat)
                                    <x-table.tr>
                                        <x-table.td>
                                            {{ $obat->kode_brng }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $obat->nama_brng }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ rp($obat->harga_satuan) }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $obat->total_pemesanan }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ rp($obat->total_harga) }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $obat->total_pemesanan_bulan_lalu }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ rp($obat->total_harga_bulan_lalu) }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $obat->selisih_pemesanan }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ rp($obat->selisih_harga) }}
                                        </x-table.td>
                                    </x-table.tr>
                                @empty
                                    <x-table.tr-empty colspan="9" padding />
                                @endforelse
                            </x-slot>
                        </x-table>
                        <x-paginator :data="$this->rincianPerbandinganBarangPO" />
                    </x-navtabs.content>
                    <x-navtabs.content id="alkes">
                        <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                            <x-slot name="columns">
                                <x-table.th title="Kode Barang" />
                                <x-table.th title="Nama Barang" />
                                <x-table.th title="Harga Satuan" />
                                <x-table.th title="Total Pesanan" />
                                <x-table.th title="Total Harga" />
                                <x-table.th title="Total Pesanan Bulan Lalu" />
                                <x-table.th title="Total Harga Bulan Lalu" />
                                <x-table.th title="Selisih Pesanan" />
                                <x-table.th name="selisih_harga" title="Selisih Harga" />
                            </x-slot>
                            <x-slot name="body">
                                @forelse ($this->rincianPerbandinganAlkesPO as $alkes)
                                    <x-table.tr>
                                        <x-table.td>
                                            {{ $alkes->kode_brng }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $alkes->nama_brng }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ rp($alkes->harga_satuan) }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $alkes->total_pemesanan }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ rp($alkes->total_harga) }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $alkes->total_pemesanan_bulan_lalu }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ rp($alkes->total_harga_bulan_lalu) }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $alkes->selisih_pemesanan }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ rp($alkes->selisih_harga) }}
                                        </x-table.td>
                                    </x-table.tr>
                                @empty
                                    <x-table.tr-empty colspan="9" padding />
                                @endforelse
                            </x-slot>
                        </x-table>
                        <x-paginator :data="$this->rincianPerbandinganAlkesPO" />
                    </x-navtabs.content>
                </x-slot>
            </x-navtabs>
        </x-slot>
    </x-card>
</div>
