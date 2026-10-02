<?php

namespace Tests\Feature\Mutu;

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Persiapan sebelum menjalankan test Mutu:
 * - `php artisan optimize:clear` (config cache membuat test mengabaikan .env.testing)
 * - `php artisan migrate --env=testing` agar tabel Mutu ada di smc_test
 *
 * Semua data dibuat di dalam transaksi pada kedua koneksi sehingga sik_test dan smc_test
 * kembali seperti semula setelah test selesai.
 */
abstract class MutuTestCase extends TestCase
{
    use DatabaseTransactions;

    protected const NIK = 'MUTU-TEST-01';

    protected const DEP_ID = 'IT';

    /** @var string[] */
    protected $connectionsToTransact = ['mysql_smc', 'mysql_sik'];

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function createUser(string $nik = self::NIK, array $permissions = ['mutu.*'], string $departemen = self::DEP_ID): User
    {
        $sik = DB::connection('mysql_sik');

        $sik->statement('set foreign_key_checks = 0');

        $sik->table('pegawai')->insert([
            'nik'            => $nik,
            'nama'           => "Pegawai {$nik}",
            'jk'             => 'Pria',
            'jbtn'           => '-',
            'jnj_jabatan'    => '-',
            'kode_kelompok'  => '-',
            'kode_resiko'    => '-',
            'kode_emergency' => '-',
            'departemen'     => $departemen,
            'bidang'         => '-',
            'stts_wp'        => '-',
            'stts_kerja'     => '-',
            'npwp'           => '-',
            'pendidikan'     => '-',
            'gapok'          => 0,
            'tmp_lahir'      => '-',
            'tgl_lahir'      => '1990-01-01',
            'alamat'         => '-',
            'kota'           => '-',
            'mulai_kerja'    => '2020-01-01',
            'ms_kerja'       => 'FT>1',
            'indexins'       => '-',
            'bpd'            => '-',
            'rekening'       => '-',
            'stts_aktif'     => 'AKTIF',
            'wajibmasuk'     => 0,
            'pengurang'      => 0,
            'indek'          => 0,
            'cuti_diambil'   => 0,
            'dankes'         => 0,
            'no_ktp'         => '-',
        ]);

        // Tabel `user` Khanza memakai MyISAM sehingga tidak ikut di-rollback; hapus manual.
        $deleteUser = fn () => $sik->delete(
            'delete from user where id_user = aes_encrypt(?, ?)',
            [$nik, config('khanza.app.userkey')]
        );

        $deleteUser();
        $this->beforeApplicationDestroyed($deleteUser);

        $sik->insert(
            'insert into user (id_user, password) values (aes_encrypt(?, ?), aes_encrypt(?, ?))',
            [$nik, config('khanza.app.userkey'), 'secret', config('khanza.app.passkey')]
        );

        $sik->statement('set foreign_key_checks = 1');

        $user = User::findByNRP($nik);

        $names = collect($permissions)
            ->flatMap(fn (string $name): array => $name === 'mutu.*' ? $this->mutuPermissions() : [$name]);

        $user->givePermissionTo(
            $names->map(fn (string $name): Permission => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']))->all()
        );

        return $user;
    }

    /**
     * @return string[]
     */
    protected function mutuPermissions(): array
    {
        return [
            'mutu.kategori-indikator.create',
            'mutu.kategori-indikator.read',
            'mutu.kategori-indikator.update',
            'mutu.kategori-indikator.delete',
            'mutu.tipe-input-indikator.create',
            'mutu.tipe-input-indikator.read',
            'mutu.tipe-input-indikator.update',
            'mutu.tipe-input-indikator.delete',
            'mutu.indikator-mutu.create',
            'mutu.indikator-mutu.read',
            'mutu.indikator-mutu.update',
            'mutu.indikator-mutu.delete',
            'mutu.validasi-data.read',
            'mutu.validasi-data.approve',
            'mutu.validasi-data.reject',
        ];
    }
}
