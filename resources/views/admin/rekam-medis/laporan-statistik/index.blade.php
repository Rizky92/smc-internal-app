@extends('layouts.admin', [
    'title' => 'Laporan Statistik',
])

@once
    @push('css')
        <link rel="stylesheet" href="{{ asset('plugins/daterangepicker/daterangepicker.css') }}">
        <link rel="stylesheet" href="{{ asset('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
        @livewireStyles
    @endpush
    @push('js')
        @livewireScripts
        <script src="{{ asset('plugins/daterangepicker/daterangepicker.js') }}"></script>
        <script src="{{ asset('plugins/moment/moment.min.js') }}"></script>
        <script src="{{ asset('plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>

        <script>
            $(document).ready(() => {
                $('#datemin').datetimepicker({
                    format: 'DD-MM-yyyy'
                })

                $('#datemax').datetimepicker({
                    format: 'DD-MM-yyyy'
                })
            })
        </script>
    @endpush
@endonce

@section('content')
    <div class="row">
        <div class="col-12">
            {{-- <div class="card">
                <div class="card-body" id="table_filter_action">
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-start">
                                <span class="text-sm pr-4">Periode:</span>
                                <div class="input-group input-group-sm date w-25" id="datemin" data-target-input="nearest">
                                    <div class="input-group-prepend" data-target="#datemin" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                    <input type="text" name="periode_awal" class="form-control datetimepicker-input" data-target="#datemin" value="{{ old('periode_awal', now()->startOfMonth()->format('d-m-Y')) }}" />
                                </div>
                                <span class="text-sm px-2">Sampai</span>
                                <div class="input-group input-group-sm date w-25" id="datemax" data-target-input="nearest">
                                    <div class="input-group-prepend" data-target="#datemax" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                    <input type="text" name="periode_akhir" class="form-control datetimepicker-input" data-target="#datemax" value="{{ old('periode_akhir', now()->endOfMonth()->format('d-m-Y')) }}" />
                                </div>
                                <div class="ml-auto">
                                    <button class="btn btn-default btn-sm" type="button">
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
                                    <select name="perpage" class="custom-control custom-select">
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                        <option value="200">200</option>
                                        <option value="500">500</option>
                                        <option value="1000">1000</option>
                                        <option value="semua">semua</option>
                                    </select>
                                </div>
                                <span class="text-sm pl-2">per halaman</span>
                                <span class="text-sm ml-auto pr-2">Cari:</span>
                                <div class="input-group input-group-sm" style="width: 12rem">
                                    <input type="search" name="search" class="form-control" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table id="rekammedis_table" class="table table-hover table-head-fixed table-striped table-sm text-sm" style="width: 480rem">
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
                                    <td>{{ optional($registrasi->rawatInap->first())->pivot->tgl_keluar ?? '' }}</td>
                                    <td>{{ optional($registrasi->rawatInap->first())->pivot->jam_keluar ?? '' }}</td>
                                    <td>{{ optional($registrasi->rawatInap->first())->pivot->diagnosa_awal ?? '' }}</td>
                                    <td>{{ optional(optional($registrasi->diagnosa)->first())->kd_penyakit ?? '-' }}</td>
                                    <td>{{ optional(optional($registrasi->diagnosa)->first())->nm_penyakit ?? '-' }}</td>
                                    @php
                                        $diagnosa = $registrasi->diagnosa->take(1 - $registrasi->diagnosa->count());
                                        $kdDiagnosa = $diagnosa->reduce(function ($carry, $item) {
                                            return $item->kd_penyakit . '; <br>' . $carry;
                                        });
                                        
                                        $nmDiagnosa = $diagnosa->reduce(function ($carry, $item) {
                                            return $item->nm_penyakit . '; <br>' . $carry;
                                        });
                                    @endphp
                                    <td>{!! $kdDiagnosa ?? '-' !!}</td>
                                    <td>{!! $nmDiagnosa ?? '-' !!}</td>
                                    <td colspan="4"></td>
                                    <td>{{ optional($registrasi->dokter)->nm_dokter }}</td>
                                    <td>{{ optional($registrasi->poliklinik)->nm_poli }}</td>
                                    <td>{{ optional($registrasi->rawatInap->first())->kelas }}</td>
                                    <td>{{ optional($registrasi->penjamin)->png_jawab }}</td>
                                    <td>{{ $registrasi->stts }}</td>
                                    <td></td>
                                    <td>{{ optional($registrasi->pasien)->no_tlp }}</td>
                                    <td>{{ optional($registrasi->pasien)->alamat }}</td>
                                    <td></td>
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
            </div> --}}
            @livewire('rekam-medis-data-table-component')
        </div>
    </div>
@endsection
