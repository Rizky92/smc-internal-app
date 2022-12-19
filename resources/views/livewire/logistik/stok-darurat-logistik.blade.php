<div>
    @if (session()->has('excel.exporting'))
        <div class="alert alert-dark alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <p>
                {{ session('excel.exporting') }}
            </p>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex align-items-center justify-content-start">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="tampilkanSaranOrderNol" wire:model.defer="tampilkanSaranOrderNol">
                            <label class="custom-control-label text-sm" for="tampilkanSaranOrderNol">Tampilkan barang dengan saran order nol</label>
                        </div>
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
                            <input type="search" class="form-control" wire:model.defer="cari" placeholder="Cari..." wire:keydown.enter.stop="$refresh" />
                            <div class="input-group-append">
                                <button type="button" wire:click="$refresh" class="btn btn-sm btn-default">
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
            <table id="table_index" class="table table-hover table-striped table-sm text-sm">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Satuan</th>
                        <th>Jenis</th>
                        <th>Supplier</th>
                        <th>Min</th>
                        <th>Max</th>
                        <th>Saat ini</th>
                        <th>Saran order</th>
                        <th>Harga Per Unit</th>
                        <th>Total Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->stokDaruratLogistik as $barang)
                        <tr>
                            <td>{{ $barang->kode_brng }}</td>
                            <td>{{ $barang->nama_brng }}</td>
                            <td>{{ $barang->satuan }}</td>
                            <td>{{ $barang->jenis }}</td>
                            <td>{{ $barang->nama_supplier }}</td>
                            <td>{{ $barang->stokmin }}</td>
                            <td>{{ $barang->stokmax }}</td>
                            <td>{{ $barang->stok }}</td>
                            <td>{{ $barang->saran_order }}</td>
                            <td>{{ rp($barang->harga) }}</td>
                            <td>{{ rp($barang->total_harga) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <div class="d-flex align-items center justify-content-start">
                <p class="text-muted">Menampilkan {{ $this->stokDaruratLogistik->count() }} dari total
                    {{ number_format($this->stokDaruratLogistik->total(), 0, ',', '.') }} item.</p>
                <div class="ml-auto">
                    {{ $this->stokDaruratLogistik->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
