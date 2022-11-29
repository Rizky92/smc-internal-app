<table>
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
            <th>ICD 9CM</th>
            <th>Tindakan</th>
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
            @endphp
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
                <td>{{ $registrasi->tgl_registrasi }}</td>
                <td>{{ $registrasi->jam_reg }}</td>
                <td>{{ $registrasi->tgl_keluar }}</td>
                <td>{{ $registrasi->jam_keluar }}</td>
                <td>{{ $registrasi->diagnosa_awal }}</td>
                <td>{{ optional(optional($registrasi->diagnosa)->first())->kd_penyakit ?? '-' }}</td>
                <td>{{ optional(optional($registrasi->diagnosa)->first())->nm_penyakit ?? '-' }}</td>
                <td>{!! $kdDiagnosaSekunder ?? '-' !!}</td>
                <td>{!! $nmDiagnosaSekunder ?? '-' !!}</td>
                {{-- <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td> --}}
                <td>{{ $registrasi->kode_tindakan }}</td>
                <td>{{ $registrasi->nama_tindakan }}</td>
                <td>-</td>
                <td>-</td>
                <td>{{ $registrasi->nm_dokter }}</td>
                <td>{{ $registrasi->nm_poli }}</td>
                <td>{{ $registrasi->kelas }}</td>
                <td>{{ $registrasi->png_jawab }}</td>
                <td>{{ $registrasi->stts }}</td>
                <td>-</td>
                <td>{{ $registrasi->no_tlp }}</td>
                <td>{{ $registrasi->alamat }}</td>
                <td>{{ $registrasi->kunjungan_ke }}</td>
            </tr>
        @endforeach
        @php
            unset($kdDiagnosaSekunder);
            unset($nmDiagnosaSekunder);
        @endphp
    </tbody>
</table>
