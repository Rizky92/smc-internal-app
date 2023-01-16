<div>
    <x-flash />

    <x-card>
        <x-slot name="header">
            <x-card.row-col>
                <x-filter.range-datetime />
                <x-filter.label class="ml-auto pr-3">Status:</x-filter.label>
                <div class="input-group input-group-sm" style="width: max-content">
                    <x-filter.select model="statusPerawatan" :options="[
                        '-' => 'Sedang Dirawat',
                        'tanggal_masuk' => 'Tgl. Masuk',
                        'tanggal_keluar' => 'Tgl. Keluar',
                    ]" />
                </div>
                <x-filter.button-export-excel class="ml-2" />
            </x-card.row-col>
            <x-card.row-col class="mt-3">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search />
            </x-card.row-col>
        </x-slot>

        <x-slot name="body" class="table-responsive">
            <x-table style="width: 180rem">
                <x-slot name="columns">
                    <x-table.th style="width: 20ch">No. Rawat</x-table.th>
                    <x-table.th style="width: 10ch">No. RM</x-table.th>
                    <x-table.th>Kamar</x-table.th>
                    <x-table.th style="width: 25ch">Pasien</x-table.th>
                    <x-table.th>Alamat</x-table.th>
                    <x-table.th style="width: 8ch">Agama</x-table.th>
                    <x-table.th style="width: 25ch">P.J.</x-table.th>
                    <x-table.th style="width: 20ch">Jenis Bayar</x-table.th>
                    <x-table.th style="width: 10ch">Asal Poli</x-table.th>
                    <x-table.th style="width: 25ch">Dokter Poli</x-table.th>
                    <x-table.th style="width: 15ch">Status</x-table.th>
                    <x-table.th style="width: 12ch">Tgl. Masuk</x-table.th>
                    <x-table.th style="width: 12ch">Jam Masuk</x-table.th>
                    <x-table.th style="width: 12ch">Tgl. Keluar</x-table.th>
                    <x-table.th style="width: 12ch">Jam Keluar</x-table.th>
                    <x-table.th style="width: 15ch">Tarif</x-table.th>
                    <x-table.th>Dokter P.J.</x-table.th>
                    <x-table.th>No. HP</x-table.th>
                </x-slot>
                <x-slot name="body">
                    @foreach ($this->daftarPasienRanap as $pasien)
                        <x-table.tr>
                            <x-table.td>
                                {{ $pasien->no_rawat }}
                                <x-slot name="clickable" data-no-rawat="{{ $pasien->no_rawat }}" data-kamar="{{ $pasien->ruangan }}" data-pasien="{{ $pasien->data_pasien }}" data-harga-kamar="{{ $pasien->trf_kamar }}" data-lama-inap="{{ $pasien->lama }}" data-total-harga="{{ $pasien->ttl_biaya }}" data-kd-kamar="{{ $pasien->kd_kamar }}" data-tgl-masuk="{{ $pasien->tgl_masuk }}" data-jam-masuk="{{ $pasien->jam_masuk }}"></x-slot>
                            </x-table.td>
                            <x-table.td>{{ $pasien->no_rkm_medis }}</x-table.td>
                            <x-table.td>{{ $pasien->ruangan }}</x-table.td>
                            <x-table.td>{{ $pasien->data_pasien }}</x-table.td>
                            <x-table.td>{{ $pasien->alamat_pasien }}</x-table.td>
                            <x-table.td>{{ $pasien->agama }}</x-table.td>
                            <x-table.td>{{ $pasien->pj }}</x-table.td>
                            <x-table.td>{{ $pasien->png_jawab }}</x-table.td>
                            <x-table.td>{{ $pasien->nm_poli }}</x-table.td>
                            <x-table.td>{{ $pasien->dokter_poli }}</x-table.td>
                            <x-table.td>{{ $pasien->stts_pulang }}</x-table.td>
                            <x-table.td>{{ $pasien->tgl_masuk }}</x-table.td>
                            <x-table.td>{{ $pasien->jam_masuk }}</x-table.td>
                            <x-table.td>{{ $pasien->tgl_keluar }}</x-table.td>
                            <x-table.td>{{ $pasien->jam_keluar }}</x-table.td>
                            <x-table.td>{{ rp($pasien->trf_kamar) }}</x-table.td>
                            <x-table.td>{{ $pasien->nama_dokter }}</x-table.td>
                            <x-table.td>{{ $pasien->no_tlp }}</x-table.td>
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
