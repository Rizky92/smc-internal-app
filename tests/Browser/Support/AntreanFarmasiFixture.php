<?php

namespace Tests\Browser\Support;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Commits outpatient prescriptions for today into sik_test so they appear on
 * /antrean-farmasi. Dusk drives a separately served app, so a transaction
 * rollback cannot hide these rows from the browser: they are committed and
 * removed again by clean(). Every row carries the DSK marker.
 */
class AntreanFarmasiFixture
{
    public const PREFIX = 'DSK';

    private const KD_DOKTER = 'DSK-DR';

    private const NO_RAWAT_PREFIX = '9999/12/31/';

    private const NO_RESEP_PANGGILAN = 'DSK-PANGGIL';

    public static function seed(int $pengerjaan = 0, int $penyerahan = 0): array
    {
        $db = self::connection();
        $now = carbon($db->selectOne('select now() as now')->now);

        $kdPoli = $db->table('poliklinik')->where('kd_poli', '!=', 'IGDK')->where('kd_poli', '!=', '-')->value('kd_poli');
        $kdPj = $db->table('penjab')->value('kd_pj');
        $lookup = [
            'kd_kel'            => $db->table('kelurahan')->value('kd_kel'),
            'kd_kec'            => $db->table('kecamatan')->value('kd_kec'),
            'kd_kab'            => $db->table('kabupaten')->value('kd_kab'),
            'kd_prop'           => $db->table('propinsi')->value('kd_prop'),
            'perusahaan_pasien' => $db->table('perusahaan_pasien')->value('kode_perusahaan'),
            'suku_bangsa'       => $db->table('suku_bangsa')->value('id'),
            'bahasa_pasien'     => $db->table('bahasa_pasien')->value('id'),
            'cacat_fisik'       => $db->table('cacat_fisik')->value('id'),
        ];

        $names = ['pengerjaan' => [], 'penyerahan' => []];

        self::withoutForeignKeys($db, function (ConnectionInterface $db) use ($pengerjaan, $penyerahan, $now, $kdPoli, $kdPj, $lookup, &$names) {
            $db->table('dokter')->insertOrIgnore([
                'kd_dokter' => self::KD_DOKTER,
                'nm_dokter' => 'dr. Dusk Fixture',
                'email'     => '',
                'status'    => '1',
            ]);

            $seq = (int) $db->table('resep_obat')->where('no_resep', 'like', self::PREFIX.'%')->count();

            foreach (['pengerjaan' => $pengerjaan, 'penyerahan' => $penyerahan] as $kategori => $jumlah) {
                for ($i = 0; $i < $jumlah; $i++) {
                    $seq++;
                    $id = str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
                    $noRkmMedis = self::PREFIX.$id;
                    $noRawat = self::NO_RAWAT_PREFIX.$id;
                    $nama = 'PASIEN DUSK '.$id;
                    // Newest first on screen, all well inside the 120-minute window.
                    $jam = $now->copy()->subMinutes(10 + $seq)->format('H:i:s');

                    $db->table('pasien')->insert(array_merge($lookup, [
                        'no_rkm_medis'  => $noRkmMedis,
                        'nm_pasien'     => $nama,
                        'nm_ibu'        => '-',
                        'umur'          => '30 Th',
                        'pnd'           => '-',
                        'namakeluarga'  => '-',
                        'kd_pj'         => $kdPj,
                        'pekerjaanpj'   => '-',
                        'alamatpj'      => '-',
                        'kelurahanpj'   => '-',
                        'kecamatanpj'   => '-',
                        'kabupatenpj'   => '-',
                        'email'         => '',
                        'nip'           => '',
                        'propinsipj'    => '-',
                    ]));

                    $db->table('reg_periksa')->insert([
                        'no_rawat'       => $noRawat,
                        'tgl_registrasi' => $now->toDateString(),
                        'jam_reg'        => $jam,
                        'kd_dokter'      => self::KD_DOKTER,
                        'no_rkm_medis'   => $noRkmMedis,
                        'kd_poli'        => $kdPoli,
                        'stts_daftar'    => 'Lama',
                        'status_lanjut'  => 'Ralan',
                        'kd_pj'          => $kdPj,
                        'status_bayar'   => 'Belum Bayar',
                        'status_poli'    => 'Lama',
                    ]);

                    $db->table('resep_obat')->insert([
                        'no_resep'       => self::PREFIX.$id,
                        'tgl_perawatan'  => $now->toDateString(),
                        'jam'            => $jam,
                        'no_rawat'       => $noRawat,
                        'kd_dokter'      => self::KD_DOKTER,
                        'tgl_peresepan'  => $now->toDateString(),
                        'jam_peresepan'  => $jam,
                        'status'         => 'ralan',
                        'tgl_penyerahan' => $kategori === 'penyerahan' ? $now->toDateString() : '0000-00-00',
                        'jam_penyerahan' => $kategori === 'penyerahan' ? $jam : '00:00:00',
                    ]);

                    $names[$kategori][] = $nama;
                }
            }
        });

        return $names;
    }

    /**
     * Records a pharmacy counter call for today, marked so clean() can remove it.
     */
    public static function panggil(string $nomor, ?string $jamPanggil = null): void
    {
        $db = self::connection();
        $now = carbon($db->selectOne('select now() as now')->now);

        $db->table('antriloketfarmasi_smc')->insert([
            'nomor'       => $nomor,
            'tanggal'     => $now->toDateString(),
            'jam'         => $now->copy()->subMinutes(30)->format('H:i:s'),
            'jam_panggil' => $jamPanggil ?? $now->format('H:i:s'),
            'no_resep'    => self::NO_RESEP_PANGGILAN,
        ]);
    }

    public static function clean(): void
    {
        self::withoutForeignKeys(self::connection(), function (ConnectionInterface $db) {
            $db->table('antriloketfarmasi_smc')->where('no_resep', self::NO_RESEP_PANGGILAN)->delete();
            $db->table('resep_obat')->where('no_resep', 'like', self::PREFIX.'%')->delete();
            $db->table('reg_periksa')->where('no_rawat', 'like', self::NO_RAWAT_PREFIX.'%')->delete();
            $db->table('pasien')->where('no_rkm_medis', 'like', self::PREFIX.'%')->delete();
            $db->table('dokter')->where('kd_dokter', self::KD_DOKTER)->delete();
        });
    }

    private static function connection(): ConnectionInterface
    {
        $db = DB::connection('mysql_sik');

        if ($db->getDatabaseName() !== 'sik_test') {
            throw new RuntimeException("AntreanFarmasiFixture only writes to sik_test, got [{$db->getDatabaseName()}].");
        }

        return $db;
    }

    private static function withoutForeignKeys(ConnectionInterface $db, callable $callback): void
    {
        // sik_test has no pegawai rows for dokter to reference; the display query never joins pegawai.
        $db->statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            $db->statement("SET SESSION sql_mode = REPLACE(@@SESSION.sql_mode, 'NO_ZERO_DATE', '')");
            $callback($db);
        } finally {
            $db->statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }
}
