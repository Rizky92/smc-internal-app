<div wire:init="loadProperties">
    <x-flash />

    <x-card use-loading>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.label class="pr-3">Tahun :</x-filter.label>
                <x-filter.select model="tahun" :options="$this->dataTahun" />
                <x-filter.button-refresh class="ml-2" />
                <x-filter.button-export-excel class="ml-auto" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table style="width: 150rem" zebra hover sticky nowrap>
                <x-slot name="columns">
                    <x-table.th width="250" title="Laporan" />
                    <x-table.th class="text-center px-0" width="150" title="Januari" />
                    <x-table.th class="text-center px-0" width="150" title="Februari" />
                    <x-table.th class="text-center px-0" width="150" title="Maret" />
                    <x-table.th class="text-center px-0" width="150" title="April" />
                    <x-table.th class="text-center px-0" width="150" title="Mei" />
                    <x-table.th class="text-center px-0" width="150" title="Juni" />
                    <x-table.th class="text-center px-0" width="150" title="Juli" />
                    <x-table.th class="text-center px-0" width="150" title="Agustus" />
                    <x-table.th class="text-center px-0" width="150" title="September" />
                    <x-table.th class="text-center px-0" width="150" title="Oktober" />
                    <x-table.th class="text-center px-0" width="150" title="November" />
                    <x-table.th class="text-center px-0" width="150" title="Desember" />
                </x-slot>
                <x-slot name="body">
                    <x-table.tr>
                        <th scope="row">TOTAL KUNJUNGAN</th>
                        @forelse ($this->kunjunganTotal as $item)
                            <th class="text-center px-0" scope="col" width="150">
                                {{ $item }}
                            </th>
                        @empty
                            <x-table.td-empty colspan="12" />
                        @endforelse
                    </x-table.tr>
                    <x-table.tr>
                        <th scope="row">Kunjungan Rawat Jalan</th>
                        @forelse ($this->kunjunganRalan as $item)
                            <td class="text-center px-0" width="150">
                                {{ $item }}
                            </td>
                        @empty
                            <x-table.td-empty colspan="12" />
                        @endforelse
                    </x-table.tr>
                    <x-table.tr>
                        <th scope="row">Kunjungan Rawat Inap</th>
                        @forelse ($this->kunjunganRanap as $item)
                            <td class="text-center px-0" width="150">
                                {{ $item }}
                            </td>
                        @empty
                            <x-table.td-empty colspan="12" />
                        @endforelse
                    </x-table.tr>
                    <x-table.tr>
                        <th>Kunjungan IGD</th>
                        @forelse ($this->kunjunganIGD as $item)
                            <td class="text-center px-0" width="150">
                                {{ $item }}
                            </td>
                        @empty
                            <x-table.td-empty colspan="12" />
                        @endforelse
                    </x-table.tr>
                    <x-table.tr>
                        <th>
                            Kunjungan
                            <i>Walk in</i>
                        </th>
                        @forelse ($this->kunjunganWalkIn as $item)
                            <td class="text-center px-0" width="150">
                                {{ $item }}
                            </td>
                        @empty
                            <x-table.td-empty colspan="12" />
                        @endforelse
                    </x-table.tr>
                    <x-table.tr>
                        <th>TOTAL PENDAPATAN</th>
                        @forelse ($this->pendapatanObatTotal as $item)
                            <th class="text-center px-0" width="150">
                                {{ rp($item) }}
                            </th>
                        @empty
                            <x-table.td-empty colspan="12" />
                        @endforelse
                    </x-table.tr>
                    <x-table.tr>
                        <th>Pendapatan Obat Rawat Jalan</th>
                        @forelse ($this->pendapatanObatRalan as $item)
                            <td class="text-center px-0" width="150">
                                {{ rp($item) }}
                            </td>
                        @empty
                            <x-table.td-empty colspan="12" />
                        @endforelse
                    </x-table.tr>
                    <x-table.tr>
                        <th>Pendapatan Obat Rawat Inap</th>
                        @forelse ($this->pendapatanObatRanap as $item)
                            <td class="text-center px-0" width="150">
                                {{ rp($item) }}
                            </td>
                        @empty
                            <x-table.td-empty colspan="12" />
                        @endforelse
                    </x-table.tr>
                    <x-table.tr>
                        <th>Pendapatan Obat IGD</th>
                        @forelse ($this->pendapatanObatIGD as $item)
                            <td class="text-center px-0" width="150">
                                {{ rp($item) }}
                            </td>
                        @empty
                            <x-table.td-empty colspan="12" />
                        @endforelse
                    </x-table.tr>
                    <x-table.tr>
                        <th>
                            Pendapatan Obat
                            <i>Walk in</i>
                        </th>
                        @forelse ($this->pendapatanObatWalkIn as $item)
                            <td class="text-center px-0" width="150">
                                {{ rp($item) }}
                            </td>
                        @empty
                            <x-table.td-empty colspan="12" />
                        @endforelse
                    </x-table.tr>
                    <x-table.tr>
                        <th>Pendapatan Alkes Farmasi dan Unit</th>
                        @forelse ($this->pendapatanAlkesFarmasiDanUnit as $item)
                            <td class="text-center px-0" width="150">
                                {{ rp($item) }}
                            </td>
                        @empty
                            <x-table.td-empty colspan="12" />
                        @endforelse
                    </x-table.tr>
                    <x-table.tr>
                        <th>Retur Obat</th>
                        @forelse ($this->returObat as $item)
                            <td class="text-center px-0" width="150">
                                {{ rp($item) }}
                            </td>
                        @empty
                            <x-table.td-empty colspan="12" />
                        @endforelse
                    </x-table.tr>
                    <x-table.tr>
                        <th>Pembelian Farmasi</th>
                        @forelse ($this->pembelianFarmasi as $item)
                            <td class="text-center px-0" width="150">
                                {{ rp($item) }}
                            </td>
                        @empty
                            <x-table.td-empty colspan="12" />
                        @endforelse
                    </x-table.tr>
                    <x-table.tr>
                        <th>Retur Supplier</th>
                        @forelse ($this->returSupplier as $item)
                            <td class="text-center px-0" width="150">
                                {{ rp($item) }}
                            </td>
                        @empty
                            <x-table.td-empty colspan="12" />
                        @endforelse
                    </x-table.tr>
                    <x-table.tr>
                        <th>
                            TOTAL PEMBELIAN
                            <br />
                            (
                            <i>Pembelian Farmasi - Retur Supplier</i>
                            )
                        </th>
                        @forelse ($this->totalBersihPembelianFarmasi as $item)
                            <th class="text-center px-0" width="150">
                                {{ rp($item) }}
                            </th>
                        @empty
                            <x-table.td-empty colspan="12" />
                        @endforelse
                    </x-table.tr>
                    <x-table.tr>
                        <th>Pemakaian BHP</th>
                        @forelse ($this->stokKeluarMedis as $item)
                            <td class="text-center px-0" width="150">
                                {{ rp($item) }}
                            </td>
                        @empty
                            <x-table.td-empty colspan="12" />
                        @endforelse
                    </x-table.tr>
                    <x-table.tr>
                        <th>Transfer Order</th>
                        @forelse ($this->transferOrder as $item)
                            <td class="text-center px-0" width="150">
                                {{ rp($item) }}
                            </td>
                        @empty
                            <x-table.td-empty colspan="12" />
                        @endforelse
                    </x-table.tr>
                </x-slot>
            </x-table>
        </x-slot>
    </x-card>
</div>
