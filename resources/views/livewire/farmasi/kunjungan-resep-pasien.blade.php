<div>
    @include('layouts.components.flash')

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
        </div>
        <div class="card-body p-0">
            <ul class="nav nav-tabs nav-fill border-bottom-0" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="tab-ralan" data-toggle="pill" href="#content-obat-regular" role="tab" aria-controls="content-obat-regular" aria-selected="false">Obat Umum</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-ranap" data-toggle="pill" href="#content-obat-racikan" role="tab" aria-controls="content-obat-racikan" aria-selected="false">Obat Racikan</a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane show active" id="content-obat-regular" role="tabpanel" aria-label="Tab Obat Umum">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-sm text-sm">
                            <thead>
                                <tr>
                                    <th>No. Resep</th>
                                    <th>Dokter Peresep</th>
                                    <th>Tgl. Validasi</th>
                                    <th>Jam</th>
                                    <th>Pasien</th>
                                    <th>Jenis Perawatan</th>
                                    <th>Total Pembelian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($this->kunjunganResepObatRegularPasien as $resep)
                                    <tr>
                                        <td>{{ $resep->no_resep }}</td>
                                        <td>{{ $resep->nm_dokter }}</td>
                                        <td>{{ $resep->tgl_perawatan }}</td>
                                        <td>{{ $resep->jam }}</td>
                                        <td>{{ $resep->nm_pasien }}</td>
                                        <td>{{ $resep->status_lanjut }}</td>
                                        <td>{{ rp($resep->total) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                 </div>
                 <div class="tab-pane" id="content-obat-racikan" role="tabpanel" aria-label="Tab Obat Racikan">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-sm text-sm">
                            <thead>
                                <tr>
                                    <th>No. Resep</th>
                                    <th>Dokter Peresep</th>
                                    <th>Tgl. Validasi</th>
                                    <th>Jam</th>
                                    <th>Pasien</th>
                                    <th>Jenis Perawatan</th>
                                    <th>Total Pembelian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($this->kunjunganResepObatRacikanPasien as $resep)
                                    <tr>
                                        <td>{{ $resep->no_resep }}</td>
                                        <td>{{ $resep->nm_dokter }}</td>
                                        <td>{{ $resep->tgl_perawatan }}</td>
                                        <td>{{ $resep->jam }}</td>
                                        <td>{{ $resep->nm_pasien }}</td>
                                        <td>{{ $resep->status_lanjut }}</td>
                                        <td>{{ rp($resep->total) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                 </div>
            </div>
        </div>
        <div class="card-footer">
            <div class="tab-content">
                <div class="tab-pane show active" id="content-obat-regular" role="tabpanel" aria-label="Footer Obat Umum">
                    <div class="d-flex align-items center justify-content-start">
                        <p class="text-muted">Menampilkan {{ $this->kunjunganResepObatRegularPasien->count() }} dari total {{ number_format($this->kunjunganResepObatRegularPasien->total(), 0, ',', '.') }} item.</p>
                        <div class="ml-auto">
                            {{ $this->kunjunganResepObatRegularPasien->links() }}
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="content-obat-racikan" role="tabpanel" aria-label="Footer Obat Racikan">
                    <div class="d-flex align-items center justify-content-start">
                        <p class="text-muted">Menampilkan {{ $this->kunjunganResepObatRacikanPasien->count() }} dari total {{ number_format($this->kunjunganResepObatRacikanPasien->total(), 0, ',', '.') }} item.</p>
                        <div class="ml-auto">
                            {{ $this->kunjunganResepObatRacikanPasien->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
