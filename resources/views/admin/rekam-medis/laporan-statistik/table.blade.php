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
                $jamKeluar = optional(optional($registrasi->rawatInap->first())->pivot)->jam_keluar;
                
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
                <td>{{ $registrasi->tgl_registrasi }}</td>
                <td>{{ $registrasi->jam_reg }}</td>
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
