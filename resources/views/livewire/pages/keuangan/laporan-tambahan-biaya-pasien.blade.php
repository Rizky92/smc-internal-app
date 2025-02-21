<div wire:init="loadProperties">
    <x-flash />

    <x-card use-default-filter use-loading>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" style="width: 150rem" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="tgl_registrasi" title="Tgl." style="width: 15ch" />
                    <x-table.th name="jam_reg" title="Jam" style="width: 10ch" />
                    <x-table.th name="nm_pasien" title="Nama Pasien" style="width: 40ch" />
                    <x-table.th name="no_rkm_medis" title="No. RM" style="width: 10ch" />
                    <x-table.th name="no_rawat" title="No. Registrasi" style="width: 20ch" />
                    <x-table.th name="nama_biaya" title="Nama Biaya" style="width: 50ch" />
                    <x-table.th name="besar_biaya" title="Nominal" style="width: 20ch" />
                    <x-table.th name="png_jawab" title="Jenis Bayar" style="width: 40ch" />
                    <x-table.th name="dokter_ralan" title="Dokter Ralan" style="width: 40ch" />
                    <x-table.th name="dokter_ranap" title="Dokter Ranap" style="width: 40ch" />
                    <x-table.th name="nm_poli" title="Asal Poli" style="width: 20ch" />
                    <x-table.th name="status_lanjut" title="Jenis Perawatan" style="width: 20ch" />
                    <x-table.th name="status_bayar" title="Status Pembayaran" style="width: 25ch" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataTambahanBiayaPasien as $item)
                        <x-table.tr>
                            <x-table.td>
                                {{ $item->tgl_registrasi }}
                            </x-table.td>
                            <x-table.td>{{ $item->jam_reg }}</x-table.td>
                            <x-table.td>{{ $item->nm_pasien }}</x-table.td>
                            <x-table.td>
                                {{ $item->no_rkm_medis }}
                            </x-table.td>
                            <x-table.td>{{ $item->no_rawat }}</x-table.td>
                            <x-table.td>
                                {{ $item->nama_biaya }}
                            </x-table.td>
                            <x-table.td>
                                {{ rp($item->besar_biaya) }}
                            </x-table.td>
                            <x-table.td>{{ $item->png_jawab }}</x-table.td>
                            <x-table.td>
                                {{ $item->dokter_ralan }}
                            </x-table.td>
                            <x-table.td>
                                {{ $item->dokter_ranap }}
                            </x-table.td>
                            <x-table.td>{{ $item->nm_poli }}</x-table.td>
                            <x-table.td>
                                {{ $item->status_lanjut }}
                            </x-table.td>
                            <x-table.td>
                                {{ $item->status_bayar }}
                            </x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="13" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->dataTambahanBiayaPasien" />
        </x-slot>
    </x-card>
</div>
