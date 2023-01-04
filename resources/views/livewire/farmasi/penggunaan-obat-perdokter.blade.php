<div>
    <x-flash />

    <x-card>
        <x-slot name="body">
            <x-card.table>
                <x-slot name="columns">
                    <x-card.table.th>No. Resep</x-card.table.th>
                    <x-card.table.th>Tgl. Validasi</x-card.table.th>
                    <x-card.table.th>Jam</x-card.table.th>
                    <x-card.table.th>Nama Obat</x-card.table.th>
                    <x-card.table.th>Jumlah</x-card.table.th>
                    <x-card.table.th>Dokter Peresep</x-card.table.th>
                    <x-card.table.th>Asal</x-card.table.th>
                    <x-card.table.th>Asal Poli</x-card.table.th>
                </x-slot>
                <x-slot name="body">
                    @foreach ($this->obatPerDokter as $obat)
                        <x-card.table.tr>
                            <x-card.table.td>{{ $obat->no_resep }}</x-card.table.td>
                            <x-card.table.td>{{ $obat->tgl_perawatan }}</x-card.table.td>
                            <x-card.table.td>{{ $obat->jam }}</x-card.table.td>
                            <x-card.table.td>{{ $obat->nama_brng }}</x-card.table.td>
                            <x-card.table.td>{{ $obat->jml }}</x-card.table.td>
                            <x-card.table.td>{{ $obat->nm_dokter }}</x-card.table.td>
                            <x-card.table.td>{{ $obat->status }}</x-card.table.td>
                            <x-card.table.td>{{ $obat->nm_poli }}</x-card.table.td>
                        </x-card.table.tr>
                    @endforeach
                </x-slot>
            </x-card.table>
        </x-slot>
        <x-slot name="footer">
            <x-card.paginator :count="$this->obatPerDokter->count()" :total="$this->obatPerDokter->total()">
                <x-slot name="links">{{ $this->obatPerDokter->links() }}</x-slot>
            </x-card.paginator>
        </x-slot>
    </x-card>
</div>
