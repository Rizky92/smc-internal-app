<div>
    @include('layouts.components.flash')

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex align-items-center justify-content-start">
                        <span class="text-sm pr-4">Periode:</span>
                        <input class="form-control form-control-sm w-25" type="date" wire:model.defer="periodeAwal" />
                        <span class="text-sm px-2">sampai</span>
                        <input class="form-control form-control-sm w-25" type="date" wire:model.defer="periodeAkhir" />
                        <div class="ml-auto">
                            <button class="btn btn-default btn-sm" type="button" wire:click="exportToExcel">
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
                            <input class="form-control" type="search" wire:model.defer="cari" placeholder="Cari..." wire:keydown.enter.stop="searchData" />
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
        <div class="card-body table-responsive p-0 border-top">
            <table class="table table-hover table-striped table-sm text-sm">
                <thead>
                    <tr>
                        <th>No. Resep</th>
                        <th>Tgl. Validasi</th>
                        <th>Jam</th>
                        <th>Nama Obat</th>
                        <th>Jumlah</th>
                        <th>Dokter Peresep</th>
                        <th>Asal</th>
                        <th>Asal Poli</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->obatPerDokter as $obat)
                        <tr>
                            <td>{{ $obat->no_resep }}</td>
                            <td>{{ $obat->tgl_perawatan }}</td>
                            <td>{{ $obat->jam }}</td>
                            <td>{{ $obat->nama_brng }}</td>
                            <td>{{ $obat->jml }}</td>
                            <td>{{ $obat->nm_dokter }}</td>
                            <td>{{ $obat->status }}</td>
                            <td>{{ $obat->nm_poli }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <div class="d-flex align-items center justify-content-start">
                <p class="text-muted">Menampilkan {{ $this->obatPerDokter->count() }} dari total {{ number_format($this->obatPerDokter->total(), 0, ',', '.') }} item.</p>
                <div class="ml-auto">
                    {{ $this->obatPerDokter->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
