<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\DB;

/**
 * Creates a patient and a visit in SIMRS Khanza.
 *
 * `pasien` has twenty-two columns that are NOT NULL with no default and nine
 * foreign keys — into geography, ethnicity, language, disability, employer and
 * payer — and `reg_periksa` adds four more. Nothing about most of this matters
 * to the reports under test; it simply cannot be inserted without it, which is
 * why the reports that read patient data went untested for so long.
 *
 * The lookup values are read from the reference tables rather than hardcoded, so
 * this keeps working when Khanza's reference data is refreshed. Those tables are
 * seeded by database/testing/rebuild-test-schemas.ps1.
 */
trait CreatesPasien
{
    /**
     * Reserved so cleanup can find whatever a test wrote.
     */
    private function fixtureRekamMedisPrefix(): string
    {
        return 'UJI-RM';
    }

    protected function deletePasienFixtures(): void
    {
        $sik = DB::connection('mysql_sik');
        $like = $this->fixtureRekamMedisPrefix().'%';

        // Children before parents.
        $sik->table('reg_periksa')->where('no_rkm_medis', 'like', $like)->delete();
        $sik->table('pasien')->where('no_rkm_medis', 'like', $like)->delete();
    }

    /**
     * One valid value from each table `pasien` points at.
     *
     * @return array<string, mixed>
     */
    private function nilaiReferensiPasien(): array
    {
        $sik = DB::connection('mysql_sik');

        return [
            'kd_pj'             => $sik->table('penjab')->value('kd_pj'),
            'kd_kel'            => $sik->table('kelurahan')->value('kd_kel'),
            'kd_kec'            => $sik->table('kecamatan')->value('kd_kec'),
            'kd_kab'            => $sik->table('kabupaten')->value('kd_kab'),
            'kd_prop'           => $sik->table('propinsi')->value('kd_prop'),
            'perusahaan_pasien' => $sik->table('perusahaan_pasien')->value('kode_perusahaan'),
            'suku_bangsa'       => $sik->table('suku_bangsa')->value('id'),
            'bahasa_pasien'     => $sik->table('bahasa_pasien')->value('id'),
            'cacat_fisik'       => $sik->table('cacat_fisik')->value('id'),
        ];
    }

    protected function createPasien(string $noRekamMedis, string $nama = 'Pasien Uji'): void
    {
        DB::connection('mysql_sik')->table('pasien')->insert(array_merge(
            $this->nilaiReferensiPasien(),
            [
                'no_rkm_medis'  => $noRekamMedis,
                'nm_pasien'     => $nama,
                'nm_ibu'        => 'Ibu Uji',
                'umur'          => '30 Th',
                'pnd'           => '-',
                'namakeluarga'  => 'Keluarga Uji',
                'pekerjaanpj'   => '-',
                'alamatpj'      => '-',
                'kelurahanpj'   => '-',
                'kecamatanpj'   => '-',
                'kabupatenpj'   => '-',
                'propinsipj'    => '-',
                'email'         => $noRekamMedis.'@test.invalid',
                'nip'           => '-',
            ]
        ));
    }

    /**
     * A visit. kd_dokter is left null on purpose: it is a nullable foreign key
     * into `dokter`, and none of these reports need a doctor to exist.
     */
    protected function createRegistrasi(
        string $noRawat,
        string $noRekamMedis,
        string $tanggal,
        string $statusLanjut = 'Ranap',
        ?string $kodePenjamin = null
    ): void {
        $sik = DB::connection('mysql_sik');

        $sik->table('reg_periksa')->insert([
            'no_rawat'       => $noRawat,
            'no_rkm_medis'   => $noRekamMedis,
            'tgl_registrasi' => $tanggal,
            'jam_reg'        => '09:00:00',
            'kd_poli'        => $sik->table('poliklinik')->value('kd_poli'),
            'kd_pj'          => $kodePenjamin ?? $sik->table('penjab')->value('kd_pj'),
            'stts_daftar'    => 'Baru',
            'status_lanjut'  => $statusLanjut,
            'status_bayar'   => 'Belum Bayar',
            'status_poli'    => 'Baru',
        ]);
    }
}
