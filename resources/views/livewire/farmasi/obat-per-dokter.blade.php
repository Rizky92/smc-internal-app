<div>
    <x-flash />

    <x-card use-default-filter>
        <x-slot name="body" class="table-responsive">
            <x-table sortable :sortColumns="$sortColumns">
                <x-slot name="columns">
                    <x-table.th name="no_resep" title="No. Resep" />
                    <x-table.th name="tgl_perawatan" title="Tgl. Validasi" />
                    <x-table.th name="jam" title="Jam" />
                    <x-table.th name="nama_brng" title="Nama Obat" />
                    <x-table.th name="jml" title="Jumlah" />
                    <x-table.th name="nm_dokter" title="Dokter Peresep" />
                    <x-table.th name="status" title="Asal" />
                    <x-table.th name="nm_poli" title="Asal Poli" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->obatPerDokter as $obat)
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
                    @empty
                        <x-table.tr-empty :colspan="8" />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->obatPerDokter" />
        </x-slot>
    </x-card>
</div>
