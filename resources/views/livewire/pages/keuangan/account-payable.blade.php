<div wire:init="loadProperties">
    <x-flash />

    <x-card use-default-filter use-loading>
        <x-slot name="body">
            <x-navtabs livewire selected="medis" with-permissions>
                <x-slot name="tabs">
                    <x-navtabs.tab id="medis" title="Medis" :hasPermission="user()->can('keuangan.account-payable.read-medis')" />
                    <x-navtabs.tab id="nonmedis" title="Non Medis" :hasPermission="user()->can('keuangan.account-payable.read-nonmedis')" />
                </x-slot>
                <x-slot name="contents">
                    <x-navtabs.content id="medis" :hasPermission="user()->can('keuangan.account-payable.read-medis')">
                        <x-table :sortColumns="$sortColumns" style="width: 180rem" sortable zebra hover sticky nowrap>
                            <x-slot name="columns">
                                <x-table.th style="width: 20ch" name="no_tagihan" title="No. Tagihan" />
                                <x-table.th style="width: 20ch" name="no_order" title="No. Order" />
                                <x-table.th style="width: 20ch" name="no_faktur" title="No. Faktur" />
                                <x-table.th style="width: 50ch" name="nama_suplier" title="Nama Suplier" />
                                <x-table.th style="width: 20ch" name="tgl_tagihan" title="Tgl. Tagihan" />
                                <x-table.th style="width: 20ch" name="tgl_tempo" title="Tgl. Tempo" />
                                <x-table.th style="width: 20ch" name="tgl_terima" title="Tgl. Terima" />
                                <x-table.th style="width: 20ch" name="tgl_bayar" title="Tgl. Bayar" />
                                <x-table.th style="width: 15ch" name="status" title="Status Penerimaan" />
                                <x-table.th style="width: 25ch" name="nama_bayar" title="Akun Bayar" />
                                <x-table.th style="width: 30ch" name="tagihan" title="Jumlah Tagihan" />
                                <x-table.th style="width: 30ch" name="dibayar" title="Dibayar" />
                                <x-table.th style="width: 30ch" name="sisa" title="Sisa" />
                                <x-table.th style="width: 30ch" name="periode_0_30" title="0 - 30" />
                                <x-table.th style="width: 30ch" name="periode_31_60" title="31 - 60" />
                                <x-table.th style="width: 30ch" name="periode_61_90" title="61 - 90" />
                                <x-table.th style="width: 30ch" name="periode_90_up" title="> 90" />
                                <x-table.th name="keterangan" title="Keterangan" />
                            </x-slot>
                            <x-slot name="body">
                                @forelse ($this->dataAccountPayableMedis as $item)
                                    <x-table.tr>
                                        <x-table.td>
                                            {{ $item->no_tagihan }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->no_order }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->no_faktur }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->nama_suplier }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->tgl_tagihan }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->tgl_tempo }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->tgl_terima }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->tgl_bayar }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->status }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->nama_bayar }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ rp($item->tagihan) }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ rp($item->dibayar) }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ rp($item->sisa) }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ between($item->umur_hari, 0, 30) ? rp($item->sisa) : rp() }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ between($item->umur_hari, 31, 60) ? rp($item->sisa) : rp() }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ between($item->umur_hari, 61, 90) ? rp($item->sisa) : rp() }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->umur_hari > 90 ? rp($item->sisa) : rp() }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->keterangan }}
                                        </x-table.td>
                                    </x-table.tr>
                                @empty
                                    <x-table.tr-empty colspan="18" padding />
                                @endforelse
                            </x-slot>
                            <x-slot name="footer">
                                <x-table.tr>
                                    <x-table.th colspan="9" />
                                    <x-table.th title="TOTAL:" />
                                    <x-table.th :title="rp(optional($this->totalAccountPayableMedis)['totalTagihan'])" />
                                    <x-table.th :title="rp(optional($this->totalAccountPayableMedis)['totalDibayar'])" />
                                    <x-table.th :title="rp(optional($this->totalAccountPayableMedis)['totalSisaTagihan'])" />
                                    <x-table.th :title="rp(optional(optional($this->totalAccountPayableMedis)['totalSisaPerPeriode'])->get('periode_0_30'))" />
                                    <x-table.th :title="rp(optional(optional($this->totalAccountPayableMedis)['totalSisaPerPeriode'])->get('periode_31_60'))" />
                                    <x-table.th :title="rp(optional(optional($this->totalAccountPayableMedis)['totalSisaPerPeriode'])->get('periode_61_90'))" />
                                    <x-table.th :title="rp(optional(optional($this->totalAccountPayableMedis)['totalSisaPerPeriode'])->get('periode_90_up'))" />
                                    <x-table.th title="" />
                                </x-table.tr>
                            </x-slot>
                        </x-table>
                        <x-paginator class="px-4 py-3 bg-light" :data="$this->dataAccountPayableMedis" />
                    </x-navtabs.content>
                    <x-navtabs.content id="nonmedis" :hasPermission="user()->can('keuangan.account-payable.read-nonmedis')">
                        <x-table :sortColumns="$sortColumns" style="width: 180rem" sortable zebra hover sticky nowrap>
                            <x-slot name="columns">
                                <x-table.th style="width: 20ch" name="no_tagihan" title="No. Tagihan" />
                                <x-table.th style="width: 20ch" name="no_order" title="No. Order" />
                                <x-table.th style="width: 20ch" name="no_faktur" title="No. Faktur" />
                                <x-table.th style="width: 50ch" name="nama_suplier" title="Nama Suplier" />
                                <x-table.th style="width: 20ch" name="tgl_tagihan" title="Tgl. Tagihan" />
                                <x-table.th style="width: 20ch" name="tgl_tempo" title="Tgl. Tempo" />
                                <x-table.th style="width: 20ch" name="tgl_terima" title="Tgl. Terima" />
                                <x-table.th style="width: 20ch" name="tgl_bayar" title="Tgl. Bayar" />
                                <x-table.th style="width: 15ch" name="status" title="Status Penerimaan" />
                                <x-table.th style="width: 25ch" name="nama_bayar" title="Akun Bayar" />
                                <x-table.th style="width: 30ch" name="tagihan" title="Jumlah Tagihan" />
                                <x-table.th style="width: 30ch" name="dibayar" title="Dibayar" />
                                <x-table.th style="width: 30ch" name="sisa" title="Sisa" />
                                <x-table.th style="width: 30ch" name="periode_0_30" title="0 - 30" />
                                <x-table.th style="width: 30ch" name="periode_31_60" title="31 - 60" />
                                <x-table.th style="width: 30ch" name="periode_61_90" title="61 - 90" />
                                <x-table.th style="width: 30ch" name="periode_90_up" title="> 90" />
                                <x-table.th name="keterangan" title="Keterangan" />
                            </x-slot>
                            <x-slot name="body">
                                @forelse ($this->dataAccountPayableNonMedis as $item)
                                    <x-table.tr>
                                        <x-table.td>
                                            {{ $item->no_tagihan }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->no_order }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->no_faktur }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->nama_suplier }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->tgl_tagihan }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->tgl_tempo }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->tgl_terima }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->tgl_bayar }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->status }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->nama_bayar }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ rp($item->tagihan) }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ rp($item->dibayar) }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ rp($item->sisa) }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ between($item->umur_hari, 0, 30) ? rp($item->sisa) : rp() }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ between($item->umur_hari, 31, 60) ? rp($item->sisa) : rp() }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ between($item->umur_hari, 61, 90) ? rp($item->sisa) : rp() }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->umur_hari > 90 ? rp($item->sisa) : rp() }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->keterangan }}
                                        </x-table.td>
                                    </x-table.tr>
                                @empty
                                    <x-table.tr-empty colspan="18" padding />
                                @endforelse
                            </x-slot>
                            <x-slot name="footer">
                                <x-table.tr>
                                    <x-table.th colspan="9" />
                                    <x-table.th title="TOTAL:" />
                                    <x-table.th :title="rp(optional($this->totalAccountPayableNonMedis)['totalTagihan'])" />
                                    <x-table.th :title="rp(optional($this->totalAccountPayableNonMedis)['totalDibayar'])" />
                                    <x-table.th :title="rp(optional($this->totalAccountPayableNonMedis)['totalSisaTagihan'])" />
                                    <x-table.th :title="rp(optional(optional($this->totalAccountPayableNonMedis)['totalSisaPerPeriode'])->get('periode_0_30'))" />
                                    <x-table.th :title="rp(optional(optional($this->totalAccountPayableNonMedis)['totalSisaPerPeriode'])->get('periode_31_60'))" />
                                    <x-table.th :title="rp(optional(optional($this->totalAccountPayableNonMedis)['totalSisaPerPeriode'])->get('periode_61_90'))" />
                                    <x-table.th :title="rp(optional(optional($this->totalAccountPayableNonMedis)['totalSisaPerPeriode'])->get('periode_90_up'))" />
                                    <x-table.th title="" />
                                </x-table.tr>
                            </x-slot>
                        </x-table>
                        <x-paginator class="px-4 py-3 bg-light" :data="$this->dataAccountPayableNonMedis" />
                    </x-navtabs.content>
                </x-slot>
            </x-navtabs>
        </x-slot>
    </x-card>
</div>
