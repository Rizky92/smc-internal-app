<div wire:init="loadProperties">
    <x-flash />

    <x-card use-loading>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.date />
                <x-filter.toggle class="ml-3" model="semuaPasien" title="Tampilkan Semua Pasien" />
                <x-filter.button-export-excel class="ml-auto" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                <x-filter.select-perpage :constantWidth="true" />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search />
            </x-row-col-flex>
        </x-slot>

        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" style="width: 160rem" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="no_rawat" title="No. Rawat" style="width: 20ch" />
                    <x-table.th name="tgl_registrasi" title="Tgl. Registrasi" style="width: 20ch" />
                    <x-table.th name="jam_reg" title="Jam Registrasi" style="width: 20ch" />
                    <x-table.th name="kelas" title="Kelas" style="width: 10ch" />
                    <x-table.th name="ruangan" title="Kamar" style="width: 35ch" />
                    <x-table.th name="trf_kamar" title="Tarif" style="width: 15ch" />
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
                    @forelse ($this->laporanPasienRanap as $pasien)
                        <x-table.tr>
                            <x-table.td>
                                {{ $pasien->no_rawat }}
                            </x-table.td>
                            <x-table.td>
                                {{ $pasien->tgl_registrasi }}
                            </x-table.td>
                            <x-table.td>{{ $pasien->jam_reg }}</x-table.td>
                            <x-table.td>{{ $pasien->kelas }}</x-table.td>
                            <x-table.td>{{ $pasien->ruangan }}</x-table.td>
                            <x-table.td>
                                {{ rp($pasien->trf_kamar) }}
                            </x-table.td>
                            <x-table.td>
                                {{ $pasien->no_rkm_medis }}
                            </x-table.td>
                            <x-table.td>
                                {{ $pasien->data_pasien }}
                            </x-table.td>
                            <x-table.td>
                                {{ $pasien->png_jawab }}
                            </x-table.td>
                            <x-table.td>{{ $pasien->nm_poli }}</x-table.td>
                            <x-table.td>
                                {{ $pasien->nm_dokter }}
                            </x-table.td>
                            <x-table.td>
                                {{ $pasien->stts_pulang }}
                            </x-table.td>
                            <x-table.td>
                                {{ $pasien->tgl_masuk }}
                            </x-table.td>
                            <x-table.td>
                                {{ $pasien->jam_masuk }}
                            </x-table.td>
                            <x-table.td>
                                {{ $pasien->tgl_keluar }}
                            </x-table.td>
                            <x-table.td>
                                {{ $pasien->jam_keluar }}
                            </x-table.td>
                            <x-table.td>{{ $pasien->dpjp }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="17" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>

        <x-slot name="footer">
            <x-paginator :data="$this->laporanPasienRanap" />
        </x-slot>
    </x-card>
</div>
