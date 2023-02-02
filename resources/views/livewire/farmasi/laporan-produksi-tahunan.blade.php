<div>
    <x-flash />

    <x-card>
        <x-slot name="header">
            <x-card.row-col>
                <x-filter.label>Tahun:</x-filter.label>
                <div class="input-group input-group-sm ml-2" style="width: 5rem">
                    <x-filter.select model="tahun" :options="$this->dataTahun" constant-width />
                </div>
                <x-filter.button-search class="ml-2" title="Refresh" method="$refresh" icon="fas fa-sync-alt" />
                <x-filter.button-export-excel class="ml-auto" />
            </x-card.row-col>
        </x-slot>
        <x-slot name="body" class="table-responsive">
            <x-table style="width: 150rem">
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
                        @foreach ($this->kunjunganTotal as $item)
                            <th class="text-center px-0" scope="col" width="150">{{ $item }}</th>
                        @endforeach
                    </x-table.tr>
                    <x-table.tr>
                        <th scope="row">Kunjungan Rawat Jalan</th>
                        @foreach ($this->kunjunganRalan as $item)
                            <td class="text-center px-0" width="150">{{ $item }}</td>
                        @endforeach
                    </x-table.tr>
                    <x-table.tr>
                        <th scope="row">Kunjungan Rawat Inap</th>
                        @foreach ($this->kunjunganRanap as $item)
                            <td class="text-center px-0" width="150">{{ $item }}</td>
                        @endforeach
                    </x-table.tr>
                    <x-table.tr>
                        <th>Kunjungan IGD</th>
                        @foreach ($this->kunjunganIGD as $item)
                            <td class="text-center px-0" width="150">{{ $item }}</td>
                        @endforeach
                    </x-table.tr>
                    <x-table.tr>
                        <th>Kunjungan <i>Walk in</i></th>
                        @foreach ($this->kunjunganWalkIn as $item)
                            <td class="text-center px-0" width="150">{{ $item }}</td>
                        @endforeach
                    </x-table.tr>
                    <x-table.tr>
                        <th>TOTAL PENDAPATAN</th>
                        @foreach ($this->pendapatanObatTotal as $item)
                            <th class="text-center px-0" width="150">{{ rp($item) }}</th>
                        @endforeach
                    </x-table.tr>
                    <x-table.tr>
                        <th>Pendapatan Obat Rawat Jalan</th>
                        @foreach ($this->pendapatanObatRalan as $item)
                            <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                        @endforeach
                    </x-table.tr>
                    <x-table.tr>
                        <th>Pendapatan Obat Rawat Inap</th>
                        @foreach ($this->pendapatanObatRanap as $item)
                            <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                        @endforeach
                    </x-table.tr>
                    <x-table.tr>
                        <th>Pendapatan Obat IGD</th>
                        @foreach ($this->pendapatanObatIGD as $item)
                            <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                        @endforeach
                    </x-table.tr>
                    <x-table.tr>
                        <th>Pendapatan Obat <i>Walk in</i></th>
                        @foreach ($this->pendapatanObatWalkIn as $item)
                            <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                        @endforeach
                    </x-table.tr>
                    <x-table.tr>
                        <th>Pendapatan Alkes Farmasi dan Unit</th>
                        @foreach ($this->pendapatanAlkesFarmasiDanUnit as $item)
                            <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                        @endforeach
                    </x-table.tr>
                    <x-table.tr>
                        <th>Retur Obat</th>
                        @foreach ($this->returObat as $item)
                            <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                        @endforeach
                    </x-table.tr>
                    <x-table.tr>
                        <th>Pembelian Farmasi</th>
                        @foreach ($this->pembelianFarmasi as $item)
                            <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                        @endforeach
                    </x-table.tr>
                    <x-table.tr>
                        <th>Retur Supplier</th>
                        @foreach ($this->returSupplier as $item)
                            <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                        @endforeach
                    </x-table.tr>
                    <x-table.tr>
                        <th>TOTAL PEMBELIAN<br> (<i>Pembelian Farmasi - Retur Supplier</i>)</th>
                        @foreach ($this->totalBersihPembelianFarmasi as $item)
                            <th class="text-center px-0" width="150">{{ rp($item) }}</th>
                        @endforeach
                    </x-table.tr>
                    <x-table.tr>
                        <th>Pemakaian BHP</th>
                        @foreach ($this->stokKeluarMedis as $item)
                            <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                        @endforeach
                    </x-table.tr>
                    <x-table.tr>
                        <th>Transfer Order</th>
                        @foreach ($this->transferOrder as $item)
                            <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                        @endforeach
                    </x-table.tr>
                </x-slot>
            </x-table>
        </x-slot>
    </x-card>
</div>
