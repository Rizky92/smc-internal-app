<div wire:init="loadProperties">
    <x-flash />

    <x-card use-loading>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.label constant-width>Periode :</x-filter.label>
                <x-filter.select
                    model="jenisPeriode"
                    :options="[
                        'jurnal'    => 'Waktu Jurnal',
                        'penagihan' => 'Tgl. Penagihan',
                        'bayar'     => 'Tgl. Bayar',
                    ]" />
                <x-filter.label class="px-3">dari</x-filter.label>
                <x-filter.range-date title="" />
                <x-button size="sm" title="Tarik Data Terbaru" icon="fas fa-sync-alt" class="ml-auto" wire:click.prevent="tarikDataTerbaru" />
                <x-filter.button-export-excel class="ml-3" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                <x-filter.select-perpage />
                <x-filter.label class="ml-auto" constant-width>Rekening :</x-filter.label>
                <x-filter.select2 name="Kode Rekening" model="kodeRekening" placeholder="-" :options="$this->akunPenagihanPiutang" show-key />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" style="width: 190rem" sortable zebra hover sticky nowrap>
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
                            <x-table.td>
                                {{ $item->waktu_jurnal }}
                            </x-table.td>
                            <x-table.td>{{ $item->no_rawat }}</x-table.td>
                            <x-table.td>
                                {{ $item->no_rkm_medis }}
                                {{ $item->nm_pasien }} ({{ $item->umur }})
                            </x-table.td>
                            <x-table.td>{{ $item->kd_pj }} {{ $item->nama_penjamin }}</x-table.td>
                            <x-table.td>
                                {{ $item->no_tagihan }}
                            </x-table.td>
                            <x-table.td>
                                {{ $item->nik_penagih }}
                                {{ $item->nama_penagih }}
                            </x-table.td>
                            <x-table.td>
                                {{ $item->nik_menyetujui }}
                                {{ $item->nama_penyetuju }}
                            </x-table.td>
                            <x-table.td>
                                {{ rp($item->piutang_dibayar) }}
                            </x-table.td>
                            <x-table.td>
                                {{ carbon($item->tgl_penagihan)->toDateString() }}
                            </x-table.td>
                            <x-table.td>
                                {{ carbon($item->tgl_jatuh_tempo)->toDateString() }}
                            </x-table.td>
                            <x-table.td>
                                {{ carbon($item->tgl_bayar)->toDateString() }}
                            </x-table.td>
                            <x-table.td>{{ $item->status }}</x-table.td>
                            <x-table.td>
                                {{ $item->nik_validasi }}
                                {{ $item->nama_pemvalidasi }}
                            </x-table.td>
                            <x-table.td>{{ $item->kd_rek }} {{ $item->nm_rek }}</x-table.td>
                            <x-table.td>
                                {{ $item->keterangan }}
                            </x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="16" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->dataPiutangDilunaskan" />
        </x-slot>
    </x-card>
</div>
