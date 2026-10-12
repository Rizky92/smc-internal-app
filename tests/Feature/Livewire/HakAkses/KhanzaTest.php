<?php

namespace Tests\Feature\Livewire\HakAkses;

use App\Livewire\Pages\HakAkses\Khanza;
use App\Models\Aplikasi\HakAkses;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: mapping SIMRS Khanza's own permission columns into SIAP's
 * khanza_mapping_akses config table.
 *
 * syncHakAkses() reads a hardcoded reference NRP ('221203'), diffs its
 * Khanza-side boolean columns against khanza_mapping_akses, then runs
 * User::query()->update(...) against every row in mysql_sik.user for any
 * newly-discovered column. That mass update touches the whole user table, so
 * this suite deliberately does not exercise a genuine diff - only the two
 * permission guards and simpanHakAkses(), which writes a single, scoped row.
 * Note for whoever revisits this: syncHakAkses() has no guard for the
 * reference NRP not existing at all - User::rawFindByNRP('221203') returning
 * null would fatal on ->getAttributes(), untested here on purpose.
 */
class KhanzaTest extends TestCase
{
    protected function tearDown(): void
    {
        DB::connection('mysql_smc')->table('khanza_mapping_akses')->where('nama_field', 'like', 'uji_%')->delete();

        parent::tearDown();
    }

    /**
     * @test
     */
    public function refuses_to_sync_hak_akses_without_the_superadmin_role(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');

        Livewire::actingAs($petugas)
            ->test(Khanza::class)
            ->call('syncHakAkses')
            ->assertSee('Anda tidak diizinkan untuk melakukan tindakan ini!');
    }

    /**
     * @test
     */
    public function refuses_to_save_a_mapping_without_the_superadmin_role(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');

        Livewire::actingAs($petugas)
            ->test(Khanza::class)
            ->call('simpanHakAkses', 'uji_field', 'Uji Menu')
            ->assertSee('Anda tidak diizinkan untuk melakukan tindakan ini!');

        $this->assertNull(HakAkses::find('uji_field'));
    }

    /**
     * @test
     */
    public function saves_a_new_hak_akses_mapping(): void
    {
        $petugas = $this->petugasWithRole(config('permission.superadmin_name'), '99999901');

        Livewire::actingAs($petugas)
            ->test(Khanza::class)
            ->call('simpanHakAkses', 'uji_field', 'Uji Menu')
            ->assertSee('Hak akses berhasil disimpan!');

        $hakAkses = HakAkses::findOrFail('uji_field');
        $this->assertSame('Uji Menu', $hakAkses->judul_menu);
        $this->assertFalse($hakAkses->default_value);
    }

    /**
     * @test
     *
     * updateOrCreate() keyed on nama_field: saving the same field again
     * updates the title in place instead of creating a duplicate row.
     */
    public function saving_an_existing_mapping_updates_its_title_instead_of_duplicating(): void
    {
        $petugas = $this->petugasWithRole(config('permission.superadmin_name'), '99999901');

        HakAkses::create(['nama_field' => 'uji_field', 'judul_menu' => 'Judul Lama', 'default_value' => false]);

        Livewire::actingAs($petugas)
            ->test(Khanza::class)
            ->call('simpanHakAkses', 'uji_field', 'Judul Baru');

        $this->assertSame(1, HakAkses::query()->where('nama_field', 'uji_field')->count());
        $this->assertSame('Judul Baru', HakAkses::findOrFail('uji_field')->judul_menu);
    }

    /**
     * @test
     *
     * khanza_mapping_akses already holds over a thousand real rows, so a
     * fixture row isn't guaranteed to land on the default first page - the
     * search itself, not unfiltered visibility, is what this pins.
     */
    public function narrows_the_table_to_the_search_term(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');

        HakAkses::create(['nama_field' => 'uji_alpha', 'judul_menu' => 'Menu Alpha Uji', 'default_value' => false]);
        HakAkses::create(['nama_field' => 'uji_beta', 'judul_menu' => 'Menu Beta Uji', 'default_value' => false]);

        Livewire::actingAs($petugas)
            ->test(Khanza::class)
            ->set('cari', 'Menu Alpha Uji')
            ->assertSee('Menu Alpha Uji')
            ->assertDontSee('Menu Beta Uji');
    }
}
