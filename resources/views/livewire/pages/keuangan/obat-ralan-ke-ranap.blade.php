<div>
    <x-flash />

    <x-card use-default-filter>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="no_rawat" title="No. Rawat" />
                    <x-table.th name="no_rkm_medis" title="No. RM" />
                    <x-table.th name="nm_pasien" title="Nama Pasien" />
                    <x-table.th name="png_jawab" title="Jenis Bayar" />
                    <x-table.th name="tgl_perawatan" title="Tgl. Pemberian Obat" />
                    <x-table.th name="jam" title="Jam" />
                    <x-table.th name="kode_brng" title="Kode Barang" />
                    <x-table.th name="nama_brng" title="Nama Barang" />
                    <x-table.th-money name="biaya_obat" title="Harga" />
                    <x-table.th name="jml" title="Jumlah" />
                    <x-table.th-money name="total" title="Total" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->collection as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->no_rawat }}</x-table.td>
                            <x-table.td>{{ $item->no_rkm_medis }}</x-table.td>
                            <x-table.td>{{ $item->nm_pasien }}</x-table.td>
                            <x-table.td>{{ $item->png_jawab }}</x-table.td>
                            <x-table.td>{{ $item->tgl_perawatan }}</x-table.td>
                            <x-table.td>{{ $item->jam }}</x-table.td>
                            <x-table.td>{{ $item->kode_brng }}</x-table.td>
                            <x-table.td>{{ $item->nama_brng }}</x-table.td>
                            <x-table.td-money :value="$item->biaya_obat" />
                            <x-table.td>{{ $item->jml }}</x-table.td>
                            <x-table.td-money :value="$item->total" />
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="13" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->collection" />
        </x-slot>
    </x-card>
</div>
