<div>
    <x-flash />

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
                <x-filter />
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
