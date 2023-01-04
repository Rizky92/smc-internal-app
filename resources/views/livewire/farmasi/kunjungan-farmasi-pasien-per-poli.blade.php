<div>
    <x-flash />

    <x-card>
        <x-slot name="body">
            <x-card.table>
                <x-slot name="columns">
                    <x-card.table.th>No. Rawat</x-card.table.th>
                    <x-card.table.th>No. Resep</x-card.table.th>
                    <x-card.table.th>Pasien</x-card.table.th>
                    <x-card.table.th>Umur</x-card.table.th>
                    <x-card.table.th>Tgl. Validasi</x-card.table.th>
                    <x-card.table.th>Jam</x-card.table.th>
                    <x-card.table.th>Dokter Peresep</x-card.table.th>
                    <x-card.table.th>Dokter Poli</x-card.table.th>
                    <x-card.table.th>Jenis Perawatan</x-card.table.th>
                    <x-card.table.th>Asal Poli</x-card.table.th>
                </x-slot>
                <x-slot name="body">
                    @foreach ($this->dataKunjunganResepPasien as $pasien)
                        <x-card.table.tr>
                            <x-card.table.td>{{ $pasien->no_rawat }}</x-card.table.td>
                            <x-card.table.td>{{ $pasien->no_resep }}</x-card.table.td>
                            <x-card.table.td>{{ $pasien->nm_pasien }}</x-card.table.td>
                            <x-card.table.td>{{ $pasien->umur }}</x-card.table.td>
                            <x-card.table.td>{{ $pasien->tgl_perawatan }}</x-card.table.td>
                            <x-card.table.td>{{ $pasien->jam }}</x-card.table.td>
                            <x-card.table.td>{{ $pasien->nm_dokter_peresep }}</x-card.table.td>
                            <x-card.table.td>{{ $pasien->nm_dokter_poli }}</x-card.table.td>
                            <x-card.table.td>{{ $pasien->status_lanjut }}</x-card.table.td>
                            <x-card.table.td>{{ $pasien->nm_poli }}</x-card.table.td>
                        </x-card.table.tr>
                    @endforeach
                </x-slot>
            </x-card.table>
        </x-slot>
        <x-slot name="footer">
            <x-card.paginator :count="$this->dataKunjunganResepPasien->count()" :total="$this->dataKunjunganResepPasien->total()">
                <x-slot name="links">{{ $this->dataKunjunganResepPasien->links() }}</x-slot>
            </x-card.paginator>
        </x-slot>
    </x-card>
</div>
