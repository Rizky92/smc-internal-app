<div>
    <x-flash />

    <x-card>
        <x-slot name="header">
            <x-card.row-col>
                <x-filter.label>Tahun:</x-filter.label>
                <div class="input-group input-group-sm ml-2" style="width: 5rem">
                    @php
                        $year = collect(range((int) now()->format('Y'), 2022, -1));
                        
                        $year = $year
                            ->mapWithKeys(function ($value, $key) {
                                return [$value => $value];
                            })
                            ->toArray();
                    @endphp
                    <x-filter.select model="tahun" :options="$year" constant-width />
                </div>
                <x-filter.button-search class="ml-2" title="Refresh" method="$refresh" icon="fas fa-sync-alt" />
                <x-filter.button-export-excel class="ml-auto" />
            </x-card.row-col>
        </x-slot>
        <x-slot name="body" class="table-responsive">
            <x-table style="width: 150rem">
                <x-slot name="columns">
                    @php($bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'])
                    <x-table.th width="250">Laporan</x-table.th>
                    @foreach ($bulan as $b)
                        <x-table.th class="text-center px-0" width="150">{{ $b }}</x-table.th>
                    @endforeach
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
                        @foreach ($this->kunjunganIgd as $item)
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
                        @foreach ($this->mutasiObatDariFarmasi as $item)
                            <td class="text-center px-0" width="150">{{ rp($item) }}</td>
                        @endforeach
                    </tr>
                </x-slot>
            </x-table>
        </x-slot>
    </x-card>
</div>
