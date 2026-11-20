<div>
    <x-flash />

    <x-card use-default-filter use-loading>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="no_resep" title="No. Resep" />
                    <x-table.th name="no_sep" title="No. SEP" />
                    <x-table.th name="no_rm" title="No. RM" />
                    <x-table.th name="no_kartu" title="No. Kartu" />
                    <x-table.th name="nama_pasien" title="Nama Pasien" />
                    <x-table.th name="nm_poli" title="Poliklinik" />
                    <x-table.th name="kode_obat" title="Kode Obat" />
                    <x-table.th name="nama_obat" title="Nama Obat" />
                    <x-table.th-money name="harga_obat" title="Harga Obat" />
                    <x-table.th name="waktu_validasi" title="Tgl. Validasi" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->collection as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->no_resep }}</x-table.td>
                            <x-table.td>{{ $item->no_sep }}</x-table.td>
                            <x-table.td>{{ $item->no_rm }}</x-table.td>
                            <x-table.td>{{ $item->no_kartu }}</x-table.td>
                            <x-table.td>{{ $item->nama_pasien }}</x-table.td>
                            <x-table.td>{{ $item->nm_poli }}</x-table.td>
                            <x-table.td>{{ $item->kode_obat }}</x-table.td>
                            <x-table.td>{{ $item->nama_obat }}</x-table.td>
                            <x-table.td-money :value="$item->harga_obat" />
                            <x-table.td>{{ $item->waktu_validasi }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="11" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->collection" />
        </x-slot>
    </x-card>
</div>
