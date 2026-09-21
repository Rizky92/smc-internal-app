<?php

namespace Tests\Feature\Livewire\Aplikasi;

use App\Livewire\Pages\Aplikasi\ManajemenPintu;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: the pintu list, with each mapped doctor/poli pair grouped beneath
 * its own row.
 *
 * getPintuProperty() runs two separate queries and merges them in PHP - a
 * paginated Pintu list, and a raw set_pintu_smc join grouped by kd_pintu -
 * because the grouping can't happen in the same paginated query. A pintu
 * with two mappings has to show both merged onto the one row, not just that
 * the page renders; the write path (InputPintuTest) already covers the
 * modal this page opens.
 */
class ManajemenPintuTest extends TestCase
{
    private const KODE = 'UJI-PINTU';

    protected function tearDown(): void
    {
        $sik = DB::connection('mysql_sik');

        $sik->table('set_pintu_smc')->where('kd_pintu', 'like', 'UJI-%')->delete();
        $sik->table('pintu_smc')->where('kd_pintu', 'like', 'UJI-%')->delete();
        $sik->table('dokter')->where('kd_dokter', 'like', '9999990%')->delete();
        $sik->table('poliklinik')->where('kd_poli', 'like', 'UJI%')->delete();

        parent::tearDown();
    }

    private function poliklinik(string $kdPoli): void
    {
        DB::connection('mysql_sik')->table('poliklinik')->insert([
            'kd_poli' => $kdPoli, 'nm_poli' => 'Poli Uji '.$kdPoli,
            'registrasi' => 0, 'registrasilama' => 0, 'status' => '1',
        ]);
    }

    private function dokter(string $nik, string $nama): void
    {
        $this->petugasWithPermissions([], $nik, $nama);

        DB::connection('mysql_sik')->table('dokter')->insert([
            'kd_dokter' => $nik, 'nm_dokter' => $nama, 'email' => $nik.'@test.invalid', 'status' => '1',
        ]);
    }

    /**
     * @test
     */
    public function groups_multiple_mappings_under_the_same_pintu(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');

        $this->poliklinik('UJI01');
        $this->poliklinik('UJI02');
        $this->dokter('99999902', 'Dokter Uji Satu');
        $this->dokter('99999903', 'Dokter Uji Dua');

        DB::connection('mysql_sik')->table('pintu_smc')->insert(['kd_pintu' => self::KODE, 'nm_pintu' => 'Pintu Uji', 'status' => '1']);
        DB::connection('mysql_sik')->table('set_pintu_smc')->insert(['kd_pintu' => self::KODE, 'kd_dokter' => '99999902', 'kd_poli' => 'UJI01']);
        DB::connection('mysql_sik')->table('set_pintu_smc')->insert(['kd_pintu' => self::KODE, 'kd_dokter' => '99999903', 'kd_poli' => 'UJI02']);

        Livewire::actingAs($petugas)
            ->test(ManajemenPintu::class)
            ->call('loadProperties')
            ->assertSee('Dokter Uji Satu')
            ->assertSee('Dokter Uji Dua');
    }

    /**
     * @test
     *
     * search() reaches into the mapped doctor/poli names via a correlated
     * subquery on Pintu (see Pintu::searchColumns()), so a pintu with no
     * name match of its own must still surface by its doctor's name.
     */
    public function finds_a_pintu_by_its_mapped_doctors_name(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');

        $this->poliklinik('UJI01');
        $this->dokter('99999902', 'Dokter Uji Satu');

        DB::connection('mysql_sik')->table('pintu_smc')->insert(['kd_pintu' => self::KODE, 'nm_pintu' => 'Pintu Uji', 'status' => '1']);
        DB::connection('mysql_sik')->table('set_pintu_smc')->insert(['kd_pintu' => self::KODE, 'kd_dokter' => '99999902', 'kd_poli' => 'UJI01']);

        Livewire::actingAs($petugas)
            ->test(ManajemenPintu::class)
            ->call('loadProperties')
            ->set('cari', 'Dokter Uji Satu')
            ->assertSee(self::KODE);
    }
}
