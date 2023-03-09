<div>
    <x-flash />

    <x-card use-default-filter use-loading>
        <x-slot name="body" class="table-responsive">
            <x-table sortable :sortColumns="$sortColumns" style="min-width: 100%; width: 180rem">
                <x-slot name="columns">
                    <x-table.th style="width: 15ch" name="no_jurnal" title="No. Jurnal" />
                    <x-table.th style="width: 17ch" name="waktu_jurnal" title="Tgl. Jurnal" />
                    <x-table.th style="width: 15ch" name="no_rawat" title="No. Rawat" />
                    <x-table.th name="no_rkm_medis" title="Pasien" />
                    <x-table.th style="width: 30ch" name="kd_pj" title="Penjamin" />
                    <x-table.th style="width: 15ch" name="no_tagihan" title="No. Tagihan" />
                    <x-table.th style="width: 28ch" name="nik_penagih" title="Penagih" />
                    <x-table.th style="width: 28ch" name="nik_penyetuju" title="Verifikasi" />
                    <x-table.th style="width: 17ch" name="piutang_dibayar" title="Nominal" />
                    <x-table.th style="width: 14ch" name="tgl_penagihan" title="Tgl. Tagihan" />
                    <x-table.th style="width: 19ch" name="tgl_jatuh_tempo" title="Tgl. Jatuh Tempo" />
                    <x-table.th style="width: 14ch" name="tgl_bayar" title="Tgl. Dibayar" />
                    <x-table.th style="width: 11ch" name="status" title="Status" />
                    <x-table.th style="width: 37ch" title="Keterangan" />
                    <x-table.th style="width: 28ch" name="nik_validasi" title="Validasi oleh" />
                    <x-table.th style="width: 20ch" name="kd_rek" title="Rekening" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataPiutangDilunaskan as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->no_jurnal }}</x-table.td>
                            <x-table.td>{{ $item->waktu_jurnal }}</x-table.td>
                            <x-table.td>{{ $item->no_rawat }}</x-table.td>
                            <x-table.td>{{ $item->no_rkm_medis }} {{ $item->pasien->nm_pasien }} ({{ $item->registrasi->umurdaftar }} {{ $item->registrasi->sttsumur }})</x-table.td>
                            <x-table.td>{{ $item->kd_pj }} {{ $item->penjamin->nama_penjamin }}</x-table.td>
                            <x-table.td>{{ $item->no_tagihan }}</x-table.td>
                            <x-table.td>{{ $item->nik_penagih }} {{ optional($item->penagih)->nama }}</x-table.td>
                            <x-table.td>{{ $item->nik_menyetujui }} {{ optional($item->penyetuju)->nama }}</x-table.td>
                            <x-table.td>{{ rp($item->piutang_dibayar) }}</x-table.td>
                            <x-table.td>{{ carbon($item->tgl_penagihan)->format('Y-m-d') }}</x-table.td>
                            <x-table.td>{{ carbon($item->tgl_jatuh_tempo)->format('Y-m-d') }}</x-table.td>
                            <x-table.td>{{ carbon($item->tgl_bayar)->format('Y-m-d') }}</x-table.td>
                            <x-table.td>{{ $item->status }}</x-table.td>
                            <x-table.td>{{ $item->nik_validasi }} {{ optional($item->pemvalidasi)->nama }}</x-table.td>
                            <x-table.td>{{ $item->kd_rek }} {{ $item->nm_rek }}</x-table.td>
                            <x-table.td>{{ $item->jurnal->keterangan }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="13" />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->dataPiutangDilunaskan" />
        </x-slot>
    </x-card>
</div>
