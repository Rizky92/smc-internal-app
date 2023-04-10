<div>
    <x-flash />

    <x-card>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.range-date />
                <x-filter.button-export-excel class="ml-auto" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                <x-filter.select-perpage />
                <x-filter.label class="ml-auto pr-3">Jenis Perawatan:</x-filter.label>
                <x-filter.select model="jenisPerawatan" :options="[
                    '-' => 'Semua',
                    'ralan' => 'Rawat Jalan',
                    'ranap' => 'Rawat Inap',
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
                        <x-table class="mb-0" :sortColumns="$sortColumns" sortable zebra hover sticky>
                            <x-slot name="columns">
                                <x-table.th style="width: 20ch" name="no_resep" title="No. Resep" />
                                <x-table.th style="width: 50ch" name="nm_dokter" title="Dokter Peresep" />
                                <x-table.th style="width: 20ch" name="tgl_perawatan" title="Tgl. Validasi" />
                                <x-table.th style="width: 15ch" name="jam" title="Jam" />
                                <x-table.th style="width: 50ch" name="nm_pasien" title="Pasien" />
                                <x-table.th style="width: 15ch" name="status_lanjut" title="Jenis Perawatan" />
                                <x-table.th style="width: 20ch" name="total" title="Total Pembelian" />
                            </x-slot>
                            <x-slot name="body">
                                @forelse ($this->dataKunjunganResepObatRegular as $resep)
                                    <x-table.tr>
                                        <x-table.td>{{ $resep->no_resep }}</x-table.td>
                                        <x-table.td>{{ $resep->nm_dokter }}</x-table.td>
                                        <x-table.td>{{ $resep->tgl_perawatan }}</x-table.td>
                                        <x-table.td>{{ $resep->jam }}</x-table.td>
                                        <x-table.td>{{ $resep->nm_pasien }}</x-table.td>
                                        <x-table.td>{{ $resep->status_lanjut }}</x-table.td>
                                        <x-table.td>{{ rp($resep->total) }}</x-table.td>
                                    </x-table.tr>
                                @empty
                                    <x-table.tr-empty :colspan="7" />
                                @endforelse
                            </x-slot>
                        </x-table>
                        <x-paginator class="px-4 py-3 bg-light" :data="$this->dataKunjunganResepObatRegular" />
                    </x-navtabs.content>
                    <x-navtabs.content id="obat-racikan">
                        <x-table class="mb-0" :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                            <x-slot name="columns">
                                <x-table.th style="width: 20ch" name="no_resep" title="No. Resep" />
                                <x-table.th style="width: 50ch" name="nm_dokter" title="Dokter Peresep" />
                                <x-table.th style="width: 20ch" name="tgl_perawatan" title="Tgl. Validasi" />
                                <x-table.th style="width: 15ch" name="jam" title="Jam" />
                                <x-table.th style="width: 50ch" name="nm_pasien" title="Pasien" />
                                <x-table.th style="width: 15ch" name="status_lanjut" title="Jenis Perawatan" />
                                <x-table.th style="width: 20ch" name="total" title="Total Pembelian" />
                            </x-slot>
                            <x-slot name="body">
                                @forelse ($this->dataKunjunganResepObatRacikan as $resep)
                                    <x-table.tr>
                                        <x-table.td>{{ $resep->no_resep }}</x-table.td>
                                        <x-table.td>{{ $resep->nm_dokter }}</x-table.td>
                                        <x-table.td>{{ $resep->tgl_perawatan }}</x-table.td>
                                        <x-table.td>{{ $resep->jam }}</x-table.td>
                                        <x-table.td>{{ $resep->nm_pasien }}</x-table.td>
                                        <x-table.td>{{ $resep->status_lanjut }}</x-table.td>
                                        <x-table.td>{{ rp($resep->total) }}</x-table.td>
                                    </x-table.tr>
                                @empty
                                    <x-table.tr-empty :colspan="7" />
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
