<div>
    <x-flash />

    <x-card use-default-filter use-loading>
        <x-slot name="body">
            <x-table zebra hover sticky nowrap style="width: 360rem">
                <x-slot name="columns">
                    <x-table.th style="width: 20ch" title="No. Rawat" />
                    <x-table.th style="width: 8ch" title="No RM" />
                    <x-table.th title="Pasien" />
                    <x-table.th style="width: 18ch" title="NIK" />
                    <x-table.th style="width: 7ch" title="L / P" />
                    <x-table.th style="width: 12ch" title="Tgl. Lahir" />
                    <x-table.th style="width: 10ch" title="Umur" />
                    <x-table.th style="width: 11ch" title="Agama" />
                    <x-table.th style="width: 11ch" title="Suku" />
                    <x-table.th style="width: 17ch" title="Jenis Rawat" />
                    <x-table.th style="width: 17ch" title="Kamar" />
                    <x-table.th style="width: 19ch" title="Pasien Lama / Baru" />
                    <x-table.th style="width: 15ch" title="Asal Poli" />
                    <x-table.th style="width: 30ch" title="Dokter Poli" />
                    <x-table.th style="width: 15ch" title="Status Ralan" />
                    <x-table.th style="width: 15ch" title="Tgl. Masuk" />
                    <x-table.th style="width: 15ch" title="Jam Masuk" />
                    <x-table.th style="width: 15ch" title="Tgl. Pulang" />
                    <x-table.th style="width: 15ch" title="Jam Pulang" />
                    <x-table.th style="width: 18ch" title="Diagnosa Masuk" />
                    <x-table.th style="width: 10ch" title="ICD-10" />
                    <x-table.th style="width: 50ch" title="Diagnosa" />
                    <x-table.th style="width: 50ch" title="Tindakan Ralan" />
                    <x-table.th style="width: 50ch" title="Tindakan Ranap" />
                    <x-table.th style="width: 13ch" title="Lama Operasi" />
                    <x-table.th style="width: 20ch" title="Rujukan Masuk" />
                    <x-table.th style="width: 30ch" title="DPJP Ranap" />
                    <x-table.th style="width: 8ch" title="Kelas" />
                    <x-table.th style="width: 30ch" title="Penjamin" />
                    <x-table.th style="width: 20ch" title="Status Bayar" />
                    <x-table.th style="width: 20ch" title="Status Pulang" />
                    <x-table.th style="width: 15ch" title="Rujukan Keluar" />
                    <x-table.th style="width: 40ch" title="Alamat" />
                    <x-table.th style="width: 15ch" title="No. HP" />
                    <x-table.th style="width: 15ch" title="Kunjungan ke" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataLaporanStatistik as $registrasi)
                        @php
                            $ruangan = str($registrasi->ruangan)->split('/(; )/');
                            $icdDiagnosa = str($registrasi->icd_diagnosa)->split('/(; )/');
                            $diagnosa = str($registrasi->diagnosa)->split('/(; )/');
                            $tindakanRalan = str($registrasi->nm_tindakan_ralan)->split('/(; )/');
                            $tindakanRanap = str($registrasi->nm_tindakan_ranap)->split('/(; )/');
                            $dokterPj = str($registrasi->dokter_pj)->split('/(; )/');
                        @endphp

                        <x-table.tr>
                            <x-table.td>
                                {{ $registrasi->no_rawat }}
                            </x-table.td>
                            <x-table.td>
                                {{ $registrasi->no_rm }}
                            </x-table.td>
                            <x-table.td>
                                {{ $registrasi->nm_pasien }}
                            </x-table.td>
                            <x-table.td>
                                {{ $registrasi->no_ktp }}
                            </x-table.td>
                            <x-table.td>{{ $registrasi->jk }}</x-table.td>
                            <x-table.td>
                                {{ $registrasi->tgl_lahir }}
                            </x-table.td>
                            <x-table.td>
                                {{ $registrasi->umur }}
                            </x-table.td>
                            <x-table.td>
                                {{ $registrasi->agama }}
                            </x-table.td>
                            <x-table.td>
                                {{ $registrasi->suku }}
                            </x-table.td>
                            <x-table.td>
                                {{ $registrasi->status_lanjut }}
                            </x-table.td>
                            <x-table.td>
                                {{ $registrasi->ruangan }}
                            </x-table.td>
                            <x-table.td>
                                {{ $registrasi->status_poli }}
                            </x-table.td>
                            <x-table.td>
                                {{ $registrasi->nm_poli }}
                            </x-table.td>
                            <x-table.td>
                                {{ $registrasi->nm_dokter }}
                            </x-table.td>
                            <x-table.td>
                                {{ $registrasi->status }}
                            </x-table.td>
                            <x-table.td>
                                {{ $registrasi->tgl_registrasi }}
                            </x-table.td>
                            <x-table.td>
                                {{ $registrasi->jam_registrasi }}
                            </x-table.td>
                            <x-table.td>
                                {{ $registrasi->tgl_keluar }}
                            </x-table.td>
                            <x-table.td>
                                {{ $registrasi->jam_keluar }}
                            </x-table.td>
                            <x-table.td>
                                {{ $registrasi->diagnosa_awal }}
                            </x-table.td>
                            <x-table.td>
                                @foreach ($icdDiagnosa as $item)
                                    {{ $item }}
                                    @if (! $loop->last)
                                        <br />
                                    @endif
                                @endforeach
                            </x-table.td>
                            <x-table.td>
                                @foreach ($diagnosa as $item)
                                    {{ $item }}
                                    @if (! $loop->last)
                                        <br />
                                    @endif
                                @endforeach
                            </x-table.td>
                            <x-table.td>
                                @foreach ($tindakanRalan as $item)
                                    {{ $item }}
                                    @if (! $loop->last)
                                        <br />
                                    @endif
                                @endforeach
                            </x-table.td>
                            <x-table.td>
                                @foreach ($tindakanRanap as $item)
                                    {{ $item }}
                                    @if (! $loop->last)
                                        <br />
                                    @endif
                                @endforeach
                            </x-table.td>
                            <x-table.td>
                                {{ $registrasi->lama_operasi }}
                            </x-table.td>
                            <x-table.td>
                                {{ $registrasi->rujukan_masuk }}
                            </x-table.td>
                            <x-table.td>
                                @foreach ($dokterPj as $item)
                                    {{ $item }}
                                    @if (! $loop->last)
                                        <br />
                                    @endif
                                @endforeach
                            </x-table.td>
                            <x-table.td>
                                {{ $registrasi->kelas }}
                            </x-table.td>
                            <x-table.td>
                                {{ $registrasi->penjamin }}
                            </x-table.td>
                            <x-table.td>
                                {{ $registrasi->status_bayar }}
                            </x-table.td>
                            <x-table.td>
                                {{ $registrasi->status_pulang_ranap }}
                            </x-table.td>
                            <x-table.td>
                                {{ $registrasi->rujuk_keluar_rs }}
                            </x-table.td>
                            <x-table.td>
                                {{ $registrasi->alamat }}
                            </x-table.td>
                            <x-table.td>
                                {{ $registrasi->no_hp }}
                            </x-table.td>
                            <x-table.td>
                                {{ $registrasi->kunjungan_ke }}
                            </x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="33" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->dataLaporanStatistik" />
        </x-slot>
    </x-card>
</div>
