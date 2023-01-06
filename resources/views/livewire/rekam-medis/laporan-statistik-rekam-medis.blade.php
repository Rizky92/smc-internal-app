<div>
    <x-flash />

    <x-card use-default-filter use-loading>
        <x-slot name="body" class="table-responsive p-0">
            <x-table style="width: 450rem">
                <x-slot name="columns">
                    <x-table.th>No. Rawat</x-table.th>
                    <x-table.th>No RM</x-table.th>
                    <x-table.th>Pasien</x-table.th>
                    <x-table.th>NIK</x-table.th>
                    <x-table.th>L / P</x-table.th>
                    <x-table.th>Tgl. Lahir</x-table.th>
                    <x-table.th>Umur</x-table.th>
                    <x-table.th>Agama</x-table.th>
                    <x-table.th>Suku</x-table.th>
                    <x-table.th>Jenis Perawatan</x-table.th>
                    <x-table.th>Pasien Lama / Baru</x-table.th>
                    <x-table.th>Asal Poli</x-table.th>
                    <x-table.th>Dokter Poli</x-table.th>
                    <x-table.th>Status Ralan</x-table.th>
                    <x-table.th>Tgl. Masuk</x-table.th>
                    <x-table.th>Jam Masuk</x-table.th>
                    <x-table.th>Tgl. Pulang</x-table.th>
                    <x-table.th>Jam Pulang</x-table.th>
                    <x-table.th>Diagnosa Masuk</x-table.th>
                    <x-table.th style="width: 30ch">ICD Diagnosa</x-table.th>
                    <x-table.th style="width: 80ch">Diagnosa</x-table.th>
                    <x-table.th style="width: 30ch">ICD Tindakan Ralan</x-table.th>
                    <x-table.th style="width: 80ch">Tindakan Ralan</x-table.th>
                    <x-table.th style="width: 30ch">ICD Tindakan Ranap</x-table.th>
                    <x-table.th style="width: 80ch">Tindakan Ranap</x-table.th>
                    <x-table.th>Lama Operasi</x-table.th>
                    <x-table.th>Rujukan Masuk</x-table.th>
                    <x-table.th>DPJP Ranap</x-table.th>
                    <x-table.th>Kelas</x-table.th>
                    <x-table.th>Penjamin</x-table.th>
                    <x-table.th>Status Bayar</x-table.th>
                    <x-table.th>Status Pulang</x-table.th>
                    <x-table.th>Rujukan Keluar</x-table.th>
                    <x-table.th>No. HP</x-table.th>
                    <x-table.th>Alamat</x-table.th>
                    <x-table.th>Kunjungan ke</x-table.th>
                </x-slot>
                <x-slot name="body">
                    @foreach ($this->dataLaporanStatistik as $registrasi)
                        <x-table.tr>
                            <x-table.td>{{ $registrasi->no_rawat }}</x-table.td>
                            <x-table.td>{{ $registrasi->no_rm }}</x-table.td>
                            <x-table.td>{{ $registrasi->pasien }}</x-table.td>
                            <x-table.td>{{ $registrasi->nik }}</x-table.td>
                            <x-table.td>{{ $registrasi->jk }}</x-table.td>
                            <x-table.td>{{ $registrasi->tgl_lahir }}</x-table.td>
                            <x-table.td>{{ $registrasi->umur }}</x-table.td>
                            <x-table.td>{{ $registrasi->agama }}</x-table.td>
                            <x-table.td>{{ $registrasi->suku }}</x-table.td>
                            <x-table.td>{{ $registrasi->status_rawat }}</x-table.td>
                            <x-table.td>{{ $registrasi->status_poli }}</x-table.td>
                            <x-table.td>{{ $registrasi->asal_poli }}</x-table.td>
                            <x-table.td>{{ $registrasi->dokter_poli }}</x-table.td>
                            <x-table.td>{{ $registrasi->status_ralan }}</x-table.td>
                            <x-table.td>{{ $registrasi->tgl_masuk }}</x-table.td>
                            <x-table.td>{{ $registrasi->jam_masuk }}</x-table.td>
                            <x-table.td>{{ $registrasi->tgl_keluar }}</x-table.td>
                            <x-table.td>{{ $registrasi->jam_keluar }}</x-table.td>
                            <x-table.td>{{ $registrasi->diagnosa_awal }}</x-table.td>
                            <x-table.td>{{ $registrasi->kd_diagnosa }}</x-table.td>
                            <x-table.td>{{ $registrasi->nm_diagnosa }}</x-table.td>
                            <x-table.td>{{ $registrasi->kd_tindakan_ralan }}</x-table.td>
                            <x-table.td>{{ $registrasi->nm_tindakan_ralan }}</x-table.td>
                            <x-table.td>{{ $registrasi->kd_tindakan_ranap }}</x-table.td>
                            <x-table.td>{{ $registrasi->nm_tindakan_ranap }}</x-table.td>
                            <x-table.td>{{ $registrasi->lama_operasi }}</x-table.td>
                            <x-table.td>{{ $registrasi->rujukan_masuk }}</x-table.td>
                            <x-table.td>{{ $registrasi->dokter_pj }}</x-table.td>
                            <x-table.td>{{ $registrasi->kelas }}</x-table.td>
                            <x-table.td>{{ $registrasi->jenis_bayar }}</x-table.td>
                            <x-table.td>{{ $registrasi->status_bayar }}</x-table.td>
                            <x-table.td>{{ $registrasi->status_pulang_ranap }}</x-table.td>
                            <x-table.td>{{ $registrasi->rujuk_keluar_rs }}</x-table.td>
                            <x-table.td>{{ $registrasi->alamat }}</x-table.td>
                            <x-table.td>{{ $registrasi->no_hp }}</x-table.td>
                            <x-table.td>{{ $registrasi->kunjungan_ke }}</x-table.td>
                        </x-table.tr>
                    @endforeach
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :count="$this->dataLaporanStatistik->count()" :total="$this->dataLaporanStatistik->total()">
                {{ $this->dataLaporanStatistik->links() }}
            </x-paginator>
        </x-slot>
    </x-card>
</div>
