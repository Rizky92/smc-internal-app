<div>
    <x-flash />

    <x-card>
        <x-slot name="header">
            <x-card.row>
                <x-filter.range-date title="Tgl. SPM" />
                <x-filter.toggle class="ml-4" model="hanyaTampilkanBarangSelisih" name="Tampilkan Barang Selisih" />
                <x-filter.button-export-excel class="ml-auto" />
            </x-card.row>
            <x-card.row class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-card.row>
        </x-slot>
        <x-slot name="body">
            <x-table>
                <x-slot name="columns">
                    <x-table.th>No. Pemesanan</x-table.th>
                    <x-table.th>Nama</x-table.th>
                    <x-table.th>Supplier Tujuan</x-table.th>
                    <x-table.th>Supplier yang Mendatangkan</x-table.th>
                    <x-table.th>Jumlah Dipesan</x-table.th>
                    <x-table.th>Jumlah yang Datang</x-table.th>
                    <x-table.th>Selisih</x-table.th>
                </x-slot>
                <x-slot name="body">
                    @foreach ($this->perbandinganOrderObatPO as $obat)
                        <x-table.tr>
                            <x-table.td>{{ $obat->no_pemesanan }}</x-table.td>
                            <x-table.td>{{ $obat->nama_brng }}</x-table.td>
                            <x-table.td>{{ $obat->suplier_pesan }}</x-table.td>
                            <x-table.td>{{ $obat->suplier_datang }}</x-table.td>
                            <x-table.td>{{ $obat->jumlah_pesan }}</x-table.td>
                            <x-table.td>{{ $obat->jumlah_datang }}</x-table.td>
                            <x-table.td>{{ $obat->selisih }}</x-table.td>
                        </x-table.tr>
                    @endforeach
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :count="$this->perbandinganOrderObatPO->count()" :total="$this->perbandinganOrderObatPO->total()">
                {{ $this->perbandinganOrderObatPO->links() }}
            </x-paginator>
        </x-slot>
    </x-card>
</div>
