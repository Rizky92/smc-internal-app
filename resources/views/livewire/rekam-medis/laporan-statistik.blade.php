<div>
    <x-flash />

    <x-card use-default-filter use-loading wire:init="loadProperties">
        <x-slot name="body" class="table-responsive">
            <x-table style="width: 450rem">
                <x-slot name="columns">
                    <x-table.th style="width: 20ch" title="No. Rawat" />
                    <x-table.th style="width: 10ch" title="No RM" />
                    <x-table.th style="width: 50ch" title="Pasien" />
                    <x-table.th style="width: 20ch" title="NIK" />
                    <x-table.th style="width: 7ch" title="L / P" />
                    <x-table.th style="width: 12ch" title="Tgl. Lahir" />
                    <x-table.th style="width: 15ch" title="Umur" />
                    <x-table.th style="width: 10ch" title="Agama" />
                    <x-table.th style="width: 10ch" title="Suku" />
                    <x-table.th style="width: 17ch" title="Jenis Perawatan" />
                    <x-table.th style="width: 21ch" title="Pasien Lama / Baru" />
                    <x-table.th style="width: 12ch" title="Asal Poli" />
                    <x-table.th style="width: 50ch" title="Dokter Poli" />
                    <x-table.th style="width: 15ch" title="Status Ralan" />
                    <x-table.th style="width: 20ch" title="Tgl. Masuk" />
                    <x-table.th style="width: 20ch" title="Jam Masuk" />
                    <x-table.th style="width: 20ch" title="Tgl. Pulang" />
                    <x-table.th style="width: 20ch" title="Jam Pulang" />
                    <x-table.th style="width: 20ch" title="Diagnosa Masuk" />
                    <x-table.th style="width: 25ch" title="ICD Diagnosa" />
                    <x-table.th style="width: 75ch" title="Diagnosa" />
                    <x-table.th style="width: 25ch" title="ICD Tindakan Ralan" />
                    <x-table.th style="width: 75ch" title="Tindakan Ralan" />
                    <x-table.th style="width: 25ch" title="ICD Tindakan Ranap" />
                    <x-table.th style="width: 75ch" title="Tindakan Ranap" />
                    <x-table.th style="width: 15ch" title="Lama Operasi" />
                    <x-table.th style="width: 15ch" title="Rujukan Masuk" />
                    <x-table.th style="width: 50ch" title="DPJP Ranap" />
                    <x-table.th style="width: 8ch" title="Kelas" />
                    <x-table.th style="width: 30ch" title="Penjamin" />
                    <x-table.th style="width: 20ch" title="Status Bayar" />
                    <x-table.th style="width: 20ch" title="Status Pulang" />
                    <x-table.th style="width: 15ch" title="Rujukan Keluar" />
                    <x-table.th style="width: 50ch" title="Alamat" />
                    <x-table.th style="width: 16ch" title="No. HP" />
                    <x-table.th style="width: 17ch" title="Kunjungan ke" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataLaporanStatistik as $registrasi)
                        <x-table.tr>
                            <x-table.td>{{ optional($registrasi)->no_rawat }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->no_rm }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->pasien }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->nik }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->jk }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->tgl_lahir }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->umur }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->agama }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->suku }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->status_rawat }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->status_poli }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->asal_poli }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->dokter_poli }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->status_ralan }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->tgl_masuk }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->jam_masuk }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->tgl_keluar }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->jam_keluar }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->diagnosa_awal }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->kd_diagnosa }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->nm_diagnosa }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->kd_tindakan_ralan }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->nm_tindakan_ralan }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->kd_tindakan_ranap }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->nm_tindakan_ranap }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->lama_operasi }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->rujukan_masuk }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->dokter_pj }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->kelas }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->jenis_bayar }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->status_bayar }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->status_pulang_ranap }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->rujuk_keluar_rs }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->alamat }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->no_hp }}</x-table.td>
                            <x-table.td>{{ optional($registrasi)->kunjungan_ke }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="36" />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->dataLaporanStatistik" />
        </x-slot>
    </x-card>
</div>
