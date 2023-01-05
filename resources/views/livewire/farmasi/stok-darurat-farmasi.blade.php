<div>
    <x-flash />

    <x-card>
        <x-slot name="header">
            <x-filter>
                <x-filter.button-export-excel class="ml-auto" />
            </x-filter>
            <x-filter class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-filter>
        </x-slot>
        <x-slot name="body">
            <x-table>
                <x-slot name="columns">
                    <x-table.th>Kode</x-table.th>
                    <x-table.th>Nama</x-table.th>
                    <x-table.th>Satuan</x-table.th>
                    <x-table.th>Kategori</x-table.th>
                    <x-table.th>Stok minimal</x-table.th>
                    <x-table.th>Stok saat ini</x-table.th>
                    <x-table.th>Saran order</x-table.th>
                    <x-table.th>Supplier</x-table.th>
                    <x-table.th>Harga Per Unit</x-table.th>
                    <x-table.th>Total Harga</x-table.th>
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
            <x-paginator :count="$this->stokDaruratObat->count()" :total="$this->stokDaruratObat->total()">
                {{ $this->stokDaruratObat->links() }}
            </x-paginator>
        </x-slot>
    </x-card>
</div>
