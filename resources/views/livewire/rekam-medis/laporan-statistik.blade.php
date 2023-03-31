<div>
    <x-flash />

    <x-card use-default-filter use-loading wire:init="loadProperties">
        <x-slot name="body">
            <x-table style="width: 450rem" zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th style="width: 20ch" title="No. Rawat" />
                    <x-table.th style="width: 10ch" title="No RM" />
                    <x-table.th title="Pasien" />
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
                    <x-table.th style="width: 20ch" title="Status Ralan" />
                    <x-table.th style="width: 20ch" title="Tgl. Masuk" />
                    <x-table.th style="width: 20ch" title="Jam Masuk" />
                    <x-table.th style="width: 20ch" title="Tgl. Pulang" />
                    <x-table.th style="width: 20ch" title="Jam Pulang" />
                    <x-table.th style="width: 20ch" title="Diagnosa Masuk" />
                    <x-table.th style="width: 15ch" title="ICD Diagnosa" />
                    <x-table.th style="width: 50ch" title="Diagnosa" />
                    <x-table.th style="width: 15ch" title="ICD Tindakan Ralan" />
                    <x-table.th style="width: 50ch" title="Tindakan Ralan" />
                    <x-table.th style="width: 15ch" title="ICD Tindakan Ranap" />
                    <x-table.th style="width: 50ch" title="Tindakan Ranap" />
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
                        @php
                            $kdDiagnosa = str($registrasi->kd_diagnosa)->split('/(, )/');
                            $nmDiagnosa = str($registrasi->nm_diagnosa)->split('/(, )/');
                            $kdTindakanRalan = str($registrasi->kd_tindakan_ralan)->split('/(, )/');
                            $nmTindakanRalan = str($registrasi->nm_tindakan_ralan)->split('/(, )/');
                            $kdTindakanRanap = str($registrasi->kd_tindakan_ranap)->split('/(, )/');
                            $nmTindakanRanap = str($registrasi->nm_tindakan_ranap)->split('/(, )/');
                            $dokterPj = str($registrasi->dokter_pj)->split('/(; )/');
                        @endphp
                        <x-table.tr>
                            <x-table.td>{{ $registrasi->no_rawat }}</x-table.td>
                            <x-table.td>{{ $registrasi->no_rm }}</x-table.td>
                            <x-table.td>{{ $registrasi->nm_pasien }}</x-table.td>
                            <x-table.td>{{ $registrasi->no_ktp }}</x-table.td>
                            <x-table.td>{{ $registrasi->jk }}</x-table.td>
                            <x-table.td>{{ $registrasi->tgl_lahir }}</x-table.td>
                            <x-table.td>{{ $registrasi->umur }}</x-table.td>
                            <x-table.td>{{ $registrasi->agama }}</x-table.td>
                            <x-table.td>{{ $registrasi->suku }}</x-table.td>
                            <x-table.td>{{ $registrasi->status_lanjut }}</x-table.td>
                            <x-table.td>{{ $registrasi->status_poli }}</x-table.td>
                            <x-table.td>{{ $registrasi->nm_poli }}</x-table.td>
                            <x-table.td>{{ $registrasi->nm_dokter }}</x-table.td>
                            <x-table.td>{{ $registrasi->status }}</x-table.td>
                            <x-table.td>{{ $registrasi->tgl_registrasi }}</x-table.td>
                            <x-table.td>{{ $registrasi->jam_registrasi }}</x-table.td>
                            <x-table.td>{{ $registrasi->tgl_keluar }}</x-table.td>
                            <x-table.td>{{ $registrasi->jam_keluar }}</x-table.td>
                            <x-table.td>{{ $registrasi->diagnosa_awal }}</x-table.td>
                            <x-table.td>
                                @foreach ($kdDiagnosa as $item)
                                    {{ $item }} @if (!$loop->last)
                                        <br>
                                    @endif
                                @endforeach
                            </x-table.td>
                            <x-table.td>
                                @foreach ($nmDiagnosa as $item)
                                    {{ $item }} @if (!$loop->last)
                                        <br>
                                    @endif
                                @endforeach
                            </x-table.td>
                            <x-table.td>
                                @foreach ($kdTindakanRalan as $item)
                                    {{ $item }} @if (!$loop->last)
                                        <br>
                                    @endif
                                @endforeach
                            </x-table.td>
                            <x-table.td>
                                @foreach ($nmTindakanRalan as $item)
                                    {{ $item }} @if (!$loop->last)
                                        <br>
                                    @endif
                                @endforeach
                            </x-table.td>
                            <x-table.td>
                                @foreach ($kdTindakanRanap as $item)
                                    {{ $item }} @if (!$loop->last)
                                        <br>
                                    @endif
                                @endforeach
                            </x-table.td>
                            <x-table.td>
                                @foreach ($nmTindakanRanap as $item)
                                    {{ $item }} @if (!$loop->last)
                                        <br>
                                    @endif
                                @endforeach
                            </x-table.td>
                            <x-table.td>{{ $registrasi->lama_operasi }}</x-table.td>
                            <x-table.td>{{ $registrasi->rujukan_masuk }}</x-table.td>
                            <x-table.td>
                                @foreach ($dokterPj as $item)
                                    {{ $item }} @if (!$loop->last)
                                        <br>
                                    @endif
                                @endforeach
                            </x-table.td>
                            <x-table.td>{{ $registrasi->kelas }}</x-table.td>
                            <x-table.td>{{ $registrasi->penjamin }}</x-table.td>
                            <x-table.td>{{ $registrasi->status_bayar }}</x-table.td>
                            <x-table.td>{{ $registrasi->status_pulang_ranap }}</x-table.td>
                            <x-table.td>{{ $registrasi->rujuk_keluar_rs }}</x-table.td>
                            <x-table.td>{{ $registrasi->alamat }}</x-table.td>
                            <x-table.td>{{ $registrasi->no_hp }}</x-table.td>
                            <x-table.td>{{ $registrasi->kunjungan_ke }}</x-table.td>
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
