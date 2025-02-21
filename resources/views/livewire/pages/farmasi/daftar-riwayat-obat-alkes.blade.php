<div>
    <x-flash />

    <x-card use-loading>
        <x-slot name="header">
            <x-row-col-flex>
                <x-filter.range-date />
                <x-filter.toggle class="ml-3" model="barangNol" title="Tampilkan Barang Nol" />
                <x-filter.button-export-excel class="ml-auto" />
            </x-row-col-flex>
            <x-row-col-flex class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search class="ml-2" />
            </x-row-col-flex>
        </x-slot>
        <x-slot name="body">
            <x-navtabs livewire selected="obat">
                <x-slot name="tabs">
                    <x-navtabs.tab id="obat" title="Obat" />
                    <x-navtabs.tab id="alkes" title="Alkes" />
                </x-slot>
                <x-slot name="contents">
                    <x-navtabs.content id="obat">
                        <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                            <x-slot name="columns">
                                <x-table.th title="Kode Item" />
                                <x-table.th title="Nama Item" />
                                <x-table.th title="Stok Saat Ini" />
                                <x-table.th title="Order Terakhir" />
                                <x-table.th title="Penggunaan Terakhir" />
                                <x-table.th name="tanggal_order_terakhir" title="Tanggal Order Terakhir" />
                                <x-table.th name="tanggal_penggunaan_terakhir" title="Tanggal Penggunaan Terakhir" />
                                <x-table.th title="Posisi Order Terakhir" />
                                <x-table.th title="Posisi Penggunaan Terakhir" />
                            </x-slot>
                            <x-slot name="body">
                                @forelse ($this->dataRiwayatObat as $item)
                                    <x-table.tr>
                                        <x-table.td>
                                            {{ $item->kode_brng }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->nama_brng }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->stok_akhir }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->order_terakhir }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->penggunaan_terakhir }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->tanggal_order_terakhir }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->tanggal_penggunaan_terakhir }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->posisi_order_terakhir }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->posisi_penggunaan_terakhir }}
                                        </x-table.td>
                                    </x-table.tr>
                                @empty
                                    <x-table.tr-empty colspan="9" padding />
                                @endforelse
                            </x-slot>
                        </x-table>
                        <x-paginator class="px-4 py-3 bg-light border-top" :data="$this->dataRiwayatObat" />
                    </x-navtabs.content>
                    <x-navtabs.content id="alkes">
                        <x-table :sortColumns="$sortColumns" sortable zebra hover sticky nowrap>
                            <x-slot name="columns">
                                <x-table.th title="Kode Item" />
                                <x-table.th title="Nama Item" />
                                <x-table.th title="Stok Saat Ini" />
                                <x-table.th title="Order Terakhir" />
                                <x-table.th title="Penggunaan Terakhir" />
                                <x-table.th name="tanggal_order_terakhir" title="Tanggal Order Terakhir" />
                                <x-table.th name="tanggal_penggunaan_terakhir" title="Tanggal Penggunaan Terakhir" />
                                <x-table.th title="Posisi Order Terakhir" />
                                <x-table.th title="Posisi Penggunaan Terakhir" />
                            </x-slot>
                            <x-slot name="body">
                                @forelse ($this->dataRiwayatAlkes as $item)
                                    <x-table.tr>
                                        <x-table.td>
                                            {{ $item->kode_brng }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->nama_brng }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->stok_akhir }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->order_terakhir }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->penggunaan_terakhir }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->tanggal_order_terakhir }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->tanggal_penggunaan_terakhir }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->posisi_order_terakhir }}
                                        </x-table.td>
                                        <x-table.td>
                                            {{ $item->posisi_penggunaan_terakhir }}
                                        </x-table.td>
                                    </x-table.tr>
                                @empty
                                    <x-table.tr-empty colspan="9" padding />
                                @endforelse
                            </x-slot>
                        </x-table>
                        <x-paginator class="px-4 py-3 bg-light border-top" :data="$this->dataRiwayatAlkes" />
                    </x-navtabs.content>
                </x-slot>
            </x-navtabs>
        </x-slot>
    </x-card>
</div>
