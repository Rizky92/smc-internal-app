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
        <div class="card-body table-responsive p-0 border-top">
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
                            <td>{{ $resep->total }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
