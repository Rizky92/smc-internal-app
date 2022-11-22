@extends('layouts.admin', [
    'title' => 'Laporan Statistik',
])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-responsive p-0">
                    <table id="table_index" class="table table-hover table-striped table-sm text-sm" style="width: 180rem">
                        <thead>
                            <tr>
                                <th>No. Rawat</th>
                                <th>No. Rekam Medis</th>
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
                                {{-- <th>Diagnosa Primer</th>
                                <th>ICD Primer</th>
                                <th>Diagnosa Sekunder</th>
                                <th>ICD Sekunder</th>
                                <th>Tindakan</th>
                                <th>ICD_9CM</th>
                                <th>Lama Operasi</th>
                                <th>Rujukan Asal</th>
                                <th>DPJP</th>
                                <th>Perawatan</th> --}}
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
                                    <td>{{ optional($registrasi->pasien)->umur }} Tahun</td>
                                    <td>{{ optional($registrasi->pasien)->agama }}</td>
                                    <td>{{ optional(optional($registrasi->pasien)->suku)->nama_suku_bangsa }}</td>
                                    <td>{{ $registrasi->status_lanjut }}</td>
                                    <td>{{ $registrasi->status_poli }}</td>
                                    <td>{{ $registrasi->tgl_registrasi }}</td>
                                    <td>{{ $registrasi->jam_reg }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
