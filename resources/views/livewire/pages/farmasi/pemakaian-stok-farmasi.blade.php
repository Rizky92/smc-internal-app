<div wire:init="loadProperties">
    <x-flash />

    <x-card use-loading>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.button-export-excel class="ml-auto" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap style="width: 140rem">
                <x-slot name="columns">
                    <x-table.th name="kode_brng" title="Kode" style="width: 13ch" />
                    <x-table.th name="nama_brng" title="Nama" style="width: 50ch" />
                    <x-table.th name="satuan_kecil" title="Satuan" style="width: 12ch" />
                    <x-table.th name="kategori" title="Kategori" style="width: 25ch" />
                    <x-table.th name="stok_saat_ini" title="Stok Seluruh Depo Farmasi Saat Ini" align="right" style="width: 40ch" />
                    <x-table.th name="ke_pasien_14_hari" title="Jumlah Ke Pasien (14 Hari)" align="right" style="width: 40ch" />
                    <x-table.th name="pemakaian_1_minggu" title="Pemakaian 1 Minggu" align="right" style="width: 40ch" />
                    <x-table.th name="pemakaian_1_bulan" title="Pemakaian 1 Bulan" align="right" style="width: 40ch" />
                    <x-table.th name="pemakaian_3_bulan" title="Pemakaian 3 Bulan" align="right" style="width: 40ch" />
                    <x-table.th name="pemakaian_6_bulan" title="Pemakaian 6 Bulan" align="right" style="width: 40ch" />
                    <x-table.th name="pemakaian_10_bulan" title="Pemakaian 10 Bulan" align="right" style="width: 40ch" />
                    <x-table.th name="pemakaian_12_bulan" title="Pemakaian 12 Bulan" align="right" style="width: 40ch" />
                </x-slot>
                <x-slot name="body">
                    @forelse ($this->pemakaianStokObat as $obat)
                        <x-table.tr>
                            <x-table.td>{{ $obat->kode_brng }}</x-table.td>
                            <x-table.td>{{ $obat->nama_brng }}</x-table.td>
                            <x-table.td>
                                {{ $obat->satuan_kecil }}
                            </x-table.td>
                            <x-table.td>{{ $obat->kategori }}</x-table.td>
                            <x-table.td class="text-right">
                                {{ $obat->stok_saat_ini }}
                            </x-table.td>
                            <x-table.td class="text-right">
                                {{ $obat->ke_pasien_14_hari }}
                            </x-table.td>
                            <x-table.td class="text-right">
                                {{ $obat->pemakaian_1_minggu }}
                            </x-table.td>
                            <x-table.td class="text-right">
                                {{ $obat->pemakaian_1_bulan }}
                            </x-table.td>
                            <x-table.td class="text-right">
                                {{ $obat->pemakaian_3_bulan }}
                            </x-table.td>
                            <x-table.td class="text-right">
                                {{ $obat->pemakaian_6_bulan }}
                            </x-table.td>
                            <x-table.td class="text-right">
                                {{ $obat->pemakaian_10_bulan }}
                            </x-table.td>
                            <x-table.td class="text-right">
                                {{ $obat->pemakaian_12_bulan }}
                            </x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr-empty colspan="25" padding />
                    @endforelse
                </x-slot>
            </x-table>
        </x-slot>
        <x-slot name="footer">
            <x-paginator :data="$this->pemakaianStokObat" />
        </x-slot>
    </x-card>
</div>
