<div>
    <x-flash />
    
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
                <x-filter />
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
                    @foreach ($this->pasienPerPoli as $pasien)
                        <tr>
                            <td>{{ $pasien->no_resep }}</td>
                            <td>{{ $pasien->no_rawat }}</td>
                            <td>{{ $pasien->nm_pasien }}</td>
                            <td>{{ $pasien->status_lanjut }}</td>
                            <td>{{ $pasien->nm_poli }}</td>
                            <td>{{ $pasien->tgl_peresepan }}</td>
                            <td>{{ $pasien->tgl_perawatan }}</td>
                            <td>{{ $pasien->jumlah_obat }}</td>
                            <td>{{ $pasien->total_obat }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <div class="d-flex align-items center justify-content-start">
                <p class="text-muted">Menampilkan {{ $this->pasienPerPoli->count() }} dari total {{ number_format($this->pasienPerPoli->total(), 0, ',', '.') }} item.</p>
                <div class="ml-auto">
                    {{ $this->pasienPerPoli->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
