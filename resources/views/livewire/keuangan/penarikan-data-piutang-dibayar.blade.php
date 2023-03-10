<div>
    <x-flash />

    <x-card use-loading>
        <x-slot name="header">
            <x-card.row-col>
                <x-filter.label constant-width>Periode:</x-filter.label>
                <div class="input-group input-group-sm" style="width: max-content">
                    <x-filter.select model="jenisPeriode" :options="['jurnal' => 'Waktu Jurnal', 'penagihan' => 'Tgl. Penagihan', 'bayar' => 'Tgl. Bayar']" />
                </div>
                <x-filter.label class="px-3">dari</x-filter.label>
                <x-filter.range-date title="" />
                <x-filter.button-export-excel class="ml-auto" />
            </x-card.row-col>
            <x-card.row-col class="mt-2">
                <x-filter.select-perpage />
                <x-filter.label class="ml-auto" constant-width>Rekening :</x-filter.label>
                <x-filter.select2 name="Kode Rekening" model="kodeRekening" :collection="$this->akunPenagihanPiutang" />
            </x-card.row-col>
            <x-card.row-col class="mt-2">
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-card.row-col>
        </x-slot>
        <x-slot name="body" class="table-responsive">
            <x-table sortable :sortColumns="$sortColumns" style="min-width: 100%; width: 190rem">
                <x-slot name="columns">
                    <x-table.th style="width: 15ch" name="no_jurnal" title="No. Jurnal" />
                    <x-table.th style="width: 17ch" name="waktu_jurnal" title="Tgl. Jurnal" />
                    <x-table.th style="width: 15ch" name="no_rawat" title="No. Rawat" />
                    <x-table.th name="no_rkm_medis" title="Pasien" />
                    <x-table.th style="width: 30ch" name="nama_penjamin" title="Penjamin" />
                    <x-table.th style="width: 15ch" name="no_tagihan" title="No. Tagihan" />
                    <x-table.th style="width: 30ch" name="nik_penagih" title="Penagih" />
                    <x-table.th style="width: 30ch" name="nik_penyetuju" title="Verifikasi" />
                    <x-table.th style="width: 17ch" name="piutang_dibayar" title="Nominal" />
                    <x-table.th style="width: 14ch" name="tgl_penagihan" title="Tgl. Tagihan" />
                    <x-table.th style="width: 19ch" name="tgl_jatuh_tempo" title="Tgl. Jatuh Tempo" />
                    <x-table.th style="width: 14ch" name="tgl_bayar" title="Tgl. Dibayar" />
                    <x-table.th style="width: 11ch" name="status" title="Status" />
                    <x-table.th style="width: 30ch" name="nik_validasi" title="Validasi oleh" />
                    <x-table.th style="width: 20ch" name="kd_rek" title="Rekening" />
                    <x-table.th style="width: 37ch" name="keterangan" title="Keterangan" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataPiutangDilunaskan as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->no_jurnal }}</x-table.td>
                            <x-table.td>{{ $item->waktu_jurnal }}</x-table.td>
                            <x-table.td>{{ $item->no_rawat }}</x-table.td>
                            <x-table.td>{{ $item->no_rkm_medis }} {{ $item->nm_pasien }} ({{ $item->umur }})</x-table.td>

                            <x-table.td>{{ $item->kd_pj }} {{ $item->nama_penjamin }}</x-table.td>
                            <x-table.td>{{ $item->no_tagihan }}</x-table.td>
                            <x-table.td>{{ $item->nik_penagih }} {{ $item->nama_penagih }}</x-table.td>
                            <x-table.td>{{ $item->nik_menyetujui }} {{ $item->nama_penyetuju }}</x-table.td>

                            <x-table.td>{{ rp($item->piutang_dibayar) }}</x-table.td>
                            <x-table.td>{{ carbon($item->tgl_penagihan)->format('Y-m-d') }}</x-table.td>
                            <x-table.td>{{ carbon($item->tgl_jatuh_tempo)->format('Y-m-d') }}</x-table.td>
                            <x-table.td>{{ carbon($item->tgl_bayar)->format('Y-m-d') }}</x-table.td>

                            <x-table.td>{{ $item->status }}</x-table.td>
                            <x-table.td>{{ $item->nik_validasi }} {{ $item->nama_pemvalidasi }}</x-table.td>
                            <x-table.td>{{ $item->kd_rek }} {{ $item->nm_rek }}</x-table.td>
                            <x-table.td>{{ $item->keterangan }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="16" />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->dataPiutangDilunaskan" />
        </x-slot>
    </x-card>
</div>
