<div>
    @if (session()->has('excel.exporting'))
        <div class="alert alert-dark alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <p>{{ session('excel.exporting') }}</p>
        </div>
    @endif

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
                        <div class="ml-auto input-group input-group-sm" style="width: 20rem">
                            <input type="search" class="form-control" wire:model.defer="cari" placeholder="Cari..." wire:keydown.enter.stop="$refresh" />
                            <div class="input-group-append">
                                <button type="button" wire:click="$refresh" class="btn btn-sm btn-default">
                                    <i class="fas fa-sync-alt"></i>
                                    <span class="ml-1">Refresh</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table id="rekammedis_table" class="table table-hover table-head-fixed table-striped table-sm text-sm" style="width: 400rem">
                <thead>
                    <tr>
                        <th>No. Rawat</th>
                        <th>No. RM</th>
                        <th>Nama Pasien</th>
                        <th>NIK</th>
                        <th>L / P</th>
                        <th>Tgl. Lahir</th>
                        <th>Umur</th>
                        <th>Agama</th>
                        <th>Suku</th>
                        <th>Jenis Perawatan</th>
                        <th>Pasien Lama / Baru</th>
                        <th>Status</th>
                        <th>Tgl. Masuk</th>
                        <th>Jam Masuk</th>
                        <th>Tgl. Pulang</th>
                        <th>Jam Pulang</th>
                        <th>Diagnosa Masuk</th>
                        <th>ICD Diagnosa</th>
                        <th>Diagnosa</th>
                        <th>ICD Tindakan</th>
                        <th>Tindakan</th>
                        <th>Lama Operasi</th>
                        <th>Rujukan Masuk</th>
                        <th>DPJP</th>
                        <th>Poli</th>
                        <th>Kelas</th>
                        <th>Penjamin</th>
                        <th>Status Bayar</th>
                        <th>Status Pulang</th>
                        <th>Rujuk keluar ke RS</th>
                        <th>No. HP</th>
                        <th>Alamat</th>
                        <th>Kunjungan ke</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->dataLaporanStatistik as $registrasi)
                        <tr>
                            <td>{{ $registrasi->no_rawat }}</td>
                            <td>{{ $registrasi->no_rkm_medis }}</td>
                            <td>{{ $registrasi->nm_pasien }}</td>
                            <td>{{ $registrasi->no_ktp }}</td>
                            <td>{{ $registrasi->jk }}</td>
                            <td>{{ $registrasi->tgl_lahir }}</td>
                            <td>{{ $registrasi->umur }}</td>
                            <td>{{ $registrasi->agama }}</td>
                            <td>{{ $registrasi->nama_suku_bangsa }}</td>
                            <td>{{ $registrasi->status_lanjut }}</td>
                            <td>{{ $registrasi->status_poli }}</td>
                            <td>{{ $registrasi->status_perawatan }}</td>
                            <td>{{ $registrasi->tgl_registrasi }}</td>
                            <td>{{ $registrasi->jam_reg }}</td>
                            <td>{{ $registrasi->tgl_keluar }}</td>
                            <td>{{ $registrasi->jam_keluar }}</td>
                            <td>{{ $registrasi->diagnosa_awal }}</td>
                            <td>{{ $registrasi->kd_diagnosa }}</td>
                            <td>{{ $registrasi->nm_diagnosa }}</td>
                            <td>{{ $registrasi->kd_tindakan }}</td>
                            <td>{{ $registrasi->nm_tindakan }}</td>
                            <td>-</td>
                            <td>-</td>
                            <td>{{ $registrasi->nm_dokter }}</td>
                            <td>{{ $registrasi->nm_poli }}</td>
                            <td>{{ $registrasi->kelas }}</td>
                            <td>{{ $registrasi->png_jawab }}</td>
                            <td>{{ $registrasi->status_bayar }}</td>
                            <td>{{ $registrasi->stts_pulang }}</td>
                            <td>-</td>
                            <td>{{ $registrasi->no_tlp }}</td>
                            <td>{{ $registrasi->alamat }}</td>
                            <td>{{ $registrasi->kunjungan_ke }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <div class="d-flex align-items center justify-content-start">
                <p class="text-muted">Menampilkan {{ $this->dataLaporanStatistik->count() }} dari total {{ number_format($this->dataLaporanStatistik->total(), 0, ',', '.') }} item.</p>
                <div class="ml-auto">
                    {{ $this->dataLaporanStatistik->links() }}
                </div>
            </div>
        </div>
        <div wire:loading.delay.class="overlay light">
            <div class="d-none justify-content-center align-items-center" wire:loading.delay.class="d-flex" wire:loading.delay.class.remove="d-none"> 
                <i class="fas fa-sync-alt fa-2x fa-spin"></i>
            </div>
        </div>
    </div>
</div>
