<div>
    <x-flash />

    <x-card use-default-filter>
        <x-slot name="body">
            <x-table>
                <x-slot name="columns">
                    <x-table.th>No. Resep</x-table.th>
                    <x-table.th>Tgl. Validasi</x-table.th>
                    <x-table.th>Jam</x-table.th>
                    <x-table.th>Nama Obat</x-table.th>
                    <x-table.th>Jumlah</x-table.th>
                    <x-table.th>Dokter Peresep</x-table.th>
                    <x-table.th>Asal</x-table.th>
                    <x-table.th>Asal Poli</x-table.th>
                </x-slot>
                <x-slot name="body">
                    @foreach ($this->obatPerDokter as $obat)
                        <x-table.tr>
                            <x-table.td>{{ $obat->no_resep }}</x-table.td>
                            <x-table.td>{{ $obat->tgl_perawatan }}</x-table.td>
                            <x-table.td>{{ $obat->jam }}</x-table.td>
                            <x-table.td>{{ $obat->nama_brng }}</x-table.td>
                            <x-table.td>{{ $obat->jml }}</x-table.td>
                            <x-table.td>{{ $obat->nm_dokter }}</x-table.td>
                            <x-table.td>{{ $obat->status }}</x-table.td>
                            <x-table.td>{{ $obat->nm_poli }}</x-table.td>
                        </x-table.tr>
                    @endforeach
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :count="$this->obatPerDokter->count()" :total="$this->obatPerDokter->total()">
                {{ $this->obatPerDokter->links() }}
            </x-paginator>
        </x-slot>
    </x-card>
</div>
