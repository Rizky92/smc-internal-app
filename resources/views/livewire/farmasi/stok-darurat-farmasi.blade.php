<div>
    <x-flash />

    <x-card :filter="false">
        <x-slot name="header">
            <x-card.tools>
                <x-card.tools.export-to-excel class="ml-auto" />
            </x-card.tools>
            <x-card.tools class="mt-2">
                <x-card.tools.perpage />
                <x-card.tools.reset-filters class="ml-auto" />
                <x-card.tools.search class="ml-2" />
            </x-card.tools>
        </x-slot>
        <x-slot name="body">
            <x-card.table>
                <x-slot name="columns">
                    <x-card.table.th>Kode</x-card.table.th>
                    <x-card.table.th>Nama</x-card.table.th>
                    <x-card.table.th>Satuan</x-card.table.th>
                    <x-card.table.th>Kategori</x-card.table.th>
                    <x-card.table.th>Stok minimal</x-card.table.th>
                    <x-card.table.th>Stok saat ini</x-card.table.th>
                    <x-card.table.th>Saran order</x-card.table.th>
                    <x-card.table.th>Supplier</x-card.table.th>
                    <x-card.table.th>Harga Per Unit</x-card.table.th>
                    <x-card.table.th>Total Harga</x-card.table.th>
                </x-slot>
                <x-slot name="body">
                    @foreach ($this->stokDaruratObat as $obat)
                        <x-card.table.tr>
                            <x-card.table.td>{{ $obat->kode_brng }}</x-card.table.td>
                            <x-card.table.td>{{ $obat->nama_brng }}</x-card.table.td>
                            <x-card.table.td>{{ $obat->satuan_kecil }}</x-card.table.td>
                            <x-card.table.td>{{ $obat->kategori }}</x-card.table.td>
                            <x-card.table.td>{{ $obat->stokminimal }}</x-card.table.td>
                            <x-card.table.td>{{ $obat->stok_sekarang }}</x-card.table.td>
                            <x-card.table.td>{{ $obat->saran_order }}</x-card.table.td>
                            <x-card.table.td>{{ $obat->nama_industri }}</x-card.table.td>
                            <x-card.table.td>{{ rp($obat->harga_beli) }}</x-card.table.td>
                            <x-card.table.td>{{ rp($obat->harga_beli_total) }}</x-card.table.td>
                        </x-card.table.tr>
                    @endforeach
                </x-slot>
            </x-card.table>
        </x-slot>
        <x-slot name="footer">
            <x-card.paginator :count="$this->stokDaruratObat->count()" :total="$this->stokDaruratObat->total()">
                <x-slot name="links">{{ $this->stokDaruratObat->links() }}</x-slot>
            </x-card.paginator>
        </x-slot>
    </x-card>
</div>
