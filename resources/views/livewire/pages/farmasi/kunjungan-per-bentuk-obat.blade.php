<div wire:init="loadProperties">
    <x-flash />

    <x-card use-loading>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.range-date />
                <x-filter.button-export-excel class="ml-auto" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                <x-filter.select-perpage />
                <x-filter.label class="ml-auto pr-3">Jenis Kunjungan:</x-filter.label>
                <x-filter.select
                    model="jenisKunjungan"
                    :options="[
                        'semua' => 'Semua',
                        'ralan' => 'Rawat Jalan',
                        'ranap' => 'Rawat Inap',
                        'igd' => 'Rawat IGD',
                    ]" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-navtabs livewire selected="obat-regular">
                <x-slot name="tabs">
                    <x-navtabs.tab id="obat-regular" title="Obat Regular" />
                    <x-navtabs.tab id="obat-racikan" title="Obat Racikan" />
                </x-slot>
                <x-slot name="contents">
                    <x-navtabs.content id="obat-regular">
                        <x-table class="mb-0" :sortColumns="$sortColumns" sortable zebra hover sticky style="width: 120rem">
                            <x-slot name="columns">
                                <x-table.th style="width: 13ch" name="tgl_perawatan" title="Tanggal" />
                                <x-table.th style="width: 13ch" name="no_resep" title="No. Resep" />
                                <x-table.th style="width: 9ch" name="no_rkm_medis" title="No. RM" />
                                <x-table.th style="width: 25ch" nama="nm_pasien" title="Pasien" />
                                <x-table.th style="width: 25ch" name="png_jawab" title="Jenis Bayar" />
                                <x-table.th style="width: 18ch" name="status" title="Jenis Perawatan" />
                                <x-table.th style="width: 20ch" name="nm_poli" title="Asal Poli" />
                                <x-table.th style="width: 40ch" name="nm_dokter" title="Dokter Peresep" />
                                <x-table.th style="width: 19ch" name="waktu_validasi" title="Waktu Validasi" />
                                <x-table.th style="width: 19ch" name="waktu_penyerahan" title="Waktu Penyerahan" />
                                <x-table.th style="width: 14ch" name="selisih" title="Selisih Waktu" />
                                <x-table.th style="width: 20ch" name="total" title="Total Pembelian" />
                                <x-table.th name="jumlah" title="Jumlah" />
                            </x-slot>
                            <x-slot name="body">
                                @forelse ($this->dataKunjunganResepObatRegular as $resep)
                                    <x-table.tr>
                                        <x-table.td>
                                            {{ $resep->tgl_perawatan }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $resep->no_resep }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $resep->no_rkm_medis }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $resep->nm_pasien }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $resep->png_jawab }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $resep->status }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $resep->nm_poli }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $resep->nm_dokter }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $resep->waktu_validasi }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $resep->waktu_penyerahan }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{
                                                $resep->waktu_penyerahan && $resep->waktu_validasi
                                                    ? carbon_immutable($resep->waktu_validasi)
                                                        ->diff(carbon($resep->waktu_penyerahan))
                                                        ->format('%R %H:%I:%S')
                                                    : null
                                            }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ rp($resep->total) }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $resep->jumlah }}
                                        </x-table.td>
                                    </x-table.tr>
                                @empty
                                    <x-table.tr-empty colspan="13" padding />
                                @endforelse
                            </x-slot>
                        </x-table>
                        <x-paginator class="px-4 py-3 bg-light" :data="$this->dataKunjunganResepObatRegular" />
                    </x-navtabs.content>
                    <x-navtabs.content id="obat-racikan">
                        <x-table class="mb-0" :sortColumns="$sortColumns" sortable zebra hover sticky nowrap style="width: 120rem">
                            <x-slot name="columns">
                                <x-table.th style="width: 13ch" name="tgl_perawatan" title="Tanggal" />
                                <x-table.th style="width: 13ch" name="no_resep" title="No. Resep" />
                                <x-table.th style="width: 9ch" name="no_rkm_medis" title="No. RM" />
                                <x-table.th style="width: 25ch" nama="nm_pasien" title="Pasien" />
                                <x-table.th style="width: 25ch" name="png_jawab" title="Jenis Bayar" />
                                <x-table.th style="width: 18ch" name="status" title="Jenis Perawatan" />
                                <x-table.th style="width: 20ch" name="nm_poli" title="Asal Poli" />
                                <x-table.th style="width: 40ch" name="nm_dokter" title="Dokter Peresep" />
                                <x-table.th style="width: 19ch" name="waktu_validasi" title="Waktu Validasi" />
                                <x-table.th style="width: 19ch" name="waktu_penyerahan" title="Waktu Penyerahan" />
                                <x-table.th style="width: 14ch" name="selisih" title="Selisih Waktu" />
                                <x-table.th style="width: 20ch" name="total" title="Total Pembelian" />
                                <x-table.th name="jumlah" title="Jumlah" />
                            </x-slot>
                            <x-slot name="body">
                                @forelse ($this->dataKunjunganResepObatRacikan as $resep)
                                    <x-table.tr>
                                        <x-table.td>
                                            {{ $resep->tgl_perawatan }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $resep->no_resep }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $resep->no_rkm_medis }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $resep->nm_pasien }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $resep->png_jawab }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $resep->status }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $resep->nm_poli }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $resep->nm_dokter }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $resep->waktu_validasi }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $resep->waktu_penyerahan }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{
                                                $resep->waktu_penyerahan && $resep->waktu_validasi
                                                    ? carbon_immutable($resep->waktu_validasi)
                                                        ->diff(carbon($resep->waktu_penyerahan))
                                                        ->format('%R %H:%I:%S')
                                                    : null
                                            }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ rp($resep->total) }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $resep->jumlah }}
                                        </x-table.td>
                                    </x-table.tr>
                                @empty
                                    <x-table.tr-empty colspan="13" padding />
                                @endforelse
                            </x-slot>
                        </x-table>
                        <x-paginator class="px-4 py-3 bg-light" :data="$this->dataKunjunganResepObatRacikan" />
                    </x-navtabs.content>
                </x-slot>
            </x-navtabs>
        </x-slot>
    </x-card>
</div>
