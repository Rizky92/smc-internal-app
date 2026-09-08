<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Pages\Aplikasi\Modal\InputPintu;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: the modal that maps a pintu to doctor/clinic pairs.
 *
 * The densest event surface in the application: two #[On] listeners and every
 * write path announcing itself by dispatch. Both listeners are fed by JavaScript
 * — select2 sends whatever it happens to have — and the write paths are gated on
 * three separate permissions. None of that is exercised by asking whether the
 * page returns 200.
 */
class InputPintuTest extends TestCase
{
    /**
     * Reserved so cleanup can find whatever a test wrote.
     */
    private const KODE = 'UJI-PINTU';

    protected function tearDown(): void
    {
        $sik = DB::connection('mysql_sik');

        // Children before parents: set_pintu_smc.kd_pintu points at pintu_smc.
        $sik->table('set_pintu_smc')->where('kd_pintu', 'like', 'UJI-%')->delete();
        $sik->table('pintu_smc')->where('kd_pintu', 'like', 'UJI-%')->delete();
        $sik->table('dokter')->where('kd_dokter', 'like', '9999990%')->delete();
        $sik->table('poliklinik')->where('kd_poli', 'like', 'UJI%')->delete();

        parent::tearDown();
    }

    /**
     * @test
     *
     * select2 does not send a consistent shape. The listener has to cope with a
     * JSON array, a comma-separated string and an empty string, because all three
     * arrive from the browser depending on how the field was touched.
     *
     * @dataProvider selectedJadwalPayloads
     *
     * @param  list<string>  $expected
     */
    public function normalises_whatever_select2_sends(mixed $payload, array $expected): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');

        Livewire::actingAs($petugas)
            ->test(InputPintu::class)
            ->dispatch('inputPintu.setSelectedJadwal', $payload)
            ->assertSet('selectedJadwal', $expected);
    }

    /**
     * @return array<string, array{0: mixed, 1: list<string>}>
     */
    public static function selectedJadwalPayloads(): array
    {
        return [
            'json array'       => ['["D001|POL1","D002|POL2"]', ['D001|POL1', 'D002|POL2']],
            'comma separated'  => ['D001|POL1,D002|POL2', ['D001|POL1', 'D002|POL2']],
            'empty string'     => ['', []],
            'already an array' => [['D001|POL1'], ['D001|POL1']],
        ];
    }

    /**
     * @test
     *
     * The three write paths are gated on separate permissions. A petugas holding
     * none of them must be refused and told so, and nothing may reach the
     * database.
     *
     * @dataProvider guardedActions
     */
    public function refuses_a_write_to_a_petugas_without_the_permission(string $action): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');

        Livewire::actingAs($petugas)
            ->test(InputPintu::class)
            ->set('kodePintu', self::KODE)
            ->set('namaPintu', 'Pintu Uji')
            ->set('selectedJadwal', ['99999902|UJI01'])
            ->set('originalKodePintu', self::KODE)
            ->call($action)
            ->assertDispatched('data-denied');

        // Third argument is required: these tables live on mysql_sik, but the
        // default connection is mysql_smc.
        $this->assertDatabaseMissing('pintu_smc', ['kd_pintu' => self::KODE], 'mysql_sik');
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function guardedActions(): array
    {
        return ['create' => ['create'], 'update' => ['update'], 'delete' => ['delete']];
    }

    /**
     * @test
     */
    public function saves_a_pintu_and_its_jadwal_mappings(): void
    {
        $petugas = $this->petugasWithPermissions(['antrean.manajemen-pintu.create'], '99999901');
        $this->createPoliklinik('UJI01');
        $this->createDokter('99999902');

        Livewire::actingAs($petugas)
            ->test(InputPintu::class)
            ->set('kodePintu', self::KODE)
            ->set('namaPintu', 'Pintu Uji')
            ->set('kodeDokter', '99999902')
            ->set('kodePoliklinik', 'UJI01')
            ->set('selectedJadwal', ['99999902|UJI01'])
            ->call('create')
            ->assertDispatched('data-saved')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('pintu_smc', ['kd_pintu' => self::KODE, 'nm_pintu' => 'Pintu Uji'], 'mysql_sik');
        $this->assertDatabaseHas('set_pintu_smc', [
            'kd_pintu'  => self::KODE,
            'kd_dokter' => '99999902',
            'kd_poli'   => 'UJI01',
        ], 'mysql_sik');
    }

    /**
     * @test
     *
     * prepare() is how the table hands a row to this modal. It has to fill the
     * fields and rebuild selectedJadwal in the "kd_dokter|kd_poli" shape select2
     * expects, then tell the front end to resync.
     */
    public function loading_an_existing_pintu_fills_the_form(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');
        $this->createPoliklinik('UJI01');
        $this->createDokter('99999902');

        DB::connection('mysql_sik')->table('pintu_smc')->insert([
            'kd_pintu' => self::KODE, 'nm_pintu' => 'Pintu Uji', 'status' => '1',
        ]);
        DB::connection('mysql_sik')->table('set_pintu_smc')->insert([
            'kd_pintu' => self::KODE, 'kd_dokter' => '99999902', 'kd_poli' => 'UJI01',
        ]);

        Livewire::actingAs($petugas)
            ->test(InputPintu::class)
            ->dispatch('prepare', ['kd_pintu' => self::KODE])
            ->assertSet('kodePintu', self::KODE)
            ->assertSet('namaPintu', 'Pintu Uji')
            ->assertSet('selectedJadwal', ['99999902|UJI01'])
            ->assertDispatched('inputPintu.syncSelectedJadwal');
    }

    /**
     * @test
     *
     * prepare() with nothing selected is the "add new" path, and must leave no
     * trace of whatever was in the modal before.
     */
    public function preparing_without_a_pintu_resets_to_create_mode(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');

        Livewire::actingAs($petugas)
            ->test(InputPintu::class)
            ->set('kodePintu', 'SISA')
            ->set('originalKodePintu', 'SISA')
            ->dispatch('prepare', [])
            ->assertSet('kodePintu', '')
            ->assertSet('originalKodePintu', null);
    }

    private function createPoliklinik(string $kdPoli): void
    {
        DB::connection('mysql_sik')->table('poliklinik')->insert([
            'kd_poli'        => $kdPoli,
            'nm_poli'        => 'Poli Uji',
            'registrasi'     => 0,
            'registrasilama' => 0,
            'status'         => '1',
        ]);
    }

    /**
     * dokter.kd_dokter is a foreign key into pegawai.nik, so the doctor has to be
     * an existing pegawai first.
     */
    private function createDokter(string $nik): void
    {
        $this->petugasWithPermissions([], $nik, 'Dokter Uji');

        DB::connection('mysql_sik')->table('dokter')->insert([
            'kd_dokter' => $nik,
            'nm_dokter' => 'Dokter Uji',
            'email'     => $nik.'@test.invalid',
            'status'    => '1',
        ]);
    }

    /**
     * @test
     *
     * A single selection also fills the two code fields, which is what the save
     * path validates against.
     */
    public function a_single_selection_fills_the_code_fields(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');

        Livewire::actingAs($petugas)
            ->test(InputPintu::class)
            ->dispatch('inputPintu.setSelectedJadwal', ['D001|POL1'])
            ->assertSet('kodeDokter', 'D001')
            ->assertSet('kodePoliklinik', 'POL1');
    }
}
