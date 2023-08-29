<div>
    <x-flash />

    <x-card use-default-filter use-loading>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="no_rawat" title="No. Rawat" />
                    <x-table.th name="tgl_registrasi" title="Tgl. Registrasi" />
                    <x-table.th name="no_rkm_medis" title="No. RM" />
                    <x-table.th name="nm_pasien" title="Pasien" />
                    <x-table.th name="no_ktp" title="NIK" />
                    <x-table.th name="nama_brng" title="Obat Diberikan" />
                    <x-table.th name="total" align="right" title="Jumlah" />
                    <x-table.th name="nm_bangsal" title="Farmasi" />
                    <x-table.th name="status_lanjut" title="Status" />
                    <x-table.th name="png_jawab" title="Penjamin" />
                    <x-table.th name="no_tlp" title="No. Telp" />
                    <x-table.th name="alamat" title="Alamat" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataLaporanPemakaianObatTB as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->no_rawat }}</x-table.td>
                            <x-table.td>{{ $item->tgl_registrasi }}</x-table.td>
                            <x-table.td>{{ $item->no_rkm_medis }}</x-table.td>
                            <x-table.td>{{ $item->nm_pasien }}</x-table.td>
                            <x-table.td>{{ $item->nama_brng }}</x-table.td>
                            <x-table.td class="text-right">{{ round($item->total, 2) }}</x-table.td>
                            <x-table.td>{{ $item->nm_bangsal }}</x-table.td>
                            <x-table.td>{{ $item->status_lanjut }}</x-table.td>
                            <x-table.td>{{ $item->png_jawab }}</x-table.td>
                            <x-table.td>{{ $item->no_tlp }}</x-table.td>
                            <x-table.td>{{ $item->alamat }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="11" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->dataLaporanPemakaianObatTB" />
        </x-slot>
    </x-card>
</div>
