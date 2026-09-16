<?php

namespace Tests\Concerns;

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Models\Aplikasi\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * Builds the identity graph a SIAP user is made of.
 *
 * A user is not one row. User::booted() joins `pegawai` on
 * trim(AES_DECRYPT(id_user, "nur")) = trim(pegawai.nik), then left-joins
 * `petugas`, `jabatan`, `dokter` and `spesialis`. A `user` row on its own is
 * invisible to every query in the app, so the fixture has to write the graph.
 *
 * Permissions live in mysql_smc while the identity lives in mysql_sik; the two
 * are tied together only by the id_user string, with no foreign key between them.
 */
trait CreatesPetugas
{
    /**
     * The NIK every fixture in this suite writes under.
     *
     * Fixtures are committed rather than rolled back (see Tests\TestCase), so
     * cleanup is by hand. Using one reserved prefix means cleanup can find every
     * row a test wrote without tracking them individually — including rows left
     * behind by a run that died partway.
     */
    private function fixtureNikPrefix(): string
    {
        return '9999990';
    }

    /**
     * Remove every petugas this suite has ever written.
     *
     * Children before parents: petugas.nip is a foreign key into pegawai.nik.
     * `user` is matched on the decrypted id_user, since that is the only thing
     * tying it to a NIK.
     */
    protected function deletePetugasFixtures(): void
    {
        $sik = DB::connection('mysql_sik');
        $like = $this->fixtureNikPrefix().'%';

        $sik->table('user')
            ->whereRaw('AES_DECRYPT(id_user, ?) like ?', [$this->khanzaUserKey(), $like])
            ->delete();

        $sik->table('petugas')->where('nip', 'like', $like)->delete();
        $sik->table('pegawai')->where('nik', 'like', $like)->delete();
    }

    /**
     * The AES key Khanza encrypts id_user with. Hardcoded in User::booted()'s
     * global scope as "nur"; the fixture must use the same key or the join that
     * makes a user visible will not match.
     *
     * A method rather than a constant because traits cannot declare constants
     * before PHP 8.2, and SIAP still runs on 8.1.
     */
    private function khanzaUserKey(): string
    {
        return 'nur';
    }

    /**
     * Create a petugas holding exactly the given permissions, and nothing else.
     *
     * @param  list<string>  $permissions
     */
    protected function petugasWithPermissions(array $permissions = [], string $nik = '99999901', ?string $nama = null): User
    {
        $this->createPegawaiRecord($nik, $nama);

        $petugas = User::findByNRP($nik);

        if ($petugas === null) {
            $this->fail(sprintf(
                'Fixture for NIK [%s] was written but is not visible through the User model. '
                .'The AES_DECRYPT join in User::booted() did not match.',
                $nik
            ));
        }

        $this->attachPermissions($petugas, $permissions);

        return $petugas;
    }

    /**
     * Create a petugas holding a role, and no direct permissions.
     *
     * Kept separate from petugasWithPermissions() because authorisation reaches a
     * user by two independent routes — model_has_roles and model_has_permissions —
     * and a test usually needs to exercise exactly one of them.
     */
    protected function petugasWithRole(string $role, string $nik = '99999901', ?string $nama = null): User
    {
        $this->createPegawaiRecord($nik, $nama);

        $petugas = User::findByNRP($nik);

        if ($petugas === null) {
            $this->fail(sprintf('Fixture for NIK [%s] is not visible through the User model.', $nik));
        }

        $petugas->assignRole(Role::findOrCreate($role, 'web'));

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $petugas;
    }

    /**
     * @param  list<string>  $permissions
     */
    private function attachPermissions(User $petugas, array $permissions): void
    {
        if ($permissions === []) {
            return;
        }

        // App\Models\Aplikasi\Permission, never Spatie's own model. Spatie's
        // default declares no connection, so it would inherit User's mysql_sik;
        // worse, calling a static on it rewrites permission.models.permission via
        // PermissionRegistrar::setPermissionClass(), breaking the relation for the
        // rest of the test.
        $models = array_map(
            fn (string $permission): Permission => Permission::findOrCreate($permission, 'web'),
            $permissions
        );

        $petugas->givePermissionTo($models);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function createPegawaiRecord(string $nik, ?string $nama = null): void
    {
        $nama = $nama ?? 'Petugas Uji '.$nik;

        $sik = DB::connection('mysql_sik');

        $sik->table('pegawai')->insert([
            'nik'             => $nik,
            'nama'            => $nama,
            'jk'              => 'Pria',
            'jbtn'            => 'Staf',
            'jnj_jabatan'     => '-',
            'kode_kelompok'   => '-',
            'kode_resiko'     => '-',
            'kode_emergency'  => '-',
            'departemen'      => '-',
            'bidang'          => '-',
            'stts_wp'         => '-',
            'stts_kerja'      => '-',
            'npwp'            => '-',
            'pendidikan'      => '-',
            'gapok'           => 0,
            'tmp_lahir'       => '-',
            'tgl_lahir'       => '1990-01-01',
            'alamat'          => '-',
            'kota'            => '-',
            'mulai_kerja'     => '2020-01-01',
            'ms_kerja'        => 'FT>1',
            'indexins'        => '-',
            // Every other lookup Khanza's pegawai FKs point at carries a "-" row;
            // `bank` does not, so this one has to name a real value.
            'bpd'             => 'TUNAI',
            'rekening'        => '-',
            'stts_aktif'      => 'AKTIF',
            'wajibmasuk'      => 0,
            'pengurang'       => 0,
            'indek'           => 0,
            'cuti_diambil'    => 0,
            'dankes'          => 0,
            'no_ktp'          => $nik,
        ]);

        $sik->table('petugas')->insert([
            'nip'   => $nik,
            'nama'  => $nama,
            'email' => $nik.'@test.invalid',
        ]);

        // id_user is AES-encrypted, so it cannot go through the query builder's
        // binding for the column value directly.
        $sik->statement(
            'insert into `user` (`id_user`, `password`) values (AES_ENCRYPT(?, ?), ?)',
            [$nik, $this->khanzaUserKey(), '']
        );
    }
}
