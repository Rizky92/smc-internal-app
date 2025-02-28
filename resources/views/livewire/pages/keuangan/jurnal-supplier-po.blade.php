<div wire:init="loadProperties">
    <x-flash />

    <x-card use-loading>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.range-date />
                <x-button size="sm" title="Tarik Data Terbaru" icon="fas fa-sync-alt" class="ml-auto" wire:click.prevent="tarikDataTerbaru" />
                <x-filter.button-export-excel class="ml-2" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body" class="tab-content">
            <x-navtabs livewire selected="medis">
                <x-slot name="tabs">
                    <x-navtabs.tab id="medis" title="Obat/BHP/Alkes" />
                    <x-navtabs.tab id="nonmedis" title="Non Medis" />
                </x-slot>
                <x-slot name="contents">
                    <x-navtabs.content id="medis">
                        <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                            <x-slot name="columns">
                                <x-table.th name="no_jurnal" title="No. Jurnal" />
                                <x-table.th name="waktu_jurnal" title="Waktu" />
                                <x-table.th name="no_faktur" title="No. Faktur" />
                                <x-table.th name="status" title="Status" />
                                <x-table.th name="besar_bayar" title="Nominal" />
                                <x-table.th name="nama_bayar" title="Akun Bayar" />
                                <x-table.th name="kd_rek" title="Kode Rekening" />
                                <x-table.th name="nama_suplier" title="Supplier" />
                                <x-table.th name="nm_pegawai" title="Petugas" />
                            </x-slot>
                            <x-slot name="body">
                                @forelse ($this->jurnalBarangMedis as $jurnal)
                                    <x-table.tr>
                                        <x-table.td>
                                            {{ $jurnal->no_jurnal }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $jurnal->waktu_jurnal }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $jurnal->no_faktur }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $jurnal->status }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ rp($jurnal->besar_bayar) }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $jurnal->nama_bayar }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $jurnal->kd_rek }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $jurnal->nama_suplier }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $jurnal->nm_pegawai }}
                                        </x-table.td>
                                    </x-table.tr>
                                @empty
                                    <x-table.tr-empty colspan="10" padding />
                                @endforelse
                            </x-slot>
                        </x-table>
                        <x-paginator class="px-4 py-3 bg-light" :data="$this->jurnalBarangMedis" />
                    </x-navtabs.content>
                    <x-navtabs.content id="nonmedis">
                        <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                            <x-slot name="columns">
                                <x-table.th name="no_jurnal" title="No. Jurnal" />
                                <x-table.th name="waktu_jurnal" title="Waktu" />
                                <x-table.th name="no_faktur" title="No. Faktur" />
                                <x-table.th name="status" title="Status" />
                                <x-table.th name="besar_bayar" title="Nominal" />
                                <x-table.th name="nama_bayar" title="Akun Bayar" />
                                <x-table.th name="kd_rek" title="Kode Rekening" />
                                <x-table.th name="nama_suplier" title="Supplier" />
                                <x-table.th name="nm_pegawai" title="Petugas" />
                            </x-slot>
                            <x-slot name="body">
                                @forelse ($this->jurnalBarangNonMedis as $jurnal)
                                    <x-table.tr>
                                        <x-table.td>
                                            {{ $jurnal->no_jurnal }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $jurnal->waktu_jurnal }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $jurnal->no_faktur }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $jurnal->status }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ rp($jurnal->besar_bayar) }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $jurnal->nama_bayar }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $jurnal->kd_rek }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $jurnal->nama_suplier }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $jurnal->nm_pegawai }}
                                        </x-table.td>
                                    </x-table.tr>
                                @empty
                                    <x-table.tr-empty colspan="10" padding />
                                @endforelse
                            </x-slot>
                        </x-table>
                        <x-paginator class="px-4 py-3 bg-light" :data="$this->jurnalBarangNonMedis" />
                    </x-navtabs.content>
                </x-slot>
            </x-navtabs>
        </x-slot>
    </x-card>
</div>
