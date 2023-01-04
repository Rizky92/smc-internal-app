<div>
    <x-flash />

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex align-items-center justify-content-start">
                        <span class="text-sm" style="width: 5rem">Periode:</span>
                        <input class="form-control form-control-sm" type="date" style="width: 8rem" wire:model.defer="periodeAwal" />
                        <span class="text-sm px-3">sampai</span>
                        <input class="form-control form-control-sm" type="date" style="width: 8rem" wire:model.defer="periodeAkhir" />
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
                        <span class="text-sm" style="width: 5rem">Tampilkan:</span>
                        <div class="input-group input-group-sm" style="width: 4rem">
                            <select class="custom-control custom-select" name="perpage" wire:model.defer="perpage">
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
                        <div class="ml-auto input-group input-group-sm" style="width: 16rem">
                            <input class="form-control" type="search" wire:model.defer="cari" placeholder="Cari..." wire:keydown.enter="searchData" />
                            <div class="input-group-append">
                                <button class="btn btn-sm btn-default" type="button" wire:click="searchData">
                                    <i class="fas fa-search"></i>
                                    <span class="ml-1">Cari</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-head-fixed table-striped table-sm text-sm" id="rekammedis_table" style="width: 450rem">
                <thead>
                    <tr>
                        <th>No. Rawat</th>
                        <th>No RM</th>
                        <th>Pasien</th>
                        <th>NIK</th>
                        <th>L / P</th>
                        <th>Tgl. Lahir</th>
                        <th>Umur</th>
                        <th>Agama</th>
                        <th>Suku</th>
                        <th>Jenis Perawatan</th>
                        <th>Pasien Lama / Baru</th>
                        <th>Asal Poli</th>
                        <th>Dokter Poli</th>
                        <th>Status Ralan</th>
                        <th>Tgl. Masuk</th>
                        <th>Jam Masuk</th>
                        <th>Tgl. Pulang</th>
                        <th>Jam Pulang</th>
                        <th>Diagnosa Masuk</th>
                        <th style="width: 30ch">ICD Diagnosa</th>
                        <th style="width: 80ch">Diagnosa</th>
                        <th style="width: 30ch">ICD Tindakan Ralan</th>
                        <th style="width: 80ch">Tindakan Ralan</th>
                        <th style="width: 30ch">ICD Tindakan Ranap</th>
                        <th style="width: 80ch">Tindakan Ranap</th>
                        <th>Lama Operasi</th>
                        <th>Rujukan Masuk</th>
                        <th>DPJP Ranap</th>
                        <th>Kelas</th>
                        <th>Penjamin</th>
                        <th>Status Bayar</th>
                        <th>Status Pulang</th>
                        <th>Rujukan Keluar</th>
                        <th>No. HP</th>
                        <th>Alamat</th>
                        <th>Kunjungan ke</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->dataLaporanStatistik as $registrasi)
                        <tr>
                            <td>{{ $registrasi->no_rawat }}</td>
                            <td>{{ $registrasi->no_rm }}</td>
                            <td>{{ $registrasi->pasien }}</td>
                            <td>{{ $registrasi->nik }}</td>
                            <td>{{ $registrasi->jk }}</td>
                            <td>{{ $registrasi->tgl_lahir }}</td>
                            <td>{{ $registrasi->umur }}</td>
                            <td>{{ $registrasi->agama }}</td>
                            <td>{{ $registrasi->suku }}</td>
                            <td>{{ $registrasi->status_rawat }}</td>
                            <td>{{ $registrasi->status_poli }}</td>
                            <td>{{ $registrasi->asal_poli }}</td>
                            <td>{{ $registrasi->dokter_poli }}</td>
                            <td>{{ $registrasi->status_ralan }}</td>
                            <td>{{ $registrasi->tgl_masuk }}</td>
                            <td>{{ $registrasi->jam_masuk }}</td>
                            <td>{{ $registrasi->tgl_keluar }}</td>
                            <td>{{ $registrasi->jam_keluar }}</td>
                            <td>{{ $registrasi->diagnosa_awal }}</td>
                            <td>{{ $registrasi->kd_diagnosa }}</td>
                            <td>{{ $registrasi->nm_diagnosa }}</td>
                            <td>{{ $registrasi->kd_tindakan_ralan }}</td>
                            <td>{{ $registrasi->nm_tindakan_ralan }}</td>
                            <td>{{ $registrasi->kd_tindakan_ranap }}</td>
                            <td>{{ $registrasi->nm_tindakan_ranap }}</td>
                            <td>{{ $registrasi->lama_operasi }}</td>
                            <td>{{ $registrasi->rujukan_masuk }}</td>
                            <td>{{ $registrasi->dokter_pj }}</td>
                            <td>{{ $registrasi->kelas }}</td>
                            <td>{{ $registrasi->jenis_bayar }}</td>
                            <td>{{ $registrasi->status_bayar }}</td>
                            <td>{{ $registrasi->status_pulang_ranap }}</td>
                            <td>{{ $registrasi->rujuk_keluar_rs }}</td>
                            <td>{{ $registrasi->alamat }}</td>
                            <td>{{ $registrasi->no_hp }}</td>
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

    {{-- <div>
        <x-flash />

        <x-card>
            <x-slot name="body">
                <x-card.table>
                    <x-slot name="columns">
                        <x-card.table.th>Kode</x-card.table.th>
                        <x-card.table.th>Nama</x-card.table.th>
                        <x-card.table.th>Satuan</x-card.table.th>
                        <x-card.table.th>Kategori</x-card.table.th>
                        <x-card.table.th>Stok minimal</x-card.table.th>
                        <x-card.table.th>Stok saat ini</x-card.table.th>
                        <x-card.table.th>Saran order</x-card.table.th>
                        <x-card.table.th>Supplier</x-card.table.th>
                        <x-card.table.th>Harga Per Unit</x-card.table.th>
                        <x-card.table.th>Total Harga</x-card.table.th>
                    </x-slot>
                    <x-slot name="body">
                        @foreach ($this->stokDaruratObat as $obat)
                            <x-card.table.tr>
                                <x-card.table.td>{{ $obat->kode_brng }}</x-card.table.td>
                                <x-card.table.td>{{ $obat->nama_brng }}</x-card.table.td>
                                <x-card.table.td>{{ $obat->satuan_kecil }}</x-card.table.td>
                                <x-card.table.td>{{ $obat->kategori }}</x-card.table.td>
                                <x-card.table.td>{{ $obat->stokminimal }}</x-card.table.td>
                                <x-card.table.td>{{ $obat->stok_sekarang }}</x-card.table.td>
                                <x-card.table.td>{{ $obat->saran_order }}</x-card.table.td>
                                <x-card.table.td>{{ $obat->nama_industri }}</x-card.table.td>
                                <x-card.table.td>{{ rp($obat->harga_beli) }}</x-card.table.td>
                                <x-card.table.td>{{ rp($obat->harga_beli_total) }}</x-card.table.td>
                            </x-card.table.tr>
                        @endforeach
                    </x-slot>
                </x-card.table>
            </x-slot>
            <x-slot name="footer">
                <x-card.paginator :count="$this->stokDaruratObat->count()" :total="$this->stokDaruratObat->total()">
                    <x-slot name="links">{{ $this->stokDaruratObat->links() }}</x-slot>
                </x-card.paginator>
            </x-slot>
        </x-card>
    </div> --}}
</div>
