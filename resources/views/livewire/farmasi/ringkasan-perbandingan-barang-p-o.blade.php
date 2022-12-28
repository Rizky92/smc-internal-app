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
                        <div class="ml-4 custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="hanyaTampilkanPenerimaanBarangYangBerbeda" wire:model.defer="hanyaTampilkanPenerimaanBarangYangBerbeda">
                            <label class="custom-control-label text-sm" for="hanyaTampilkanPenerimaanBarangYangBerbeda">Tampilkan barang yang berbeda jumlah</label>
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
            <table class="table table-hover table-striped table-sm text-sm">
                <thead>
                    <tr>
                        <th>No. Pemesanan</th>
                        <th>Nama</th>
                        <th>Supplier Tujuan</th>
                        <th>Supplier yang Mendatangkan</th>
                        <th>Jumlah Dipesan</th>
                        <th>Jumlah yang Datang</th>
                        <th>Selisih</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->perbandinganOrderObatPO as $obat)
                        <tr>
                            <td>{{ $obat->no_pemesanan }}</td>
                            <td>{{ $obat->nama_brng }}</td>
                            <td>{{ $obat->suplier_pesan }}</td>
                            <td>{{ $obat->suplier_datang }}</td>
                            <td>{{ $obat->jumlah_pesan }}</td>
                            <td>{{ $obat->jumlah_datang }}</td>
                            <td>{{ $obat->selisih }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <div class="d-flex align-items center justify-content-start">
                <p class="text-muted">Menampilkan {{ $this->perbandinganOrderObatPO->count() }} dari total {{ number_format($this->perbandinganOrderObatPO->total(), 0, ',', '.') }} item.</p>
                <div class="ml-auto">
                    {{ $this->perbandinganOrderObatPO->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
