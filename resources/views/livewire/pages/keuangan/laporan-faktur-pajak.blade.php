<div wire:init="loadProperties">
    <x-flash />

    <x-card use-default-filter use-loading>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" style="width: 150rem" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="no_rawat" title="No. Rawat" />
                    <x-table.th name="tgl_registrasi" title="Tgl. Registrasi" />
                    <x-table.th name="tgl_bayar" title="Tgl. Pelunasan" />
                    <x-table.th name="jam_bayar" title="Jam Pelunasan" />
                    <x-table.th name="jenis_id" title="Jenis ID" />
                    <x-table.th name="negara" title="Negara" />
                    <x-table.th name="npwp" title="No. NPWP" />
                    <x-table.th name="no_rkm_medis" title="No. RM" />
                    <x-table.th name="no_ktp" title="NIK" />
                    <x-table.th name="nm_pasien" title="Nama" />
                    <x-table.th name="alamat" title="Alamat" />
                    <x-table.th name="email" title="Email" />
                    <x-table.th name="no_tlp" title="No. Telp" />
                    <x-table.th name="status_lanjut" title="Status Registrasi" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataLaporanFakturPajak as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->no_rawat }}</x-table.td>
                            <x-table.td>{{ $item->tgl_registrasi }}</x-table.td>
                            <x-table.td>{{ $item->tgl_bayar }}</x-table.td>
                            <x-table.td>{{ $item->jam_bayar }}</x-table.td>
                            <x-table.td>{{ $item->jenis_id }}</x-table.td>
                            <x-table.td>{{ $item->negara }}</x-table.td>
                            <x-table.td>{{ $item->npwp }}</x-table.td>
                            <x-table.td>{{ $item->no_rkm_medis }}</x-table.td>
                            <x-table.td>{{ $item->no_ktp }}</x-table.td>
                            <x-table.td>{{ $item->nm_pasien }}</x-table.td>
                            <x-table.td>{{ $item->alamat }}</x-table.td>
                            <x-table.td>{{ $item->email }}</x-table.td>
                            <x-table.td>{{ $item->no_tlp }}</x-table.td>
                            <x-table.td>{{ $item->status_lanjut }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="14" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->dataLaporanFakturPajak" />
        </x-slot>
    </x-card>
</div>
