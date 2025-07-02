<div wire:init="loadProperties">
    <x-flash />

    <x-card use-default-filter use-loading>
        <x-slot name="body">
            <x-table style="width: 150rem" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="nm_kec" title="Kecamatan" width="250" />
                    <x-table.th name="no_rkm_medis" title="No. RM" width="70" />
                    <x-table.th name="no_rawat" title="No. Registrasi" width="150" />
                    <x-table.th name="nm_pasien" title="Nama Pasien" width="250" />
                    <x-table.th name="tgl_lahir" title="Tgl. Lahir" width="80" />
                    <x-table.th name="alamat" title="Alamat" width="500" />
                    <x-table.th name="umur" title="Umur" width="50" />
                    <x-table.th name="jk" title="L / P" width="50" />
                    <x-table.th title="ICD-10" />
                    <x-table.th title="Diagnosa" />
                    <x-table.th name="agama" title="Agama" width="100" />
                    <x-table.th name="pnd" title="Pendidikan" width="100" />
                    <x-table.th name="nama_bahasa" title="Bahasa" width="100" />
                    <x-table.th name="nama_suku_bangsa" title="Suku" width="100" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->demografiPasien as $pasien)
                        <x-table.tr>
                            <x-table.td>{{ $pasien->nm_kec }}</x-table.td>
                            <x-table.td>
                                {{ $pasien->no_rkm_medis }}
                            </x-table.td>
                            <x-table.td>
                                {{ $pasien->no_rawat }}
                            </x-table.td>
                            <x-table.td>
                                {{ $pasien->nm_pasien }}
                            </x-table.td>
                            <x-table.td>
                                {{ $pasien->tgl_lahir }}
                            </x-table.td>
                            <x-table.td>{{ $pasien->alamat }}</x-table.td>
                            <x-table.td>
                                {{ sprintf('%s %s', $pasien->umurdaftar, $pasien->sttsumur) }}
                            </x-table.td>
                            <x-table.td>{{ $pasien->jk }}</x-table.td>
                            <x-table.td>
                                {{ $pasien->kd_penyakit }}
                            </x-table.td>
                            <x-table.td>
                                {{ $pasien->nm_penyakit }}
                            </x-table.td>
                            <x-table.td>{{ $pasien->agama }}</x-table.td>
                            <x-table.td>{{ $pasien->pnd }}</x-table.td>
                            <x-table.td>
                                {{ $pasien->nama_bahasa }}
                            </x-table.td>
                            <x-table.td>
                                {{ $pasien->nama_suku_bangsa }}
                            </x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="12" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->demografiPasien" />
        </x-slot>
    </x-card>
</div>
