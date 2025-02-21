<div>
    <x-flash />

    <x-card use-loading>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.range-date />
                <x-filter.button-export-excel class="ml-auto" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                <x-filter.select-perpage />
                <x-filter.label class="ml-auto" constant-width>Rekening :</x-filter.label>
                <x-filter.select2 name="Kode Rekening" livewire show-key :options="$this->rekening" placeholder="SEMUA" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" style="width: 100rem" sortable zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th name="tgl_jurnal" title="Tgl." style="width: 13ch" />
                    <x-table.th name="jam_jurnal" title="Jam" style="width: 9ch" />
                    <x-table.th name="no_jurnal" title="No. Jurnal" style="width: 15ch" />
                    <x-table.th name="no_bukti" title="No. Bukti" style="width: 17ch" />
                    <x-table.th name="keterangan" title="Keterangan Jurnal" />
                    <x-table.th name="keterangan_pengeluaran" title="Keterangan Pengeluaran" />
                    <x-table.th name="catatan" title="Catatan" />
                    <x-table.th name="kd_rek" title="Kode" style="width: 10ch" />
                    <x-table.th name="nm_rek" title="Rekening" style="width: 30ch" />
                    <x-table.th name="debet" title="Debet" style="width: 20ch" />
                    <x-table.th name="kredit" title="Kredit" style="width: 20ch" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->bukuBesar as $jurnal)
                        <x-table.tr>
                            <x-table.td>
                                {{ $jurnal->tgl_jurnal }}
                            </x-table.td>
                            <x-table.td>
                                {{ $jurnal->jam_jurnal }}
                            </x-table.td>
                            <x-table.td>
                                {{ $jurnal->no_jurnal }}
                            </x-table.td>
                            <x-table.td>
                                {{ $jurnal->no_bukti }}
                            </x-table.td>
                            <x-table.td>
                                {{ $jurnal->keterangan }}
                            </x-table.td>
                            <x-table.td>
                                {{ optional($jurnal->pengeluaranHarian)->keterangan ?? '-' }}
                            </x-table.td>
                            <x-table.td>
                                @if ($jurnal->piutangDilunaskan && $jurnal->piutangDilunaskan->tagihan)
                                    {{ $jurnal->piutangDilunaskan->tagihan->catatan }}
                                @else
                                    -
                                @endif
                            </x-table.td>
                            <x-table.td>{{ $jurnal->kd_rek }}</x-table.td>
                            <x-table.td>{{ $jurnal->nm_rek }}</x-table.td>
                            <x-table.td>
                                {{ rp($jurnal->debet) }}
                            </x-table.td>
                            <x-table.td>
                                {{ rp($jurnal->kredit) }}
                            </x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="12" padding />
                    @endforelse
                </x-slot>
                <x-slot name="footer">
                    <x-table.tr>
                        <x-table.th colspan="8" />
                        <x-table.th title="TOTAL :" />
                        <x-table.th :title="rp(optional($this->totalDebetDanKredit)->debet)" />
                        <x-table.th :title="rp(optional($this->totalDebetDanKredit)->kredit)" />
                    </x-table.tr>
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->bukuBesar" />
        </x-slot>
    </x-card>
</div>
