<div>
    <x-flash />

    <x-card>
        <x-slot name="header">
            <x-card.row>
                <x-filter.toggle model="tampilkanSaranOrderNol" title="Tampilkan Saran Order Nol" />
                <x-filter.button-export-excel class="ml-auto" />
            </x-card.row>
            <x-card.row class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-card.row>
        </x-slot>
        <x-slot name="body" class="table-responsive p-0">
            <x-table>
                <x-slot name="columns">
                    <x-table.th>Kode</x-table.th>
                    <x-table.th>Nama</x-table.th>
                    <x-table.th>Satuan</x-table.th>
                    <x-table.th>Jenis</x-table.th>
                    <x-table.th>Supplier</x-table.th>
                    <x-table.th>Min</x-table.th>
                    <x-table.th>Max</x-table.th>
                    <x-table.th>Saat ini</x-table.th>
                    <x-table.th>Saran order</x-table.th>
                    <x-table.th>Harga Per Unit</x-table.th>
                    <x-table.th>Total Harga</x-table.th>
                </x-slot>
                <x-slot name="body">
                    @foreach ($this->stokDaruratLogistik as $barang)
                        <x-table.tr>
                            <x-table.td>{{ $barang->kode_brng }}</x-table.td>
                            <x-table.td>{{ $barang->nama_brng }}</x-table.td>
                            <x-table.td>{{ $barang->satuan }}</x-table.td>
                            <x-table.td>{{ $barang->jenis }}</x-table.td>
                            <x-table.td>{{ $barang->nama_supplier }}</x-table.td>
                            <x-table.td>{{ $barang->stokmin }}</x-table.td>
                            <x-table.td>{{ $barang->stokmax }}</x-table.td>
                            <x-table.td>{{ $barang->stok }}</x-table.td>
                            <x-table.td>{{ $barang->saran_order }}</x-table.td>
                            <x-table.td>{{ rp($barang->harga) }}</x-table.td>
                            <x-table.td>{{ rp($barang->total_harga) }}</x-table.td>
                        </x-table.tr>
                    @endforeach
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :count="$this->stokDaruratLogistik->count()" :total="$this->stokDaruratLogistik->total()">
                {{ $this->stokDaruratLogistik->links() }}
            </x-paginator>
        </x-slot>
    </x-card>
</div>
