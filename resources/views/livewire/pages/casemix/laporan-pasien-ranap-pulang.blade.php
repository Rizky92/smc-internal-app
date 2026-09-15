<div wire:init="loadProperties">
    <x-flash />

    <x-card use-loading>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.label class="pr-3">Tahun :</x-filter.label>
                <x-filter.select model="tahun" :options="$this->dataTahun" />
                <x-filter.label class="pr-3 ml-3">Bulan :</x-filter.label>
                <x-filter.select model="bulan" :options="$this->dataBulan" />
                <x-filter.button-export-excel class="ml-auto" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap style="width: 350rem">
                <x-slot name="columns">
                    <x-table.th name="no_sep" title="No. SEP" />
                    <x-table.th name="no_rawat" title="No. Rawat" />
                    <x-table.th name="tgl_registrasi" title="Tgl. Registrasi" />
                    <x-table.th name="no_rm" title="No. RM" />
                    <x-table.th name="nama_pasien" title="Nama Pasien" />
                    <x-table.th name="jenis_pelayanan" title="Jenis Pelayanan SEP" />
                    <x-table.th name="status_lanjut" title="Status Lanjut" />
                    <x-table.th name="kd_poli" title="Kode Poli Asal" />
                    <x-table.th name="nm_poli" title="Nama Poli Asal" />
                    <x-table.th name="kd_pj" title="Kode Jenis Bayar" />
                    <x-table.th name="jenis_bayar" title="Nama Jenis Bayar" />
                    <x-table.th name="kls_rawat" title="Kelas Rawat" />
                    <x-table.th name="tglsep" title="Tgl. SEP" />
                    <x-table.th name="tglpulang" title="Tgl. Pulang SEP" />
                    <x-table.th name="tgl_masuk_ranap" title="Tgl. Masuk Ranap" />
                    <x-table.th name="tgl_keluar_ranap" title="Tgl. Keluar Ranap" />
                    <x-table.th name="kamar_terakhir" title="Kamar Terakhir" />
                    <x-table.th name="tgl_close_billing" title="Tgl. Close Billing" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->collection as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->no_sep }}</x-table.td>
                            <x-table.td>{{ $item->no_rawat }}</x-table.td>
                            <x-table.td>{{ $item->tgl_registrasi }}</x-table.td>
                            <x-table.td>{{ $item->no_rm }}</x-table.td>
                            <x-table.td>{{ $item->nama_pasien }}</x-table.td>
                            <x-table.td>{{ $item->jenis_pelayanan }}</x-table.td>
                            <x-table.td>{{ $item->status_lanjut }}</x-table.td>
                            <x-table.td>{{ $item->kd_poli }}</x-table.td>
                            <x-table.td>{{ $item->nm_poli }}</x-table.td>
                            <x-table.td>{{ $item->kd_pj }}</x-table.td>
                            <x-table.td>{{ $item->jenis_bayar }}</x-table.td>
                            <x-table.td>{{ $item->kls_rawat }}</x-table.td>
                            <x-table.td>{{ $item->tglsep }}</x-table.td>
                            <x-table.td>{{ $item->tglpulang }}</x-table.td>
                            <x-table.td>{{ $item->tgl_masuk_ranap }}</x-table.td>
                            <x-table.td>{{ $item->tgl_keluar_ranap }}</x-table.td>
                            <x-table.td>{{ $item->kamar_terakhir }}</x-table.td>
                            <x-table.td>{{ $item->tgl_close_billing }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="18" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->collection" />
        </x-slot>
    </x-card>
</div>
