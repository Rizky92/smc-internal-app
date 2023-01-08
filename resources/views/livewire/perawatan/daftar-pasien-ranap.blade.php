<div>
    <x-flash />

    {{-- @can('perawatan.rawat-inap.batal-ranap')
        @once
            @push('js')
                <script>
                    let inputReadNoRawat
                    let inputReadNoRekamMedis
                    let inputReadPasien
                    let inputReadRuangan
                    let inputReadKamar
                    let inputReadWaktuMasuk
                    let inputReadTglMasuk
                    let inputReadJamMasuk

                    $(document).ready(() => {
                        inputReadNoRawat = $('#no_rawat')
                        inputReadNoRekamMedis = $('#no_rm')
                        inputReadPasien = $('#pasien')
                        inputReadRuangan = $('#ruangan')
                        inputReadKamar = $('#kamar')
                        inputReadWaktuMasuk = $('#waktu_masuk')
                        inputReadTglMasuk = $('#tgl_masuk')
                        inputReadJamMasuk = $('#jam_masuk')
                    })

                    const loadData = ({
                        noRawat,
                        noRekamMedis,
                        pasien,
                        ruangan,
                        kamar,
                        tglMasuk,
                        jamMasuk
                    }) => {
                        inputReadNoRawat.val(noRawat)
                        inputReadNoRekamMedis.val(noRekamMedis)
                        inputReadPasien.val(pasien)
                        inputReadRuangan.val(ruangan)
                        inputReadKamar.val(kamar)
                        inputReadWaktuMasuk.val(`${tglMasuk} ${jamMasuk}`)
                        inputReadJamMasuk.val(jamMasuk)
                        inputReadTglMasuk.val(tglMasuk)

                        inputReadNoRawat.trigger('change')
                        inputReadNoRekamMedis.trigger('change')
                        inputReadPasien.trigger('change')
                        inputReadRuangan.trigger('change')
                        inputReadKamar.trigger('change')
                        inputReadWaktuMasuk.trigger('change')
                        inputReadJamMasuk.trigger('change')
                        inputReadTglMasuk.trigger('change')
                    }

                    $('#batalkan-ranap').click(() => {
                        if (
                            inputReadNoRawat.val() &&
                            inputReadNoRekamMedis.val() &&
                            inputReadPasien.val() &&
                            inputReadRuangan.val() &&
                            inputReadKamar.val() &&
                            inputReadWaktuMasuk.val() &&
                            inputReadJamMasuk.val() &&
                            inputReadTglMasuk.val()
                        ) {
                            $('#konfirmasi-batal').modal('show')
                        }
                    })

                    $('#simpandata').click(() => {
                        @this.batalkanRanapPasien(
                            inputReadNoRawat.val(),
                            inputReadTglMasuk.val(),
                            inputReadJamMasuk.val(),
                            inputReadKamar.val()
                        )

                        clearinputs()
                    })

                    $('#batalsimpan').click(() => clearinputs())
                    $('#resetinput').click(() => clearinputs())

                    function clearinputs() {
                        inputReadNoRawat.val('')
                        inputReadNoRekamMedis.val('')
                        inputReadPasien.val('')
                        inputReadRuangan.val('')
                        inputReadKamar.val('')
                        inputReadWaktuMasuk.val('')
                        inputReadJamMasuk.val('')
                        inputReadTglMasuk.val('')

                        inputReadNoRawat.trigger('change')
                        inputReadNoRekamMedis.trigger('change')
                        inputReadPasien.trigger('change')
                        inputReadRuangan.trigger('change')
                        inputReadKamar.trigger('change')
                        inputReadWaktuMasuk.trigger('change')
                        inputReadJamMasuk.trigger('change')
                        inputReadTglMasuk.trigger('change')
                    }
                </script>
            @endpush
        @endonce

        <div class="modal fade" id="konfirmasi-batal">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-start align-items-start">
                                    <h5 class="ml-2">Apakah anda yakin?</h5>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-end align-items-center">
                                    <button class="btn btn-default" id="batalsimpan" data-dismiss="modal" type="button">
                                        Tidak
                                    </button>
                                    <button class="ml-2 btn btn-danger" id="simpandata" data-dismiss="modal" type="button">
                                        <i class="fas fa-check"></i>
                                        <span class="ml-1">Ya</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-4">
                        <div class="form-group">
                            <label class="text-sm" for="no_rawat">No. Rawat</label>
                            <input class="form-control form-control-sm bg-light" id="no_rawat" type="text" readonly>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label class="text-sm" for="no_rm">No. Rekam Medis</label>
                            <input class="form-control form-control-sm bg-light" id="no_rm" type="text" readonly>
                        </div>
                    </div>
                    <div class="col-5">
                        <div class="form-group">
                            <label class="text-sm" for="nama_pasien">Pasien</label>
                            <input class="form-control form-control-sm bg-light" id="pasien" type="text" readonly>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label class="text-sm" for="ruangan">Kamar / Ruangan</label>
                            <input class="form-control form-control-sm bg-light" id="ruangan" type="text" readonly>
                            <input id="kamar" type="hidden" readonly>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label class="text-sm" for="waktu_masuk">Waktu Masuk</label>
                            <input class="form-control form-control-sm bg-light" id="waktu_masuk" type="text" readonly>
                            <input id="tgl_masuk" type="hidden" readonly>
                            <input id="jam_masuk" type="hidden" readonly>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex justify-content-end align-items-end h-100 pb-3">
                            <button class="btn btn-sm btn-default" id="resetinput" type="button">
                                <i class="fas fa-sync-alt"></i>
                                <span class="ml-1">Reset</span>
                            </button>
                            @can('perawatan.rawat-inap.batal-ranap')
                                <button class="ml-2 btn btn-sm btn-danger" id="batalkan-ranap" type="button">
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
                            <input class="form-control form-control-sm w-25" type="date" wire:model.defer="tglAwal" />
                            <span class="text-sm px-2">sampai</span>
                            <input class="form-control form-control-sm w-25" type="date" wire:model.defer="tglAkhir" />
                        </div>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12">
                        <div class="d-flex align-items-center justify-content-start">
                            <span class="text-sm pr-2">Tampilkan:</span>
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
                            <div class="ml-auto input-group input-group-sm" style="width: 20rem">
                                <input class="form-control" type="search" wire:model.defer="cari" placeholder="Cari..." wire:keydown.enter.stop="searchData" />
                                <div class="input-group-append">
                                    <button class="btn btn-sm btn-default" type="button" wire:click="searchData">
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
                                    <a data-no-rawat="{{ $pasien->no_rawat }}" data-no-rekam-medis="{{ $pasien->no_rkm_medis }}" data-pasien="{{ $pasien->data_pasien }}" data-ruangan="{{ $pasien->ruangan }}" data-kamar="{{ $pasien->kd_kamar }}" data-tgl-masuk="{{ $pasien->tgl_masuk }}" data-jam-masuk="{{ $pasien->jam_masuk }}" href="#" style="position: absolute; left: 0; right: 0; top: 0; bottom: 0" onclick="loadData(this.dataset)"></a>
                                </td>
                                <td style="width: 10ch">{{ $pasien->no_rkm_medis }}</td>
                                <td style="width: 25ch">{{ $pasien->data_pasien }}</td>
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
    @endcan --}}

    {{-- @once
        @push('css')
            <link rel="stylesheet" href="{{ asset('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
        @endpush
        @push('js')
            <script src="{{ asset('plugins/moment/moment.min.js') }}"></script>
            <script src="{{ asset('plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
            <script>
                $('#jam_awal').datetimepicker({ format: 'LT' })
                $('#jam_akhir').datetimepicker({ format: 'LT' })
            </script>
        @endpush
    @endonce --}}

    <x-card>
        <x-slot name="header">
            <x-card.row-col>
                <x-filter.range-date model-start="tglAwal" model-end="tglAkhir" />
                <x-filter.button-export-excel class="ml-auto" />
            </x-card.row-col>
            <x-card.row-col class="mt-2">
                <x-filter.range-time />
                <x-filter.select-perpage class="ml-auto" />
            </x-card.row-col>
            <x-card.row-col class="mt-2">
                <x-filter.label constant-width>Status:</x-filter.label>
                <div class="input-group input-group-sm" style="width: max-content">
                    <x-filter.select model="statusPerawatan" :options="[
                        '-' => 'Sedang Dirawat',
                        'tanggal_masuk' => 'Tgl. Masuk',
                        'tanggal_keluar' => 'Tgl. Keluar'
                    ]" />
                </div>
                <x-filter.button-reset-filters class="ml-auto" />
                <x-filter.search />
            </x-card.row-col>
        </x-slot>

        <x-slot name="body" class="table-responsive">
            <x-table style="width: 180rem">
                <x-slot name="columns">
                    <x-table.th style="width: 20ch">No. Rawat</x-table.th>
                    <x-table.th style="width: 10ch">No. RM</x-table.th>
                    <x-table.th>Kamar</x-table.th>
                    <x-table.th style="width: 25ch">Pasien</x-table.th>
                    <x-table.th>Alamat</x-table.th>
                    <x-table.th style="width: 8ch">Agama</x-table.th>
                    <x-table.th style="width: 25ch">P.J.</x-table.th>
                    <x-table.th style="width: 20ch">Jenis Bayar</x-table.th>
                    <x-table.th style="width: 10ch">Asal Poli</x-table.th>
                    <x-table.th style="width: 25ch">Dokter Poli</x-table.th>
                    <x-table.th style="width: 15ch">Status</x-table.th>
                    <x-table.th style="width: 12ch">Tgl. Masuk</x-table.th>
                    <x-table.th style="width: 12ch">Jam Masuk</x-table.th>
                    <x-table.th style="width: 12ch">Tgl. Keluar</x-table.th>
                    <x-table.th style="width: 12ch">Jam Keluar</x-table.th>
                    <x-table.th style="width: 15ch">Tarif</x-table.th>
                    <x-table.th>Dokter P.J.</x-table.th>
                    <x-table.th>No. HP</x-table.th>
                </x-slot>
                <x-slot name="body">
                    @foreach ($this->daftarPasienRanap as $pasien)
                        <x-table.tr>
                            <x-table.td>{{ $pasien->no_rawat }}</x-table.td>
                            <x-table.td>{{ $pasien->no_rkm_medis }}</x-table.td>
                            <x-table.td>{{ $pasien->ruangan }}</x-table.td>
                            <x-table.td>{{ $pasien->data_pasien }}</x-table.td>
                            <x-table.td>{{ $pasien->alamat_pasien }}</x-table.td>
                            <x-table.td>{{ $pasien->agama }}</x-table.td>
                            <x-table.td>{{ $pasien->pj }}</x-table.td>
                            <x-table.td>{{ $pasien->png_jawab }}</x-table.td>
                            <x-table.td>{{ $pasien->nm_poli }}</x-table.td>
                            <x-table.td>{{ $pasien->dokter_poli }}</x-table.td>
                            <x-table.td>{{ $pasien->stts_pulang }}</x-table.td>
                            <x-table.td>{{ $pasien->tgl_masuk }}</x-table.td>
                            <x-table.td>{{ $pasien->jam_masuk }}</x-table.td>
                            <x-table.td>{{ $pasien->tgl_keluar }}</x-table.td>
                            <x-table.td>{{ $pasien->jam_keluar }}</x-table.td>
                            <x-table.td>{{ rp($pasien->trf_kamar) }}</x-table.td>
                            <x-table.td>{{ $pasien->nama_dokter }}</x-table.td>
                            <x-table.td>{{ $pasien->no_tlp }}</x-table.td>
                        </x-table.tr>
                    @endforeach
                </x-slot>
            </x-table>
        </x-slot>

        <x-slot name="footer">
            <x-paginator :data="$this->daftarPasienRanap" />
        </x-slot>
    </x-card>
</div>
