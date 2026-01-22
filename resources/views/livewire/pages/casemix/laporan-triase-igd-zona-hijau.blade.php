<div>
    <x-flash />

    <x-card use-default-filter use-loading>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="no_rawat" title="No. Rawat" />
                    <x-table.th name="no_sep" title="No. SEP" />
                    <x-table.th name="no_rkm_medis" title="No. RM" />
                    <x-table.th name="nm_pasien" title="Nama Pasien" />
                    <x-table.th name="png_jawab" title="Jenis Bayar" />
                    <x-table.th name="tgl_kunjungan" title="Tgl. Kunjungan" />
                    <x-table.th name="cara_masuk" title="Cara Masuk" />
                    <x-table.th name="stts" title="Status" />
                    <x-table.th name="status_lanjut" title="Jenis Rawat" />
                    <x-table.th name="alasan_kedatangan" title="Alasan Kedatangan" />
                    <x-table.th name="macam_kasus" title="Macam Kasus" />
                    <x-table.th name="plan" title="Zona" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->collection as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->no_rawat }}</x-table.td>
                            <x-table.td>{{ $item->no_sep }}</x-table.td>
                            <x-table.td>{{ $item->no_rkm_medis }}</x-table.td>
                            <x-table.td>{{ $item->nm_pasien }}</x-table.td>
                            <x-table.td>{{ $item->png_jawab }}</x-table.td>
                            <x-table.td>{{ $item->tgl_kunjungan }}</x-table.td>
                            <x-table.td>{{ $item->cara_masuk }}</x-table.td>
                            <x-table.td>{{ $item->stts }}</x-table.td>
                            <x-table.td>{{ $item->status_lanjut }}</x-table.td>
                            <x-table.td>{{ $item->alasan_kedatangan }}</x-table.td>
                            <x-table.td>{{ $item->macam_kasus }}</x-table.td>
                            <x-table.td>{{ $item->plan }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="12" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->collection" />
        </x-slot>
    </x-card>
</div>
