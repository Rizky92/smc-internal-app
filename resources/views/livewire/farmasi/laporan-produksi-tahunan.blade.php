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
                    <x-table.th width="250">Laporan</x-table.th>
                    <x-table.th class="text-center px-0" width="150">Januari</x-table.th>
                    <x-table.th class="text-center px-0" width="150">Februari</x-table.th>
                    <x-table.th class="text-center px-0" width="150">Maret</x-table.th>
                    <x-table.th class="text-center px-0" width="150">April</x-table.th>
                    <x-table.th class="text-center px-0" width="150">Mei</x-table.th>
                    <x-table.th class="text-center px-0" width="150">Juni</x-table.th>
                    <x-table.th class="text-center px-0" width="150">Juli</x-table.th>
                    <x-table.th class="text-center px-0" width="150">Agustus</x-table.th>
                    <x-table.th class="text-center px-0" width="150">September</x-table.th>
                    <x-table.th class="text-center px-0" width="150">Oktober</x-table.th>
                    <x-table.th class="text-center px-0" width="150">November</x-table.th>
                    <x-table.th class="text-center px-0" width="150">Desember</x-table.th>
                </x-slot>
                <x-slot name="body">
                    <tr>
                        <th scope="row" width="250">TOTAL KUNJUNGAN</th>
                        @foreach ($this->kunjunganTotal as $item)
                            <th class="text-center px-0" scope="col" width="150">{{ $item }}</th>
                        @endforeach
                    </tr>
                    <tr>
                        <th scope="row" width="250">Kunjungan Rawat Jalan</th>
                        @foreach ($this->kunjunganRalan as $item)
                            <td class="text-center px-0" width="150">{{ $item }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th scope="row" width="250">Kunjungan Rawat Inap</th>
                        @foreach ($this->kunjunganRanap as $item)
                            <td class="text-center px-0" width="150">{{ $item }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th>Kunjungan IGD</th>
                        @foreach ($this->kunjunganIGD as $item)
                            <td class="text-center px-0" width="150">{{ $item }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th width="250">Kunjungan <i>Walk in</i></th>
                        @foreach ($this->kunjunganWalkIn as $item)
                            <td class="text-center px-0" width="150">{{ $item }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th width="250">TOTAL PENDAPATAN</th>
                        @foreach ($this->pendapatanObatTotal as $item)
                            <th class="text-center px-0" width="150">{{ rp($item) }}</th>
                        @endforeach
                    </tr>
                    <tr>
                        <th width="250">Pendapatan Obat Rawat Jalan</th>
                        @foreach ($this->pendapatanObatRalan as $item)
                            <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th width="250">Pendapatan Obat Rawat Inap</th>
                        @foreach ($this->pendapatanObatRanap as $item)
                            <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th width="250">Pendapatan Obat IGD</th>
                        @foreach ($this->pendapatanObatIGD as $item)
                            <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th width="250">Pendapatan Obat <i>Walk in</i></th>
                        @foreach ($this->pendapatanObatWalkIn as $item)
                            <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th width="250">Pendapatan Alkes Farmasi dan Unit</th>
                        @foreach ($this->pendapatanAlkesFarmasiDanUnit as $item)
                            <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th width="250">Retur Obat</th>
                        @foreach ($this->returObat as $item)
                            <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th width="250">Pembelian Farmasi</th>
                        @foreach ($this->pembelianFarmasi as $item)
                            <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th width="250">Retur Supplier</th>
                        @foreach ($this->returSupplier as $item)
                            <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th width="250">TOTAL PEMBELIAN<br> (<i>Pembelian Farmasi - Retur Supplier</i>)</th>
                        @foreach ($this->totalBersihPembelianFarmasi as $item)
                            <th class="text-center px-0" width="150">{{ rp($item) }}</th>
                        @endforeach
                    </tr>
                    <tr>
                        <th width="250">Pemakaian BHP</th>
                        @foreach ($this->stokKeluarMedis as $item)
                            <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th width="250">Transfer Order</th>
                        @foreach ($this->transferOrder as $item)
                            <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                        @endforeach
                    </tr>
                </x-slot>
            </x-table>
        </x-slot>
    </x-card>
</div>
