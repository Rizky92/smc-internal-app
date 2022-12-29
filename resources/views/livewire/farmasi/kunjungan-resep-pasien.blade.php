<div>
    <x-flash />

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex align-items-center justify-content-start">
                        <span class="text-sm pr-4">Periode:</span>
                        <input class="form-control form-control-sm" style="width: 10rem" type="date" wire:model.defer="periodeAwal" />
                        <span class="text-sm px-2">sampai</span>
                        <input class="form-control form-control-sm" style="width: 10rem" type="date" wire:model.defer="periodeAkhir" />
                        <div class="ml-auto">
                            <button class="ml-auto btn btn-default btn-sm" type="button" wire:click="exportToExcel">
                                <i class="fas fa-file-excel"></i>
                                <span class="ml-1">Export ke Excel</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <x-filter>
                    <x-slot name="replaceSearch">
                        <div class="ml-2 d-flex align-items-center">
                            <span class="text-sm">Jenis Perawatan:</span>
                            <select class="ml-2 form-control form-control-sm" wire:model.defer="jenisPerawatan" style="width: 8rem">
                                <option value="-">Semua</option>
                                <option value="ralan">Rawat Jalan</option>
                                <option value="ranap">Rawat Inap</option>
                            </select>
                            <button class="ml-2 btn btn-sm btn-default" type="button" wire:click="searchData">
                                <i class="fas fa-sync-alt"></i>
                                <span class="ml-1">Refresh</span>
                            </button>
                        </div>
                    </x-slot>
                </x-filter>
            </div>
        </div>
        <div class="card-body p-0">
            <x-navtabs :livewire="true">
                <x-slot name="tabs">
                    <x-navtabs.tab id="obat-regular" title="Obat Regular" selected />
                    <x-navtabs.tab id="obat-racikan" title="Obat Racikan" />
                </x-slot>
                <x-slot name="contents">
                    <x-navtabs.content id="obat-regular" title="Obat Regular" selected>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-sm text-sm m-0">
                                <thead>
                                    <tr>
                                        <th>No. Resep</th>
                                        <th>Dokter Peresep</th>
                                        <th>Tgl. Validasi</th>
                                        <th>Jam</th>
                                        <th>Pasien</th>
                                        <th>Jenis Perawatan</th>
                                        <th>Total Pembelian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($this->kunjunganResepObatRegularPasien as $resep)
                                        <tr>
                                            <td>{{ $resep->no_resep }}</td>
                                            <td>{{ $resep->nm_dokter }}</td>
                                            <td>{{ $resep->tgl_perawatan }}</td>
                                            <td>{{ $resep->jam }}</td>
                                            <td>{{ $resep->nm_pasien }}</td>
                                            <td>{{ $resep->status_lanjut }}</td>
                                            <td>{{ rp($resep->total) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="px-4 pt-3 pb-2 d-flex align-items-center justify-content-start bg-light">
                            <p class="text-muted">Menampilkan {{ $this->kunjunganResepObatRegularPasien->count() }} dari total {{ number_format($this->kunjunganResepObatRegularPasien->total(), 0, ',', '.') }} item.</p>
                            <div class="ml-auto">
                                {{ $this->kunjunganResepObatRegularPasien->links() }}
                            </div>
                        </div>
                    </x-navtabs.content>
                    <x-navtabs.content id="obat-racikan" title="Obat Racikan">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-sm text-sm m-0">
                                <thead>
                                    <tr>
                                        <th>No. Resep</th>
                                        <th>Dokter Peresep</th>
                                        <th>Tgl. Validasi</th>
                                        <th>Jam</th>
                                        <th>Pasien</th>
                                        <th>Jenis Perawatan</th>
                                        <th>Total Pembelian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($this->kunjunganResepObatRacikanPasien as $resep)
                                        <tr>
                                            <td>{{ $resep->no_resep }}</td>
                                            <td>{{ $resep->nm_dokter }}</td>
                                            <td>{{ $resep->tgl_perawatan }}</td>
                                            <td>{{ $resep->jam }}</td>
                                            <td>{{ $resep->nm_pasien }}</td>
                                            <td>{{ $resep->status_lanjut }}</td>
                                            <td>{{ rp($resep->total) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="px-4 pt-3 pb-2 d-flex align-items-center justify-content-start bg-light">
                            <p class="text-muted">Menampilkan {{ $this->kunjunganResepObatRacikanPasien->count() }} dari total {{ number_format($this->kunjunganResepObatRacikanPasien->total(), 0, ',', '.') }} item.</p>
                            <div class="ml-auto">
                                {{ $this->kunjunganResepObatRacikanPasien->links() }}
                            </div>
                        </div>
                    </x-navtabs.content>
                </x-slot>
            </x-navtabs>
        </div>
    </div>
</div>
