<div wire:init="loadProperties">
    <x-flash />

    <x-card>
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
                    <x-table.th name="stokminimal" title="Stok minimal" style="width: 24ch" />
                    <x-table.th name="stok_sekarang_ap" title="Stok Farmasi B Sekarang" style="width: 17ch" />
                    <x-table.th name="stok_sekarang_ifi" title="Stok Farmasi RWI Sekarang" style="width: 17ch" />
                    <x-table.th name="saran_order" title="Saran order" style="width: 14ch" />
                    <x-table.th name="ke_pasien_14_hari" title="Jumlah Ke Pasien (14 Hari)" style="width: 40ch" />
                    <x-table.th name="nama_industri" title="Supplier" style="width: 40ch" />
                    <x-table.th name="harga_beli" title="Harga Per Unit" style="width: 18ch" />
                    <x-table.th name="harga_beli_total" title="Total Harga" style="width: 15ch" />
                    <x-table.th name="harga_beli_terakhir" title="Harga Beli Terakhir" style="width: 25ch" />
                    <x-table.th name="diskon_terakhir" title="Diskon Terakhir (%)" style="width: 24ch" />
                    <x-table.th name="supplier_terakhir" title="Supplier Terakhir" style="width: 40ch" />
                    <x-table.th name="pemakaian_3_bulan" title="Pemakaian 3 Bulan" style="width: 40ch" />
                    <x-table.th name="pemakaian_1_bulan" title="Pemakaian 1 Bulan" style="width: 40ch" />
                    <x-table.th name="pemakaian_1_minggu" title="Pemakaian 1 Minggu" style="width: 40ch" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->stokDaruratObat as $obat)
                        <x-table.tr>
                            <x-table.td>{{ $obat->kode_brng }}</x-table.td>
                            <x-table.td>{{ $obat->nama_brng }}</x-table.td>
                            <x-table.td>{{ $obat->satuan_kecil }}</x-table.td>
                            <x-table.td>{{ $obat->kategori }}</x-table.td>
                            <x-table.td>{{ $obat->stokminimal }}</x-table.td>
                            <x-table.td>{{ floatval($obat->stok_sekarang_ap) }}</x-table.td>
                            <x-table.td>{{ floatval($obat->stok_sekarang_ifi) }}</x-table.td>
                            <x-table.td>{{ floatval($obat->saran_order) }}</x-table.td>
                            <x-table.td>{{ floatval($obat->ke_pasien_14_hari) }}</x-table.td>
                            <x-table.td>{{ $obat->nama_industri }}</x-table.td>
                            <x-table.td>{{ rp($obat->harga_beli) }}</x-table.td>
                            <x-table.td>{{ rp($obat->harga_beli_total) }}</x-table.td>
                            <x-table.td>{{ rp($obat->harga_beli_terakhir) }}</x-table.td>
                            <x-table.td>{{ $obat->diskon_terakhir }}</x-table.td>
                            <x-table.td>{{ $obat->supplier_terakhir }}</x-table.td>
                            <x-table.td>{{ floatval($obat->pemakaian_3_bulan) }}</x-table.td>
                            <x-table.td>{{ floatval($obat->pemakaian_1_bulan) }}</x-table.td>
                            <x-table.td>{{ floatval($obat->pemakaian_1_minggu) }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="16" />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->stokDaruratObat" />
        </x-slot>
    </x-card>
</div>
