<div wire:init="loadProperties">
    <x-flash />

    <x-card use-default-filter use-loading>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" style="width: 185rem" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th style="width: 20ch" name="no_rawat" title="No. Rawat" />
                    <x-table.th style="width: 12ch" name="no_rkm_medis" title="No. RM" />
                    <x-table.th style="width: 42ch" name="nm_pasien" title="Pasien" />
                    <x-table.th style="width: 20ch" name="png_jawab" title="Jenis Bayar" />
                    <x-table.th style="width: 15ch" name="nama_petugas" title="Petugas" />
                    <x-table.th style="width: 13ch" name="tgl_periksa" title="Tgl. Periksa" />
                    <x-table.th style="width: 10ch" name="jam" title="Jam" />
                    <x-table.th style="width: 10ch" name="dokter_perujuk" title="Perujuk" />
                    <x-table.th style="width: 17ch" name="kd_jenis_prw" title="Kode Tindakan" />
                    <x-table.th style="width: 30ch" name="nm_perawatan" title="Nama Tindakan" />
                    <x-table.th style="width: 13ch" name="biaya" title="Biaya" />
                    <x-table.th style="width: 15ch" name="status_bayar" title="Status Bayar" />
                    <x-table.th style="width: 18ch" name="status" title="Jenis Perawatan" />
                    <x-table.th style="width: 15ch" name="kd_dokter" title="Kode Dokter" />
                    <x-table.th style="width: 30ch" name="nm_dokter" title="Nama Dokter Pemeriksa" />
                    <x-table.th style="width: 80ch" name="hasil_pemeriksaan" title="Hasil Pemeriksaan" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataLaporanTindakanRadiologi as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->no_rawat }}</x-table.td>
                            <x-table.td>
                                {{ $item->no_rkm_medis }}
                            </x-table.td>
                            <x-table.td>{{ $item->nm_pasien }}</x-table.td>
                            <x-table.td>{{ $item->png_jawab }}</x-table.td>
                            <x-table.td>
                                {{ $item->nama_petugas }}
                            </x-table.td>
                            <x-table.td>
                                {{ $item->tgl_periksa }}
                            </x-table.td>
                            <x-table.td>{{ $item->jam }}</x-table.td>
                            <x-table.td>
                                {{ $item->dokter_perujuk }}
                            </x-table.td>
                            <x-table.td>
                                {{ $item->kd_jenis_prw }}
                            </x-table.td>
                            <x-table.td>
                                {{ $item->nm_perawatan }}
                            </x-table.td>
                            <x-table.td>{{ rp($item->biaya) }}</x-table.td>
                            <x-table.td>
                                {{ $item->status_bayar }}
                            </x-table.td>
                            <x-table.td>{{ $item->status }}</x-table.td>
                            <x-table.td>{{ $item->kd_dokter }}</x-table.td>
                            <x-table.td>{{ $item->nm_dokter }}</x-table.td>
                            <x-table.td>
                                {{ $item->hasil_pemeriksaan }}
                            </x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="16" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->dataLaporanTindakanRadiologi" />
        </x-slot>
    </x-card>
</div>
