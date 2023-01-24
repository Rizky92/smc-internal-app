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
                {{-- <x-filter.label constant-width>Per:</x-filter.label>
                <div class="input-group input-group-sm" style="width: 10rem">
                    <x-filter.select model="statusPerawatan" :options="[
                        'tanggal_masuk' => 'Tgl. Masuk Ranap',
                        'tanggal_keluar' => 'Tgl. Keluar Ranap',
                    ]" />
                </div> --}}
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search />
            </x-card.row-col>
        </x-slot>

        <x-slot name="body" class="table-responsive">
            <x-table style="width: 160rem">
                <x-slot name="columns">
                    <x-table.th style="width: 20ch">No. Rawat</x-table.th>
                    <x-table.th style="width: 15ch">Tgl. Registrasi</x-table.th>
                    <x-table.th style="width: 15ch">Jam Registrasi</x-table.th>
                    <x-table.th style="width: 35ch">Kamar</x-table.th>
                    <x-table.th style="width: 10ch">Kelas</x-table.th>
                    <x-table.th style="width: 10ch">No. RM</x-table.th>
                    <x-table.th style="width: 50ch">Pasien</x-table.th>
                    <x-table.th style="width: 25ch">Jenis Bayar</x-table.th>
                    <x-table.th style="width: 20ch">Asal Poli</x-table.th>
                    <x-table.th style="width: 40ch">Dokter Poli</x-table.th>
                    <x-table.th style="width: 15ch">Status</x-table.th>
                    <x-table.th style="width: 12ch">Tgl. Masuk</x-table.th>
                    <x-table.th style="width: 12ch">Jam Masuk</x-table.th>
                    <x-table.th style="width: 12ch">Tgl. Keluar</x-table.th>
                    <x-table.th style="width: 12ch">Jam Keluar</x-table.th>
                    <x-table.th style="width: 40ch">DPJP</x-table.th>
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
