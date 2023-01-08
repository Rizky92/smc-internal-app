<div>
    <x-flash />

    <x-card>
        <x-slot name="header">
            <x-card.row-col>
                <x-filter.range-date />
                <x-filter.button-export-excel class="ml-auto" />
            </x-card.row-col>
            <x-card.row-col class="mt-2">
                <x-filter.select-perpage />
                <x-filter.button-reset-filters class="ml-auto" />
                <div class="ml-2 input-group input-group-sm" style="width: max-content">
                    <x-filter.select model="jenisPerawatan" placeholder="--Jenis Perawatan--" :options="[
                        '-' => 'Semua',
                        'ralan' => 'Rawat Jalan',
                        'ranap' => 'Rawat Inap',
                    ]" />
                    <x-filter.button-search class="input-group-append" title="Refresh" icon="fas fa-sync-alt" />
                </div>
            </x-card.row-col>
        </x-slot>
        <x-slot name="body">
            <x-navtabs :livewire="true">
                <x-slot name="tabs">
                    <x-navtabs.tab id="obat-regular" title="Obat Regular" selected />
                    <x-navtabs.tab id="obat-racikan" title="Obat Racikan" />
                </x-slot>
                <x-slot name="contents">
                    <x-navtabs.content id="obat-regular" title="Obat Regular" selected class="table-responsive">
                        <x-table class="mb-0">
                            <x-slot name="columns">
                                <x-table.th>No. Resep</x-table.th>
                                <x-table.th>Dokter Peresep</x-table.th>
                                <x-table.th>Tgl. Validasi</x-table.th>
                                <x-table.th>Jam</x-table.th>
                                <x-table.th>Pasien</x-table.th>
                                <x-table.th>Jenis Perawatan</x-table.th>
                                <x-table.th>Total Pembelian</x-table.th>
                            </x-slot>
                            <x-slot name="body">
                                @foreach ($this->kunjunganResepObatRegularPasien as $resep)
                                    <x-table.tr>
                                        <x-table.td>{{ $resep->no_resep }}</x-table.td>
                                        <x-table.td>{{ $resep->nm_dokter }}</x-table.td>
                                        <x-table.td>{{ $resep->tgl_perawatan }}</x-table.td>
                                        <x-table.td>{{ $resep->jam }}</x-table.td>
                                        <x-table.td>{{ $resep->nm_pasien }}</x-table.td>
                                        <x-table.td>{{ $resep->status_lanjut }}</x-table.td>
                                        <x-table.td>{{ rp($resep->total) }}</x-table.td>
                                    </x-table.tr>
                                @endforeach
                            </x-slot>
                        </x-table>
                        <x-paginator class="px-4 py-3 bg-light" :data="$this->kunjunganResepObatRegularPasien" />
                    </x-navtabs.content>
                    <x-navtabs.content id="obat-racikan" title="Obat Racikan" class="table-responsive">
                        <x-table class="mb-0">
                            <x-slot name="columns">
                                <x-table.th>No. Resep</x-table.th>
                                <x-table.th>Dokter Peresep</x-table.th>
                                <x-table.th>Tgl. Validasi</x-table.th>
                                <x-table.th>Jam</x-table.th>
                                <x-table.th>Pasien</x-table.th>
                                <x-table.th>Jenis Perawatan</x-table.th>
                                <x-table.th>Total Pembelian</x-table.th>
                            </x-slot>
                            <x-slot name="body">
                                @foreach ($this->kunjunganResepObatRacikanPasien as $resep)
                                    <x-table.tr>
                                        <x-table.td>{{ $resep->no_resep }}</x-table.td>
                                        <x-table.td>{{ $resep->nm_dokter }}</x-table.td>
                                        <x-table.td>{{ $resep->tgl_perawatan }}</x-table.td>
                                        <x-table.td>{{ $resep->jam }}</x-table.td>
                                        <x-table.td>{{ $resep->nm_pasien }}</x-table.td>
                                        <x-table.td>{{ $resep->status_lanjut }}</x-table.td>
                                        <x-table.td>{{ rp($resep->total) }}</x-table.td>
                                    </x-table.tr>
                                @endforeach
                            </x-slot>
                        </x-table>
                        <x-paginator class="px-4 py-3 bg-light" :data="$this->kunjunganResepObatRacikanPasien" />
                    </x-navtabs.content>
                </x-slot>
            </x-navtabs>
        </x-slot>
    </x-card>
</div>
