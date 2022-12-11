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
        <div class="card-body" id="input">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-start align-items-center">
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
                        <span class="text-sm pr-4">Periode:</span>
                        <input type="date" class="form-control form-control-sm w-25" wire:model.defer="periodeAwal" />
                        <span class="text-sm px-2">sampai</span>
                        <input type="date" class="form-control form-control-sm w-25" wire:model.defer="periodeAkhir" />
                        <div class="ml-auto">
                            <button class="btn btn-default btn-sm" type="button" wire:click="$emit('refreshFilter')">
                                <i class="fas fa-sync"></i>
                                <span class="ml-1">Refresh</span>
                            </button>
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
                        <th>Harga Per Unit (Rp)</th>
                        <th>Total Harga (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->barangDaruratStok as $barang)
                        <tr style="position: relative">
                            <td>{{ $barang->kode_brng }}</td>
                            <td>{{ $barang->nama_brng }}</td>
                            <td>{{ $barang->satuan }}</td>
                            <td>{{ $barang->jenis }}</td>
                            <td>{{ $barang->nama_supplier }}</td>
                            <td>{{ $barang->stokmin }}</td>
                            <td>{{ $barang->stokmax }}</td>
                            <td>{{ $barang->stok }}</td>
                            <td>{{ $barang->saran_order }}</td>
                            <td>{{ $barang->harga }}</td>
                            <td>{{ $barang->total_harga }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <div class="d-flex align-items center justify-content-start">
                <p class="text-muted">Menampilkan {{ $this->barangDaruratStok->count() }} dari total {{ number_format($this->barangDaruratStok->total(), 0, ',', '.') }} item.</p>
                <div class="ml-auto">
                    {{ $this->barangDaruratStok->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
