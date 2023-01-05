<div>
    <x-flash />

    <x-card use-default-filter>
        <x-slot name="body">
            <x-table>
                <x-slot name="columns">
                    <x-table.th>No. Rawat</x-table.th>
                    <x-table.th>No. Resep</x-table.th>
                    <x-table.th>Pasien</x-table.th>
                    <x-table.th>Umur</x-table.th>
                    <x-table.th>Tgl. Validasi</x-table.th>
                    <x-table.th>Jam</x-table.th>
                    <x-table.th>Dokter Peresep</x-table.th>
                    <x-table.th>Dokter Poli</x-table.th>
                    <x-table.th>Jenis Perawatan</x-table.th>
                    <x-table.th>Asal Poli</x-table.th>
                </x-slot>
                <x-slot name="body">
                    @foreach ($this->dataKunjunganResepPasien as $pasien)
                        <x-table.tr>
                            <x-table.td>{{ $pasien->no_rawat }}</x-table.td>
                            <x-table.td>{{ $pasien->no_resep }}</x-table.td>
                            <x-table.td>{{ $pasien->nm_pasien }}</x-table.td>
                            <x-table.td>{{ $pasien->umur }}</x-table.td>
                            <x-table.td>{{ $pasien->tgl_perawatan }}</x-table.td>
                            <x-table.td>{{ $pasien->jam }}</x-table.td>
                            <x-table.td>{{ $pasien->nm_dokter_peresep }}</x-table.td>
                            <x-table.td>{{ $pasien->nm_dokter_poli }}</x-table.td>
                            <x-table.td>{{ $pasien->status_lanjut }}</x-table.td>
                            <x-table.td>{{ $pasien->nm_poli }}</x-table.td>
                        </x-table.tr>
                    @endforeach
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :count="$this->dataKunjunganResepPasien->count()" :total="$this->dataKunjunganResepPasien->total()">
                {{ $this->dataKunjunganResepPasien->links() }}
            </x-paginator>
        </x-slot>
    </x-card>
</div>
