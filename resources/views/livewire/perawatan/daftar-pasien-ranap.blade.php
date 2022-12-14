<div>
    @if (session()->has('saved.kamar-dibatalkan'))
    @endif

    @once
        @push('js')
            <script>
                let inputReadNoRawat
                let inputReadNoRekamMedis
                let inputReadPasien
                let inputReadRuangan
                let inputReadKamar
                let inputReadWaktuMasuk

                $(document).ready(() => {
                    inputReadNoRawat = $('#no_rawat')
                    inputReadNoRekamMedis = $('#no_rm')
                    inputReadPasien = $('#pasien')
                    inputReadRuangan = $('#ruangan')
                    inputReadKamar = $('#kamar')
                    inputReadWaktuMasuk = $('#waktu_masuk')
                })

                const loadData = ({
                    noRawat,
                    noRekamMedis,
                    pasien,
                    ruangan,
                    kamar,
                    waktuMasuk
                }) => {
                    inputReadNoRawat.val(noRawat)
                    inputReadNoRekamMedis.val(noRekamMedis)
                    inputReadPasien.val(pasien)
                    inputReadRuangan.val(ruangan)
                    inputReadKamar.val(kamar)
                    inputReadWaktuMasuk.val(waktuMasuk)

                    inputReadNoRawat.trigger('change')
                    inputReadNoRekamMedis.trigger('change')
                    inputReadPasien.trigger('change')
                    inputReadRuangan.trigger('change')
                    inputReadKamar.trigger('change')
                    inputReadWaktuMasuk.trigger('change')
                }
            </script>
        @endpush
    @endonce

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-4">
                    <div class="form-group">
                        <label for="no_rawat" class="text-sm">No. Rawat</label>
                        <input type="text" id="no_rawat" class="form-control form-control-sm bg-light" readonly>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label for="no_rm" class="text-sm">No. Rekam Medis</label>
                        <input type="text" id="no_rm" class="form-control form-control-sm bg-light" readonly>
                    </div>
                </div>
                <div class="col-5">
                    <div class="form-group">
                        <label for="nama_pasien" class="text-sm">Pasien</label>
                        <input type="text" id="pasien" class="form-control form-control-sm bg-light" readonly>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label for="ruangan" class="text-sm">Kamar / Ruangan</label>
                        <input type="text" id="ruangan" class="form-control form-control-sm bg-light" readonly>
                        <input type="hidden" id="kamar" readonly>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label for="waktu_masuk" class="text-sm">Waktu Masuk</label>
                        <input type="text" id="waktu_masuk" class="form-control form-control-sm bg-light" readonly>
                    </div>
                </div>
                <div class="col-6">
                    <div class="d-flex justify-content-end align-items-end h-100 pb-3">
                        <button type="button" class="btn btn-sm btn-default">
                            <i class="fas fa-sync-alt"></i>
                            <span class="ml-1">Reset</span>
                        </button>
                        @can('perawatan.rawat-inap.batal-ranap')
                            <button type="button" class="ml-2 btn btn-sm btn-danger">
                                <i class="fas fa-sign-out-alt fa-flip-horizontal"></i>
                                <span class="ml-1">Batal Ranap</span>
                            </button>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body border-top">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex align-items-center justify-content-start">
                        <span class="text-sm pr-4">Periode:</span>
                        <input type="date" class="form-control form-control-sm w-25" wire:model.defer="periodeAwal" />
                        <span class="text-sm px-2">sampai</span>
                        <input type="date" class="form-control form-control-sm w-25" wire:model.defer="periodeAkhir" />
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
            <table class="table table-hover table-head-fixed table-striped table-sm text-sm" style="width: 150rem">
                <thead>
                    <tr>
                        <th>No. Rawat</th>
                        <th>No. RM</th>
                        <th>Pasien</th>
                        <th>Alamat</th>
                        <th>Agama</th>
                        <th>P.J.</th>
                        <th>Jenis Bayar</th>
                        <th>Kamar</th>
                        <th>Tarif</th>
                        <th>Tgl. Masuk</th>
                        <th>Jam Masuk</th>
                        <th>Lama Inap</th>
                        <th>Dokter P.J.</th>
                        <th>No. HP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->daftarPasienRanap as $pasien)
                        <tr style="position: relative">
                            <td style="width: 20ch">
                                {{ $pasien->no_rawat }}
                                <a href="#" style="position: absolute; left: 0; right: 0; top: 0; bottom: 0" data-no-rawat="{{ $pasien->no_rawat }}" data-no-rekam-medis="{{ $pasien->no_rkm_medis }}" data-pasien="{{ $pasien->data_pasien }}" data-ruangan="{{ $pasien->ruangan }}" data-kamar="{{ $pasien->kd_kamar }}" data-waktu-masuk="{{ $pasien->tgl_masuk }} {{ $pasien->jam_masuk }}" onclick="loadData(this.dataset)"></a>
                            </td>
                            <td style="width: 10ch">{{ $pasien->no_rkm_medis }}</td>
                            <td style="width: 25ch">
                                {{ $pasien->data_pasien }}
                            </td>
                            <td style="width: 50ch;">{{ $pasien->alamat_pasien }}</td>
                            <td>{{ $pasien->agama }}</td>
                            <td>{{ $pasien->pj }}</td>
                            <td>{{ $pasien->png_jawab }}</td>
                            <td>{{ $pasien->ruangan }}</td>
                            <td style="width: 15ch">{{ rp($pasien->trf_kamar) }}</td>
                            <td>{{ $pasien->tgl_masuk }}</td>
                            <td>{{ $pasien->jam_masuk }}</td>
                            <td>{{ $pasien->lama }} hari</td>
                            <td>{{ $pasien->nama_dokter }}</td>
                            <td>{{ $pasien->no_tlp }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <div class="d-flex align-items center justify-content-start">
                <p class="text-muted">Menampilkan {{ $this->daftarPasienRanap->count() }} dari total {{ number_format($this->daftarPasienRanap->total(), 0, ',', '.') }} item.</p>
                <div class="ml-auto">
                    {{ $this->daftarPasienRanap->links() }}
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
