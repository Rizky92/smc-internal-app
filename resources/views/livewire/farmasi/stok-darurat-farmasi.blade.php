<div>
    <x-flash />
    
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex align-items-center justify-content-start">
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
                            <select name="perpage" class="custom-control custom-select" wire:model.defer="perpage">
                                <option value="10">10</option>
                                <option value="25" selected>25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="200">200</option>
                                <option value="500">500</option>
                                <option value="1000">1000</option>
                            </select>
                        </div>
                        <span class="text-sm pl-2">per halaman</span>
                        <div class="ml-auto input-group input-group-sm" style="width: 20rem">
                            <input type="search" class="form-control" wire:model.defer="cari" placeholder="Cari..." wire:keydown.enter.stop="searchData" />
                            <div class="input-group-append">
                                <button type="button" wire:click="searchData" class="btn btn-sm btn-default">
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
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Satuan</th>
                        <th>Kategori</th>
                        <th>Stok minimal</th>
                        <th>Stok saat ini</th>
                        <th>Saran order</th>
                        <th>Supplier</th>
                        <th>Harga Per Unit</th>
                        <th>Total Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->stokDaruratObat as $obat)
                        <tr>
                            <td>{{ $obat->kode_brng }}</td>
                            <td>{{ $obat->nama_brng }}</td>
                            <td>{{ $obat->satuan_kecil }}</td>
                            <td>{{ $obat->kategori }}</td>
                            <td>{{ $obat->stokminimal }}</td>
                            <td>{{ $obat->stok_sekarang }}</td>
                            <td>{{ $obat->saran_order }}</td>
                            <td>{{ $obat->nama_industri }}</td>
                            <td>{{ rp($obat->harga_beli) }}</td>
                            <td>{{ rp($obat->harga_beli_total) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <div class="d-flex align-items center justify-content-start">
                <p class="text-muted">Menampilkan {{ $this->stokDaruratObat->count() }} dari total {{ number_format($this->stokDaruratObat->total(), 0, ',', '.') }} item.</p>
                <div class="ml-auto">
                    {{ $this->stokDaruratObat->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
