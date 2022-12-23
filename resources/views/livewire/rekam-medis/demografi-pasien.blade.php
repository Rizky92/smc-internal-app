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
                        <div class="ml-auto input-group input-group-sm" style="width: 20rem">
                            <input class="form-control" type="search" wire:model.defer="cari" placeholder="Cari..." wire:keydown.enter="searchData" />
                            <div class="input-group-append">
                                <button class="btn btn-sm btn-default" type="button" wire:click="searchData">
                                    <i class="fas fa-sync-alt"></i>
                                    <span class="ml-1">Refresh</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
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
        </div>
        <div class="card-footer">
            <div class="px-4 pt-3 pb-2 d-flex align-items-center justify-content-start bg-light">
                <p class="text-muted">Menampilkan {{ $this->kunjunganResepObatRegularPasien->count() }} dari total {{ number_format($this->kunjunganResepObatRegularPasien->total(), 0, ',', '.') }} item.</p>
                <div class="ml-auto">
                    {{ $this->kunjunganResepObatRegularPasien->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
