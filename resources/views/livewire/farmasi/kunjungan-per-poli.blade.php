<div>
    <x-flash />

    <x-card use-default-filter>
        <x-slot name="body" class="table-responsive">
            <x-table sortable :sortColumns="$sortColumns">
                <x-slot name="columns">
                    <x-table.th name="no_rawat" title="No. Rawat" />
                    <x-table.th name="no_resep" title="No. Resep" />
                    <x-table.th name="nm_pasien" title="Pasien" />
                    <x-table.th name="umur" title="Umur" />
                    <x-table.th name="tgl_perawatan" title="Tgl. Validasi" />
                    <x-table.th name="jam" title="Jam" />
                    <x-table.th name="nm_dokter_peresep" title="Dokter Peresep" />
                    <x-table.th name="nm_dokter_poli" title="Dokter Poli" />
                    <x-table.th name="status_lanjut" title="Jenis Perawatan" />
                    <x-table.th name="nm_poli" title="Asal Poli" />
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
            <x-paginator :data="$this->dataKunjunganResepPasien" />
        </x-slot>
    </x-card>
</div>
