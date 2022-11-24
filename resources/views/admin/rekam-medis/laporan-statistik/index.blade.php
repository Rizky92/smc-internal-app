@extends('layouts.admin', [
    'title' => 'Laporan Statistik',
])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-responsive p-0" style="max-height: 480px">
                    <table id="table_index" class="table table-hover table-head-fixed table-striped table-sm text-sm" style="width: 400rem">
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
                                <th>ICD_9CM</th>
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
                                    <td>{{ optional($registrasi->rawatInap->first())->tgl_keluar ?? '' }}</td>
                                    <td>{{ optional($registrasi->rawatInap->first())->jam_keluar ?? '' }}</td>
                                    <td>{{ optional($registrasi->rawatInap->first())->diagnosa_masuk ?? '' }}</td>
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
                                    <td></td>
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
            </div>
        </div>
    </div>
@endsection
