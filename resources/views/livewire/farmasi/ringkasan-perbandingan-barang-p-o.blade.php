<div>
    <x-flash />

    <x-card :filter="false">
        <x-slot name="header">
            <x-card.tools>
                <x-card.tools.date-range title="Tgl. SPM" />
                <x-card.tools.toggle class="ml-4" model="hanyaTampilkanBarangSelisih" name="Tampilkan Barang Selisih" />
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
                    <x-card.table.th>No. Pemesanan</x-card.table.th>
                    <x-card.table.th>Nama</x-card.table.th>
                    <x-card.table.th>Supplier Tujuan</x-card.table.th>
                    <x-card.table.th>Supplier yang Mendatangkan</x-card.table.th>
                    <x-card.table.th>Jumlah Dipesan</x-card.table.th>
                    <x-card.table.th>Jumlah yang Datang</x-card.table.th>
                    <x-card.table.th>Selisih</x-card.table.th>
                </x-slot>
                <x-slot name="body">
                    @foreach ($this->perbandinganOrderObatPO as $obat)
                        <x-card.table.tr>
                            <x-card.table.td>{{ $obat->no_pemesanan }}</x-card.table.td>
                            <x-card.table.td>{{ $obat->nama_brng }}</x-card.table.td>
                            <x-card.table.td>{{ $obat->suplier_pesan }}</x-card.table.td>
                            <x-card.table.td>{{ $obat->suplier_datang }}</x-card.table.td>
                            <x-card.table.td>{{ $obat->jumlah_pesan }}</x-card.table.td>
                            <x-card.table.td>{{ $obat->jumlah_datang }}</x-card.table.td>
                            <x-card.table.td>{{ $obat->selisih }}</x-card.table.td>
                        </x-card.table.tr>
                    @endforeach
                </x-slot>
            </x-card.table>
        </x-slot>
        <x-slot name="footer">
            <x-card.paginator :count="$this->perbandinganOrderObatPO->count()" :total="$this->perbandinganOrderObatPO->total()">
                <x-slot name="links">{{ $this->perbandinganOrderObatPO->links() }}</x-slot>
            </x-card.paginator>
        </x-slot>
    </x-card>
</div>
