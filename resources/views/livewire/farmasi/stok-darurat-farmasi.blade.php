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
                <x-filter />
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
