<div>
    @if (session()->has('excel.exporting'))
        <div class="alert alert-dark alert-dismissible fade show">
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
        <div class="card-body table-responsive p-0">
            <ul class="nav nav-tabs border-bottom-0" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="tab-ralan" data-toggle="pill" href="#tab-ralan" role="tab" aria-controls="tab-ralan" aria-selected="true">Rawat Jalan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-ranap" data-toggle="pill" href="#tab-ranap" role="tab" aria-controls="tab-ranap" aria-selected="false">Rawat Inap</a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="tab-ralan" role="tabpanel" aria-label="Tab Ralan">
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
                 <div class="tab-pane fade" id="tab-ranap" role="tabpanel" aria-label="Tab Ranap">
                    asdasda
                 </div>
            </div>
        </div>
        <div class="card-footer">
            <div class="d-flex align-items center justify-content-start">
                <p class="text-muted">Menampilkan {{ $this->kunjunganResepPasienRalan->count() }} dari total {{ number_format($this->kunjunganResepPasienRalan->total(), 0, ',', '.') }} item.</p>
                <div class="ml-auto">
                    {{ $this->kunjunganResepPasienRalan->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
