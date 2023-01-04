<div>
    <x-flash />

    <x-card :filter="false">
        <x-slot name="header">
            <x-card.tools>
                <x-card.tools.toggle model="tampilkanSaranOrderNol" name="Tampilkan Saran Order Nol" />
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
                    <x-card.table.th>Jenis</x-card.table.th>
                    <x-card.table.th>Supplier</x-card.table.th>
                    <x-card.table.th>Min</x-card.table.th>
                    <x-card.table.th>Max</x-card.table.th>
                    <x-card.table.th>Saat ini</x-card.table.th>
                    <x-card.table.th>Saran order</x-card.table.th>
                    <x-card.table.th>Harga Per Unit</x-card.table.th>
                    <x-card.table.th>Total Harga</x-card.table.th>
                </x-slot>
                <x-slot name="body">
                    @foreach ($this->stokDaruratLogistik as $barang)
                        <x-card.table.tr>
                            <x-card.table.td>{{ $barang->kode_brng }}</x-card.table.td>
                            <x-card.table.td>{{ $barang->nama_brng }}</x-card.table.td>
                            <x-card.table.td>{{ $barang->satuan }}</x-card.table.td>
                            <x-card.table.td>{{ $barang->jenis }}</x-card.table.td>
                            <x-card.table.td>{{ $barang->nama_supplier }}</x-card.table.td>
                            <x-card.table.td>{{ $barang->stokmin }}</x-card.table.td>
                            <x-card.table.td>{{ $barang->stokmax }}</x-card.table.td>
                            <x-card.table.td>{{ $barang->stok }}</x-card.table.td>
                            <x-card.table.td>{{ $barang->saran_order }}</x-card.table.td>
                            <x-card.table.td>{{ rp($barang->harga) }}</x-card.table.td>
                            <x-card.table.td>{{ rp($barang->total_harga) }}</x-card.table.td>
                        </x-card.table.tr>
                    @endforeach
                </x-slot>
            </x-card.table>
        </x-slot>
        <x-slot name="footer">
            <x-card.paginator
                :count="$this->stokDaruratLogistik->count()"
                :total="$this->stokDaruratLogistik->total()"
            >
                <x-slot name="links">{{ $this->stokDaruratLogistik->links() }}</x-slot>
            </x-card.paginator>
        </x-slot>
    </x-card>
</div>
