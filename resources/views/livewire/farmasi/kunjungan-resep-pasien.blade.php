<div>
    @if (session()->has('excel.exporting'))
        <div class="alert alert-dark alert-dismissible show">
            <button class="close" data-dismiss="alert" type="button" aria-hidden="true">&times;</button>
            <p>
                {{ session('excel.exporting') }}
            </p>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-12">

                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <ul class="nav nav-tabs nav-fill border-bottom-0" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="tab-ralan" data-toggle="pill" href="#content-ralan" role="tab" aria-controls="content-ralan" aria-selected="false">Rawat Jalan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-ranap" data-toggle="pill" href="#content-ranap" role="tab" aria-controls="content-ranap" aria-selected="false">Rawat Inap</a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane show active" id="content-ralan" role="tabpanel" aria-label="Tab Ralan">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-sm text-sm">
                            <thead>
                                <tr>
                                    <th>No. Rawat</th>
                                    <th>No. Resep</th>
                                    <th>Pasien</th>
                                    <th>Tgl. Validasi</th>
                                    <th>Status Poli</th>
                                    <th>Total Pembelian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($this->kunjunganResepPasienRalan as $resep)
                                    <tr>
                                        <td>{{ $resep->no_rawat }}</td>
                                        <td>{{ $resep->no_resep }}</td>
                                        <td>{{ $resep->nm_pasien }}</td>
                                        <td>{{ $resep->tgl_perawatan }}</td>
                                        <td>{{ $resep->status_lanjut }}</td>
                                        <td>{{ rp($resep->total) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                 </div>
                 <div class="tab-pane" id="content-ranap" role="tabpanel" aria-label="Tab Ranap">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-sm text-sm">
                            <thead>
                                <tr>
                                    <th>No. Rawat</th>
                                    <th>No. Resep</th>
                                    <th>Pasien</th>
                                    <th>Tgl. Validasi</th>
                                    <th>Status Poli</th>
                                    <th>Total Pembelian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($this->kunjunganResepPasienRanap as $resep)
                                    <tr>
                                        <td>{{ $resep->no_rawat }}</td>
                                        <td>{{ $resep->no_resep }}</td>
                                        <td>{{ $resep->nm_pasien }}</td>
                                        <td>{{ $resep->tgl_perawatan }}</td>
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
                <div class="tab-pane show active" id="content-ralan" role="tabpanel" aria-label="Footer Tab Ralan">
                    <div class="d-flex align-items center justify-content-start">
                        <p class="text-muted">Menampilkan {{ $this->kunjunganResepPasienRalan->count() }} dari total {{ number_format($this->kunjunganResepPasienRalan->total(), 0, ',', '.') }} item.</p>
                        <div class="ml-auto">
                            {{ $this->kunjunganResepPasienRalan->links() }}
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="content-ranap" role="tabpanel" aria-label="Footer Tab Ralan">
                    <div class="d-flex align-items center justify-content-start">
                        <p class="text-muted">Menampilkan {{ $this->kunjunganResepPasienRanap->count() }} dari total {{ number_format($this->kunjunganResepPasienRanap->total(), 0, ',', '.') }} item.</p>
                        <div class="ml-auto">
                            {{ $this->kunjunganResepPasienRanap->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
