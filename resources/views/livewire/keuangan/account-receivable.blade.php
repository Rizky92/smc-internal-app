<div wire:init="loadProperties">
    <x-flash />

    <x-card use-loading loading-target="loadProperties">
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.range-date />
                <x-filter.button-export-excel class="ml-auto" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                <x-filter.label constant-width>Status:</x-filter.label>
                <x-filter.select model="jenisPerawatan" :options="['semua' => 'Semua', 'ralan' => 'Rawat Jalan', 'ranap' => 'Rawat Inap']" selected="semua" />
                <x-filter.label class="ml-auto">Asuransi Pasien:</x-filter.label>
                <x-filter.select2 livewire name="jaminanPasien" show-key class="ml-3" :options="$this->penjamin" selected="-" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" style="width: 200rem" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th style="width: 15ch" name="no_tagihan" title="No. Tagihan" />
                    <x-table.th style="width: 15ch" name="no_rawat" title="No. Rawat" />
                    <x-table.th style="width: 12ch" name="tgl_tagihan" title="Tgl. Tagihan" />
                    <x-table.th style="width: 12ch" name="tgl_jatuh_tempo" title="Tgl. Jatuh Tempo" />
                    <x-table.th style="width: 20ch" name="tgl_bayar" title="Tgl. Bayar" />
                    <x-table.th name="no_rkm_medis" title="No RM" />
                    <x-table.th name="nm_pasien" title="Pasien" />
                    <x-table.th name="penjab_pasien" title="Jaminan Pasien" />
                    <x-table.th name="penjab_piutang" title="Jaminan Akun Piutang" />
                    <x-table.th name="catatan" title="Catatan" />
                    <x-table.th style="width: 12ch" name="status" title="Status Piutang" />
                    <x-table.th name="nama_bayar" title="Nama Bayar" />
                    <x-table.th name="total_piutang" title="Piutang" />
                    <x-table.th name="besar_cicilan" title="Cicilan" />
                    <x-table.th name="sisa_piutang" title="Sisa" />
                    <x-table.th style="width: 20ch" title="0 - 30" />
                    <x-table.th style="width: 20ch" title="31 - 60" />
                    <x-table.th style="width: 20ch" title="61 - 90" />
                    <x-table.th style="width: 20ch" title="> 90" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->dataAccountReceivable as $item)
                        <x-table.tr>
                            <x-table.td>{{ $item->no_tagihan }}</x-table.td>
                            <x-table.td>{{ $item->no_rawat }}</x-table.td>
                            <x-table.td>{{ $item->tgl_tagihan }}</x-table.td>
                            <x-table.td>{{ $item->tgl_jatuh_tempo }}</x-table.td>
                            <x-table.td>{{ $item->tgl_bayar ?? '-' }}</x-table.td>
                            <x-table.td>{{ $item->no_rkm_medis }}</x-table.td>
                            <x-table.td>{{ $item->nm_pasien }}</x-table.td>
                            <x-table.td>{{ $item->penjab_pasien }}</x-table.td>
                            <x-table.td>{{ $item->penjab_piutang }}</x-table.td>
                            <x-table.td>{{ $item->catatan }}</x-table.td>
                            <x-table.td>{{ $item->status }}</x-table.td>
                            <x-table.td>{{ $item->nama_bayar }}</x-table.td>
                            <x-table.td>{{ rp($item->total_piutang) }}</x-table.td>
                            <x-table.td>{{ rp($item->besar_cicilan) }}</x-table.td>
                            <x-table.td>{{ rp($item->sisa_piutang) }}</x-table.td>
                            <x-table.td>{{ $item->umur_hari <= 30 ? rp($item->sisa_piutang) : '-' }}</x-table.td>
                            <x-table.td>{{ $item->umur_hari > 30 && $item->umur_hari <= 60 ? rp($item->sisa_piutang) : '-' }}</x-table.td>
                            <x-table.td>{{ $item->umur_hari > 60 && $item->umur_hari <= 90 ? rp($item->sisa_piutang) : '-' }}</x-table.td>
                            <x-table.td>{{ $item->umur_hari > 90 ? rp($item->sisa_piutang) : '-' }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="19" />
                    @endforelse
                </x-slot>
                <x-slot name="footer">
                    <x-table.tr>
                        <x-table.th colspan="11" />
                        <x-table.th title="TOTAL :" />
                        <x-table.th :title="rp(optional($this->totalPiutangAging)['totalPiutang'])" />
                        <x-table.th :title="rp(optional($this->totalPiutangAging)['totalCicilan'])" />
                        <x-table.th :title="rp(optional($this->totalPiutangAging)['totalSisaCicilan'])" />
                        <x-table.th :title="rp(optional(optional($this->totalPiutangAging)['totalSisaPerPeriode'])->get('periode_0_30'))" />
                        <x-table.th :title="rp(optional(optional($this->totalPiutangAging)['totalSisaPerPeriode'])->get('periode_31_60'))" />
                        <x-table.th :title="rp(optional(optional($this->totalPiutangAging)['totalSisaPerPeriode'])->get('periode_61_90'))" />
                        <x-table.th :title="rp(optional(optional($this->totalPiutangAging)['totalSisaPerPeriode'])->get('periode_90_up'))" />
                    </x-table.tr>
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->dataAccountReceivable" />
        </x-slot>
    </x-card>
</div>
