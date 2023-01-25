<div>
    <x-flash />

    <x-card>
        <x-slot name="header">
            <x-card.row-col>
                <x-filter.date />
                <x-filter.toggle class="ml-3" model="tampilkanSemuaPasienPerTanggal" title="Tampilkan Semua Pasien" />
                <x-filter.button-export-excel class="ml-auto" />
            </x-card.row-col>
            <x-card.row-col class="mt-2">
                <x-filter.select-perpage :constantWidth="true" />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search />
            </x-card.row-col>
        </x-slot>

        <x-slot name="body" class="table-responsive">
            <x-table style="width: 160rem" sortable :sortColumns="$sortColumns">
                <x-slot name="columns">
                    <x-table.th name="no_rawat" title="No. Rawat" style="width: 20ch" />
                    <x-table.th name="tgl_registrasi" title="Tgl. Registrasi" style="width: 20ch" />
                    <x-table.th name="jam_reg" title="Jam Registrasi" style="width: 20ch" />
                    <x-table.th name="ruangan" title="Kamar" style="width: 35ch" />
                    <x-table.th name="kelas" title="Kelas" style="width: 10ch" />
                    <x-table.th name="no_rkm_medis" title="No. RM" style="width: 15ch" />
                    <x-table.th name="data_pasien" title="Pasien" style="width: 50ch" />
                    <x-table.th name="png_jawab" title="Jenis Bayar" style="width: 25ch" />
                    <x-table.th name="nm_poli" title="Asal Poli" style="width: 20ch" />
                    <x-table.th name="dokter_poli" title="Dokter Poli" style="width: 40ch" />
                    <x-table.th name="stts_pulang" title="Status" style="width: 15ch" />
                    <x-table.th name="tgl_masuk" title="Tgl. Masuk" style="width: 15ch" />
                    <x-table.th name="jam_masuk" title="Jam Masuk" style="width: 15ch" />
                    <x-table.th name="tgl_keluar" title="Tgl. Keluar" style="width: 15ch" />
                    <x-table.th name="jam_keluar" title="Jam Keluar" style="width: 15ch" />
                    <x-table.th name="dpjp" title="DPJP" style="width: 40ch" />
                </x-slot>
                <x-slot name="body">
                    @foreach ($this->daftarPasienRanap as $pasien)
                        <x-table.tr>
                            <x-table.td>{{ $pasien->no_rawat }}</x-table.td>
                            <x-table.td>{{ $pasien->tgl_registrasi }}</x-table.td>
                            <x-table.td>{{ $pasien->jam_reg }}</x-table.td>
                            <x-table.td>{{ $pasien->ruangan }}</x-table.td>
                            <x-table.td>{{ $pasien->kelas }}</x-table.td>
                            <x-table.td>{{ $pasien->no_rkm_medis }}</x-table.td>
                            <x-table.td>{{ $pasien->data_pasien }}</x-table.td>
                            <x-table.td>{{ $pasien->png_jawab }}</x-table.td>
                            <x-table.td>{{ $pasien->nm_poli }}</x-table.td>
                            <x-table.td>{{ $pasien->dokter_poli }}</x-table.td>
                            <x-table.td>{{ $pasien->stts_pulang }}</x-table.td>
                            <x-table.td>{{ $pasien->tgl_masuk }}</x-table.td>
                            <x-table.td>{{ $pasien->jam_masuk }}</x-table.td>
                            <x-table.td>{{ $pasien->tgl_keluar }}</x-table.td>
                            <x-table.td>{{ $pasien->jam_keluar }}</x-table.td>
                            <x-table.td>{{ $pasien->dpjp }}</x-table.td>
                        </x-table.tr>
                    @endforeach
                </x-slot>
            </x-table>
        </x-slot>

        <x-slot name="footer">
            <x-paginator :data="$this->daftarPasienRanap" />
        </x-slot>
    </x-card>
</div>
