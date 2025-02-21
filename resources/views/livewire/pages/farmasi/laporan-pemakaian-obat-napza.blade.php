<div wire:init="loadProperties">
    <x-flash />

    <x-card use-default-filter use-loading>
        <x-slot name="body">
            <x-navtabs livewire selected="narkotika">
                <x-slot name="tabs">
                    <x-navtabs.tab id="narkotika" title="Narkotika" />
                    <x-navtabs.tab id="psikotropika" title="Psikotropika" />
                </x-slot>
                <x-slot name="contents">
                    <x-navtabs.content id="narkotika">
                        <x-table :sortColumns="$sortColumns" style="min-width: 100%" sortable zebra hover sticky nowrap>
                            <x-slot name="columns">
                                <x-table.th name="kode_brng" title="Kode" />
                                <x-table.th name="nama_brng" title="Nama" />
                                <x-table.th name="nama" title="Golongan" />
                                <x-table.th name="satuan" title="Satuan" />
                                <x-table.th align="right" title="Stok Awal" />
                                <x-table.th align="right" title="Transfer Obat Masuk" />
                                <x-table.th align="right" title="Penerimaan Obat" />
                                <x-table.th align="right" title="Hibah Obat" />
                                <x-table.th align="right" title="Obat Retur" />
                                <x-table.th align="right" title="Total Masuk" />
                                <x-table.th align="right" title="Pemberian Obat" />
                                <x-table.th align="right" title="Penjualan Obat" />
                                <x-table.th align="right" title="Transfer Obat Keluar" />
                                <x-table.th align="right" title="Retur ke Supplier" />
                                <x-table.th align="right" title="Total Keluar" />
                                <x-table.th align="right" title="Stok Akhir" />
                            </x-slot>
                            <x-slot name="body">
                                @forelse ($this->dataPemakaianObatNarkotika as $item)
                                    <x-table.tr>
                                        <x-table.td>
                                            {{ $item->kode_brng }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->nama_brng }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->nama }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->satuan }}
                                        </x-table.td>
                                        <x-table.td class="text-right">
                                            {{ number_format($stokAwal = $item->stok_awal > 0 ? $item->stok_awal : $item->stok_awal_terakhir, 0, ',', '.') }}
                                        </x-table.td>
                                        <x-table.td class="text-right">
                                            {{ number_format($item->tf_masuk, 0, ',', '.') }}
                                        </x-table.td>
                                        <x-table.td class="text-right">
                                            {{ number_format($item->penerimaan_obat, 0, ',', '.') }}
                                        </x-table.td>
                                        <x-table.td class="text-right">
                                            {{ number_format($item->hibah_obat, 0, ',', '.') }}
                                        </x-table.td>
                                        <x-table.td class="text-right">
                                            {{ number_format($item->retur_pasien + $item->hapus_beriobat, 0, ',', '.') }}
                                        </x-table.td>
                                        <x-table.td class="text-right">
                                            {{ number_format($totalMasuk = $item->tf_masuk + $item->penerimaan_obat + $item->hibah_obat + $item->retur_pasien + $item->hapus_beriobat, 0, ',', '.') }}
                                        </x-table.td>
                                        <x-table.td class="text-right">
                                            {{ number_format($item->pemberian_obat + $item->hapus_beriobat, 0, ',', '.') }}
                                        </x-table.td>
                                        <x-table.td class="text-right">
                                            {{ number_format($item->penjualan_obat, 0, ',', '.') }}
                                        </x-table.td>
                                        <x-table.td class="text-right">
                                            {{ number_format($item->tf_keluar, 0, ',', '.') }}
                                        </x-table.td>
                                        <x-table.td class="text-right">
                                            {{ number_format($item->retur_supplier, 0, ',', '.') }}
                                        </x-table.td>
                                        <x-table.td class="text-right">
                                            {{ number_format($totalKeluar = $item->pemberian_obat + $item->hapus_beriobat + $item->penjualan_obat + $item->tf_keluar + $item->retur_supplier, 0, ',', '.') }}
                                        </x-table.td>
                                        <x-table.td class="text-right">
                                            {{ number_format($stokAwal + $totalMasuk - $totalKeluar, 0, ',', '.') }}
                                        </x-table.td>
                                    </x-table.tr>
                                @empty
                                    <x-table.tr-empty colspan="18" padding />
                                @endforelse
                            </x-slot>
                        </x-table>
                        <x-paginator class="px-4 py-3 bg-light border-top" :data="$this->dataPemakaianObatNarkotika" />
                    </x-navtabs.content>
                    <x-navtabs.content id="psikotropika">
                        <x-table :sortColumns="$sortColumns" style="min-width: 100%" sortable zebra hover sticky nowrap>
                            <x-slot name="columns">
                                <x-table.th name="kode_brng" title="Kode" />
                                <x-table.th name="nama_brng" title="Nama" />
                                <x-table.th name="nama" title="Golongan" />
                                <x-table.th name="satuan" title="Satuan" />
                                <x-table.th align="right" title="Stok Awal" />
                                <x-table.th align="right" title="Transfer Obat Masuk" />
                                <x-table.th align="right" title="Penerimaan Obat" />
                                <x-table.th align="right" title="Hibah Obat" />
                                <x-table.th align="right" title="Obat Retur" />
                                <x-table.th align="right" title="Total Masuk" />
                                <x-table.th align="right" title="Pemberian Obat" />
                                <x-table.th align="right" title="Penjualan Obat" />
                                <x-table.th align="right" title="Transfer Obat Keluar" />
                                <x-table.th align="right" title="Retur ke Supplier" />
                                <x-table.th align="right" title="Total Keluar" />
                                <x-table.th align="right" title="Stok Akhir" />
                            </x-slot>
                            <x-slot name="body">
                                @forelse ($this->dataPemakaianObatPsikotropika as $item)
                                    <x-table.tr>
                                        <x-table.td>
                                            {{ $item->kode_brng }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->nama_brng }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->nama }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->satuan }}
                                        </x-table.td>
                                        <x-table.td class="text-right">
                                            {{ number_format($stokAwal = $item->stok_awal > 0 ? $item->stok_awal : $item->stok_awal_terakhir, 0, ',', '.') }}
                                        </x-table.td>
                                        <x-table.td class="text-right">
                                            {{ number_format($item->tf_masuk, 0, ',', '.') }}
                                        </x-table.td>
                                        <x-table.td class="text-right">
                                            {{ number_format($item->penerimaan_obat, 0, ',', '.') }}
                                        </x-table.td>
                                        <x-table.td class="text-right">
                                            {{ number_format($item->hibah_obat, 0, ',', '.') }}
                                        </x-table.td>
                                        <x-table.td class="text-right">
                                            {{ number_format($item->retur_pasien + $item->hapus_beriobat, 0, ',', '.') }}
                                        </x-table.td>
                                        <x-table.td class="text-right">
                                            {{ number_format($totalMasuk = $item->tf_masuk + $item->penerimaan_obat + $item->hibah_obat + $item->retur_pasien + $item->hapus_beriobat, 0, ',', '.') }}
                                        </x-table.td>
                                        <x-table.td class="text-right">
                                            {{ number_format($item->pemberian_obat + $item->hapus_beriobat, 0, ',', '.') }}
                                        </x-table.td>
                                        <x-table.td class="text-right">
                                            {{ number_format($item->penjualan_obat, 0, ',', '.') }}
                                        </x-table.td>
                                        <x-table.td class="text-right">
                                            {{ number_format($item->tf_keluar, 0, ',', '.') }}
                                        </x-table.td>
                                        <x-table.td class="text-right">
                                            {{ number_format($item->retur_supplier, 0, ',', '.') }}
                                        </x-table.td>
                                        <x-table.td class="text-right">
                                            {{ number_format($totalKeluar = $item->pemberian_obat + $item->hapus_beriobat + $item->penjualan_obat + $item->tf_keluar + $item->retur_supplier, 0, ',', '.') }}
                                        </x-table.td>
                                        <x-table.td class="text-right">
                                            {{ number_format($stokAwal + $totalMasuk - $totalKeluar, 0, ',', '.') }}
                                        </x-table.td>
                                    </x-table.tr>
                                @empty
                                    <x-table.tr-empty colspan="18" padding />
                                @endforelse
                            </x-slot>
                        </x-table>
                        <x-paginator class="px-4 py-3 bg-light border-top" :data="$this->dataPemakaianObatPsikotropika" />
                    </x-navtabs.content>
                </x-slot>
            </x-navtabs>
        </x-slot>
    </x-card>
</div>
