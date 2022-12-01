<div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex align-items-center justify-content-start">
                        <span class="text-sm pr-4">Periode:</span>
                        <input type="date" class="form-control form-control-sm w-25" wire:model.defer="periodeAwal" />
                        <span class="text-sm px-2">sampai</span>
                        <input type="date" class="form-control form-control-sm w-25" wire:model.defer="periodeAkhir" />
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
                            <input type="search" name="search" class="form-control" wire:model.defer="cari" />
                            <div class="input-group-append">
                                <button type="button" wire:click="$emit('refreshFilter')" class="btn btn-sm btn-default">
                                    <i class="fas fa-redo-alt"></i>
                                    <span class="ml-1">Refresh</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table id="table_index" class="table table-hover table-striped table-sm text-sm">
                <thead>
                    <tr>
                        <th>No. Resep</th>
                        <th>Tanggal Peresepan</th>
                        <th>Nama Obat</th>
                        <th>Jumlah</th>
                        <th>Dokter Peresep</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($obatPerDokter as $dataObat)
                        <tr>
                            <td>{{ $dataObat->no_resep }}</td>
                            <td>{{ $dataObat->tgl_peresepan }}</td>
                            <td>{{ $dataObat->nama_brng }}</td>
                            <td>{{ $dataObat->jml }}</td>
                            <td>{{ $dataObat->nm_dokter }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <div class="d-flex align-items center justify-content-start">
                <p class="text-muted">Menampilkan {{ $obatPerDokter->count() }} dari total {{ number_format($obatPerDokter->total(), 0, ',', '.') }} item.</p>
                <div class="ml-auto">
                    {{ $obatPerDokter->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
