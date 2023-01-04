<div>
    <x-flash />

    <x-card :filter="false">
        <x-slot name="header">
            <x-card.tools>
                <x-card.tools.date-range />
                <x-card.tools.export-to-excel class="ml-auto" />
            </x-card.tools>
            <x-card.tools class="mt-2">
                <x-card.tools.perpage />
                <x-card.tools.reset-filters class="ml-auto" />
                <div class="ml-2 input-group input-group-sm" style="width: 16rem">
                    <select class="form-control form-control-sm" wire:model.defer="jenisPerawatan">
                        <option value="" disabled selected>--JENIS PERAWATAN--</option>
                        <option value="">Semua</option>
                        <option value="ralan">Rawat Jalan</option>
                        <option value="ranap">Rawat Inap</option>
                    </select>
                    <div class="input-group-append">
                        <button class="btn btn-sm btn-default" type="button" wire:click="searchData">
                            <i class="fas fa-sync-alt"></i>
                            <span class="ml-1">Refresh</span>
                        </button>
                    </div>
                </div>
            </x-card.tools>
        </x-slot>
        <x-slot name="body">
            <x-navtabs :livewire="true">
                <x-slot name="tabs">
                    <x-navtabs.tab id="obat-regular" title="Obat Regular" selected />
                    <x-navtabs.tab id="obat-racikan" title="Obat Racikan" />
                </x-slot>
                <x-slot name="contents">
                    <x-navtabs.content id="obat-regular" title="Obat Regular" selected>
                        <div class="table-responsive">
                            <x-card.table class="mb-0">
                                <x-slot name="columns">
                                    <x-card.table.th>No. Resep</x-card.table.th>
                                    <x-card.table.th>Dokter Peresep</x-card.table.th>
                                    <x-card.table.th>Tgl. Validasi</x-card.table.th>
                                    <x-card.table.th>Jam</x-card.table.th>
                                    <x-card.table.th>Pasien</x-card.table.th>
                                    <x-card.table.th>Jenis Perawatan</x-card.table.th>
                                    <x-card.table.th>Total Pembelian</x-card.table.th>
                                </x-slot>
                                <x-slot name="body">
                                    @foreach ($this->kunjunganResepObatRegularPasien as $resep)
                                        <x-card.table.tr>
                                            <x-card.table.td>{{ $resep->no_resep }}</x-card.table.td>
                                            <x-card.table.td>{{ $resep->nm_dokter }}</x-card.table.td>
                                            <x-card.table.td>{{ $resep->tgl_perawatan }}</x-card.table.td>
                                            <x-card.table.td>{{ $resep->jam }}</x-card.table.td>
                                            <x-card.table.td>{{ $resep->nm_pasien }}</x-card.table.td>
                                            <x-card.table.td>{{ $resep->status_lanjut }}</x-card.table.td>
                                            <x-card.table.td>{{ rp($resep->total) }}</x-card.table.td>
                                        </x-card.table.tr>
                                    @endforeach
                                </x-slot>
                            </x-card.table>
                        </div>
                        <x-card.paginator class="px-4 pt-3 pb-2 bg-light" :count="$this->kunjunganResepObatRegularPasien->count()" :total="$this->kunjunganResepObatRegularPasien->total()">
                            <x-slot name="links">{{ $this->kunjunganResepObatRegularPasien->links() }}</x-slot>
                        </x-card.paginator>
                    </x-navtabs.content>
                    <x-navtabs.content id="obat-racikan" title="Obat Racikan">
                        <div class="table-responsive">
                            <x-card.table class="mb-0">
                                <x-slot name="columns">
                                    <x-card.table.th>No. Resep</x-card.table.th>
                                    <x-card.table.th>Dokter Peresep</x-card.table.th>
                                    <x-card.table.th>Tgl. Validasi</x-card.table.th>
                                    <x-card.table.th>Jam</x-card.table.th>
                                    <x-card.table.th>Pasien</x-card.table.th>
                                    <x-card.table.th>Jenis Perawatan</x-card.table.th>
                                    <x-card.table.th>Total Pembelian</x-card.table.th>
                                </x-slot>
                                <x-slot name="body">
                                    @foreach ($this->kunjunganResepObatRacikanPasien as $resep)
                                        <x-card.table.tr>
                                            <x-card.table.td>{{ $resep->no_resep }}</x-card.table.td>
                                            <x-card.table.td>{{ $resep->nm_dokter }}</x-card.table.td>
                                            <x-card.table.td>{{ $resep->tgl_perawatan }}</x-card.table.td>
                                            <x-card.table.td>{{ $resep->jam }}</x-card.table.td>
                                            <x-card.table.td>{{ $resep->nm_pasien }}</x-card.table.td>
                                            <x-card.table.td>{{ $resep->status_lanjut }}</x-card.table.td>
                                            <x-card.table.td>{{ rp($resep->total) }}</x-card.table.td>
                                        </x-card.table.tr>
                                    @endforeach
                                </x-slot>
                            </x-card.table>
                        </div>
                        <x-card.paginator class="px-4 pt-3 pb-2 bg-light" :count="$this->kunjunganResepObatRacikanPasien->count()" :total="$this->kunjunganResepObatRacikanPasien->total()">
                            <x-slot name="links">{{ $this->kunjunganResepObatRacikanPasien->links() }}</x-slot>
                        </x-card.paginator>
                    </x-navtabs.content>
                </x-slot>
            </x-navtabs>
        </x-slot>
    </x-card>
</div>
