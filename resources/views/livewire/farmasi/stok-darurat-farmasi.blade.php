<div>
    <x-flash />

    <x-card>
        <x-slot name="header">
            <x-card.row-col>
                <x-filter.button-export-excel class="ml-auto" />
            </x-card.row-col>
            <x-card.row-col class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-card.row-col>
        </x-slot>
        <x-slot name="body" class="table-responsive">
            <x-table sortable :sortColumns="$sortColumns">
                <x-slot name="columns">
                    <x-table.th name="kode_brng" title="Kode" />
                    <x-table.th name="nama_brng" title="Nama" />
                    <x-table.th name="satuan_kecil" title="Satuan" />
                    <x-table.th name="kategori" title="Kategori" />
                    <x-table.th name="stokminimal" title="Stok minimal" />
                    <x-table.th name="stok_sekarang" title="Stok saat ini" />
                    <x-table.th name="saran_order" title="Saran order" />
                    <x-table.th name="nama_industri" title="Supplier" />
                    <x-table.th name="harga_beli" title="Harga Per Unit" />
                    <x-table.th name="harga_beli_total" title="Total Harga" />
                </x-slot>
                <x-slot name="body">
                    @foreach ($this->stokDaruratObat as $obat)
                        <x-table.tr>
                            <x-table.td>{{ $obat->kode_brng }}</x-table.td>
                            <x-table.td>{{ $obat->nama_brng }}</x-table.td>
                            <x-table.td>{{ $obat->satuan_kecil }}</x-table.td>
                            <x-table.td>{{ $obat->kategori }}</x-table.td>
                            <x-table.td>{{ $obat->stokminimal }}</x-table.td>
                            <x-table.td>{{ $obat->stok_sekarang }}</x-table.td>
                            <x-table.td>{{ $obat->saran_order }}</x-table.td>
                            <x-table.td>{{ $obat->nama_industri }}</x-table.td>
                            <x-table.td>{{ rp($obat->harga_beli) }}</x-table.td>
                            <x-table.td>{{ rp($obat->harga_beli_total) }}</x-table.td>
                        </x-table.tr>
                    @endforeach
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->stokDaruratObat" />
        </x-slot>
    </x-card>
</div>
