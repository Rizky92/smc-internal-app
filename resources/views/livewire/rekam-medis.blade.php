<div class="card">
    <div class="card-body" id="table_filter_action">
        <div class="row">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-start">
                    <span class="text-sm pr-4">Periode:</span>
                    <div class="input-group input-group-sm date w-25">
                        <div class="input-group-prepend">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                        <input type="date" class="form-control" wire:model.defer="periodeAwal" />
                    </div>
                    <span class="text-sm px-2">Sampai</span>
                    <div class="input-group input-group-sm date w-25">
                        <div class="input-group-prepend">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                        <input type="date" class="form-control" wire:model.defer="periodeAkhir" />
                    </div>
                    <div class="ml-auto">
                        <button class="btn btn-success btn-sm" type="button" wire:click="exportToExcel">
                            <i class="fas fa-file-excel"></i>
                            <span class="ml-1">Export ke Excel</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-12 mt-2">
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
                    <th>Tgl. Masuk</th>
                    <th>Jam Masuk</th>
                    <th>Tgl. Pulang</th>
                    <th>Jam Pulang</th>
                    <th>Diagnosa Masuk</th>
                    <th>ICD Primer</th>
                    <th>Diagnosa Primer</th>
                    <th>ICD Sekunder</th>
                    <th>Diagnosa Sekunder</th>
                    <th>Tindakan</th>
                    <th>ICD 9CM</th>
                    <th>Lama Operasi</th>
                    <th>Rujukan Masuk</th>
                    <th>DPJP</th>
                    <th>Poli</th>
                    <th>Kelas</th>
                    <th>Penjamin</th>
                    <th>Status Pulang</th>
                    <th>Rujuk keluar ke RS</th>
                    <th>No. HP</th>
                    <th>Alamat</th>
                    <th>Kunjungan ke</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($statistik as $registrasi)
                    @php
                        $diagnosa = $registrasi->diagnosa->take(1 - $registrasi->diagnosa->count());
                        
                        $kdDiagnosaSekunder = $diagnosa->reduce(function ($carry, $item) {
                            return $item->kd_penyakit . '; <br>' . $carry;
                        });
                        
                        $nmDiagnosaSekunder = $diagnosa->reduce(function ($carry, $item) {
                            return $item->nm_penyakit . '; <br>' . $carry;
                        });
                        
                        $tglKeluar = optional(optional($registrasi->rawatInap->first())->pivot)->tgl_keluar;
                        $jamKeluar = optional(optional($registrasi->rawatInap->first())->pivot)->tgl_keluar;
                        
                        if (!is_null($tglKeluar)) {
                            $tglKeluar = $tglKeluar->format('d-m-y');
                        }
                        
                        if (!is_null($jamKeluar)) {
                            $jamKeluar = $jamKeluar->format('H:i:s');
                        }
                        
                        $nmTindakanRalanDokter = $registrasi->tindakanRalanDokter->reduce(function ($carry, $item) {
                            return $item->nm_perawatan . '; <br>' . $carry;
                        });
                        
                        $nmTindakanRalanPerawat = $registrasi->tindakanRalanPerawat->reduce(function ($carry, $item) {
                            return $item->nm_perawatan . '; <br>' . $carry;
                        });
                        
                        $nmTindakanRalanDokterPerawat = $registrasi->tindakanRalanDokterPerawat->reduce(function ($carry, $item) {
                            return $item->nm_perawatan . '; <br>' . $carry;
                        });
                        
                        $nmTindakanRanapDokter = $registrasi->tindakanRanapDokter->reduce(function ($carry, $item) {
                            return $item->nm_perawatan . '; <br>' . $carry;
                        });
                        
                        $nmTindakanRanapPerawat = $registrasi->tindakanRanapPerawat->reduce(function ($carry, $item) {
                            return $item->nm_perawatan . '; <br>' . $carry;
                        });
                        
                        $nmTindakanRanapDokterPerawat = $registrasi->tindakanRanapDokterPerawat->reduce(function ($carry, $item) {
                            return $item->nm_perawatan . '; <br>' . $carry;
                        });
                        
                        $nmTindakan = collect([$nmTindakanRalanDokter, $nmTindakanRalanPerawat, $nmTindakanRalanDokterPerawat, $nmTindakanRanapDokter, $nmTindakanRanapPerawat, $nmTindakanRanapDokterPerawat]);
                        
                        $nmTindakan = $nmTindakan->join('');
                        
                        $kdTindakanRalanDokter = $registrasi->tindakanRalanDokter->reduce(function ($carry, $item) {
                            return $item->kd_jenis_prw . '; <br>' . $carry;
                        });
                        
                        $kdTindakanRalanPerawat = $registrasi->tindakanRalanPerawat->reduce(function ($carry, $item) {
                            return $item->kd_jenis_prw . '; <br>' . $carry;
                        });
                        
                        $kdTindakanRalanDokterPerawat = $registrasi->tindakanRalanDokterPerawat->reduce(function ($carry, $item) {
                            return $item->kd_jenis_prw . '; <br>' . $carry;
                        });
                        
                        $kdTindakanRanapDokter = $registrasi->tindakanRanapDokter->reduce(function ($carry, $item) {
                            return $item->kd_jenis_prw . '; <br>' . $carry;
                        });
                        
                        $kdTindakanRanapPerawat = $registrasi->tindakanRanapPerawat->reduce(function ($carry, $item) {
                            return $item->kd_jenis_prw . '; <br>' . $carry;
                        });
                        
                        $kdTindakanRanapDokterPerawat = $registrasi->tindakanRanapDokterPerawat->reduce(function ($carry, $item) {
                            return $item->kd_jenis_prw . '; <br>' . $carry;
                        });
                        
                        $kdTindakan = collect([$kdTindakanRalanDokter, $kdTindakanRalanPerawat, $kdTindakanRalanDokterPerawat, $kdTindakanRanapDokter, $kdTindakanRanapPerawat, $kdTindakanRanapDokterPerawat]);
                        
                        $kdTindakan = $kdTindakan->join('');
                    @endphp
                    <tr>
                        <td>{{ $registrasi->no_rawat }}</td>
                        <td>{{ $registrasi->no_rkm_medis }}</td>
                        <td>{{ optional($registrasi->pasien)->nm_pasien }}</td>
                        <td>{{ optional($registrasi->pasien)->no_ktp }}</td>
                        <td>{{ optional($registrasi->pasien)->jk }}</td>
                        <td>{{ optional($registrasi->pasien)->tgl_lahir }}</td>
                        <td>{{ $registrasi->umurdaftar }} {{ $registrasi->sttsumur }}</td>
                        <td>{{ optional($registrasi->pasien)->agama }}</td>
                        <td>{{ optional(optional($registrasi->pasien)->suku)->nama_suku_bangsa }}</td>
                        <td>{{ $registrasi->status_lanjut }}</td>
                        <td>{{ $registrasi->status_poli }}</td>
                        <td>{{ $registrasi->tgl_registrasi->format('d-m-Y') }}</td>
                        <td>{{ $registrasi->jam_reg->format('H:i:s') }}</td>
                        <td>{{ $tglKeluar }}</td>
                        <td>{{ $jamKeluar }}</td>
                        <td>{{ optional($registrasi->rawatInap->first())->pivot->diagnosa_awal ?? '' }}</td>
                        <td>{{ optional(optional($registrasi->diagnosa)->first())->kd_penyakit ?? '-' }}</td>
                        <td>{{ optional(optional($registrasi->diagnosa)->first())->nm_penyakit ?? '-' }}</td>
                        <td>{!! $kdDiagnosaSekunder ?? '-' !!}</td>
                        <td>{!! $nmDiagnosaSekunder ?? '-' !!}</td>
                        <td>{!! $nmTindakan !!}</td>
                        <td>{!! $kdTindakan !!}</td>
                        <td colspan="2"></td>
                        <td>{{ optional($registrasi->dokter)->nm_dokter }}</td>
                        <td>{{ optional($registrasi->poliklinik)->nm_poli }}</td>
                        <td>{{ optional($registrasi->rawatInap->first())->kelas }}</td>
                        <td>{{ optional($registrasi->penjamin)->png_jawab }}</td>
                        <td>{{ $registrasi->stts }}</td>
                        <td></td>
                        <td>{{ optional($registrasi->pasien)->no_tlp }}</td>
                        <td>{{ optional($registrasi->pasien)->alamat }}</td>
                        <td>{{ $registrasi->kunjungan_ke }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        <div class="d-flex align-items center justify-content-start">
            <p class="text-muted">Menampilkan {{ $statistik->count() }} dari total {{ number_format($statistik->total(), 0, ',', '.') }} item.</p>
            <div class="ml-auto">
                {{ $statistik->links() }}
            </div>
        </div>
    </div>
</div>
