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
                <div class="col-12">
                    <div class="d-flex align-items-center justify-content-start">
                        <span class="text-sm pr-2">Tampilkan:</span>
                        <div class="input-group input-group-sm" style="width: 4rem">
                            <select class="custom-control custom-select" name="perpage" wire:model.defer="perpage">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="200">200</option>
                                <option value="500">500</option>
                                <option value="1000">1000</option>
                            </select>
                        </div>
                        <span class="text-sm pl-2">per halaman</span>
                        <span class="text-sm ml-auto">Jenis Perawatan:</span>
                        <select class="ml-2 form-control form-control-sm" wire:model.defer="jenisPerawatan" style="width: 8rem">
                            <option value="-">Semua</option>
                            <option value="ralan">Rawat Jalan</option>
                            <option value="ranap">Rawat Inap</option>
                        </select>
                        <div class="ml-2">
                            <button class="btn btn-sm btn-default" type="button" wire:click="searchData">
                                <i class="fas fa-sync-alt"></i>
                                <span class="ml-1">Refresh</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <ul class="nav nav-tabs nav-fill border-bottom-0" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="tab-ralan" data-toggle="pill" href="#content-obat-regular" role="tab" aria-controls="content-obat-regular" aria-selected="false" wire:ignore>
                        <span>Obat Umum</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-ranap" data-toggle="pill" href="#content-obat-racikan" role="tab" aria-controls="content-obat-racikan" aria-selected="false" wire:ignore>
                        <span>Obat Racikan</span>
                    </a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane show active" id="content-obat-regular" role="tabpanel" aria-label="Tab Obat Umum" wire:ignore.self>
                    <div>
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
                    </div>
                </div>
                <div class="tab-pane" id="content-obat-racikan" role="tabpanel" aria-label="Tab Obat Racikan" wire:ignore.self>
                    <div>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
