<div>
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
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="200">200</option>
                                <option value="500">500</option>
                                <option value="1000">1000</option>
                            </select>
                        </div>
                        <span class="text-sm pl-2">per halaman</span>
                        <span class="text-sm ml-auto pr-2">Cari:</span>
                        <div class="input-group input-group-sm" style="width: 16rem">
                            <input type="search" class="form-control" wire:model.defer="cari" />
                            <div class="input-group-append">
                                <button type="button" wire:click="$refresh" class="btn btn-sm btn-default">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped table-sm text-sm">
                <thead>
                    <tr>
                        <th>Kode barang</th>
                        <th>Nama</th>
                        <th>Satuan kecil</th>
                        <th>Kategori</th>
                        <th>Stok minimal</th>
                        <th>Stok saat ini</th>
                        <th>Saran order</th>
                        <th>Supplier</th>
                        <th>Harga Per Unit (Rp)</th>
                        <th>Total Harga (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($stokDarurat as $barang)
                        @php($saranOrder = $barang->saran_order < 0 ? '0' : $barang->saran_order)
                        <tr>
                            <td>{{ $barang->kode_brng }}</td>
                            <td>{{ $barang->nama_brng }}</td>
                            <td>{{ $barang->satuan_kecil }}</td>
                            <td>{{ $barang->kategori }}</td>
                            <td>{{ $barang->stokminimal }}</td>
                            <td>{{ $barang->stok_saat_ini }}</td>
                            <td>{{ $saranOrder }}</td>
                            <td>{{ $barang->nama_industri }}</td>
                            <td>{{ ceil($barang->h_beli) }}</td>
                            <td>{{ ceil($barang->h_beli * $saranOrder) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
