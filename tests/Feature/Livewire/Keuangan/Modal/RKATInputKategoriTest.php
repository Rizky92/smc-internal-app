<?php

namespace Tests\Feature\Livewire\Keuangan\Modal;

use App\Livewire\Pages\Keuangan\Modal\RKATInputKategori;
use App\Models\Keuangan\RKAT\Anggaran;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: the modal that creates and edits an RKAT budget category.
 *
 * "Anggaran Baru" only has a bare data-toggle="modal" button behind it, with
 * no action to reset the form - unlike a row click, which reaches prepare()
 * via JS. Reopening the modal for a new category right after editing one is
 * the only way this component's create/update split is actually exercised
 * end to end.
 */
class RKATInputKategoriTest extends TestCase
{
    protected function tearDown(): void
    {
        DB::connection('mysql_smc')->table('anggaran')->where('nama', 'like', 'Kategori Uji%')->delete();

        parent::tearDown();
    }

    /**
     * @test
     *
     * rkat-kategori.blade.php's "Anggaran Baru" button dispatches prepare
     * with no options at all (see rkat-input-kategori.blade.php's
     * shown.bs.modal handler) - prepare()'s own defaults (id: -1, nama/
     * deskripsi: '') already match defaultValues(), so a bare dispatch
     * resets it correctly.
     */
    public function reopening_for_a_new_kategori_resets_the_form(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');
        $existing = Anggaran::create(['nama' => 'Kategori Uji Lama']);

        Livewire::actingAs($petugas)
            ->test(RKATInputKategori::class)
            ->dispatch('prepare', id: $existing->id, nama: $existing->nama, deskripsi: '')
            ->assertSet('anggaranId', $existing->id)
            ->dispatch('prepare')
            ->assertSet('anggaranId', -1)
            ->assertSet('nama', '')
            ->assertSet('deskripsi', '');
    }

    /**
     * @test
     *
     * create() routes to update() whenever anggaranId isn't -1. Before the
     * dispatch above ran on every "Anggaran Baru" open, a stale anggaranId
     * left over from editing $existing meant this call silently renamed
     * $existing instead of creating a new category.
     */
    public function creating_after_editing_another_kategori_does_not_touch_it(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.rkat-kategori.create', 'keuangan.rkat-kategori.update'], '99999901');
        $existing = Anggaran::create(['nama' => 'Kategori Uji Lama']);

        Livewire::actingAs($petugas)
            ->test(RKATInputKategori::class)
            ->dispatch('prepare', id: $existing->id, nama: $existing->nama, deskripsi: '')
            ->dispatch('prepare')
            ->set('nama', 'Kategori Uji Baru')
            ->call('create')
            ->assertDispatched('data-saved');

        $existing->refresh();

        $this->assertSame('Kategori Uji Lama', $existing->nama);
        $this->assertSame(1, Anggaran::query()->where('nama', 'Kategori Uji Baru')->count());
    }

    /**
     * @test
     */
    public function refuses_a_new_kategori_without_the_permission(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');

        Livewire::actingAs($petugas)
            ->test(RKATInputKategori::class)
            ->set('nama', 'Kategori Uji Ditolak')
            ->call('create')
            ->assertDispatched('data-denied')
            ->assertNotDispatched('data-saved');

        $this->assertSame(0, Anggaran::query()->where('nama', 'Kategori Uji Ditolak')->count());
    }

    /**
     * @test
     *
     * The create path turns a blank description into null rather than storing
     * an empty string, so the listing can tell "no description" apart.
     */
    public function a_blank_deskripsi_is_stored_as_null(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.rkat-kategori.create'], '99999901');

        Livewire::actingAs($petugas)
            ->test(RKATInputKategori::class)
            ->set('nama', 'Kategori Uji Tanpa Deskripsi')
            ->set('deskripsi', '')
            ->call('create')
            ->assertDispatched('data-saved');

        $this->assertNull(Anggaran::query()->where('nama', 'Kategori Uji Tanpa Deskripsi')->sole()->deskripsi);
    }

    /**
     * @test
     */
    public function updates_the_selected_kategori(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.rkat-kategori.update'], '99999901');
        $existing = Anggaran::create(['nama' => 'Kategori Uji Lama']);

        Livewire::actingAs($petugas)
            ->test(RKATInputKategori::class)
            ->dispatch('prepare', id: $existing->id, nama: $existing->nama, deskripsi: '')
            ->set('nama', 'Kategori Uji Diubah')
            ->set('deskripsi', 'Deskripsi baru')
            ->call('create')
            ->assertDispatched('data-saved');

        $existing->refresh();

        $this->assertSame('Kategori Uji Diubah', $existing->nama);
        $this->assertSame('Deskripsi baru', $existing->deskripsi);
        $this->assertSame(1, Anggaran::query()->where('nama', 'like', 'Kategori Uji%')->count());
    }

    /**
     * @test
     *
     * Holding the create permission is not enough to edit: the update path
     * checks its own.
     */
    public function refuses_an_update_without_the_update_permission(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.rkat-kategori.create'], '99999901');
        $existing = Anggaran::create(['nama' => 'Kategori Uji Lama']);

        Livewire::actingAs($petugas)
            ->test(RKATInputKategori::class)
            ->dispatch('prepare', id: $existing->id, nama: $existing->nama, deskripsi: '')
            ->set('nama', 'Kategori Uji Diubah')
            ->call('create')
            ->assertDispatched('data-denied');

        $this->assertSame('Kategori Uji Lama', $existing->refresh()->nama);
    }

    /**
     * @test
     *
     * The row was removed while the modal was open. Nothing is written and the
     * user is told, rather than the save failing on null.
     */
    public function reports_a_kategori_that_no_longer_exists(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.rkat-kategori.update'], '99999901');

        Livewire::actingAs($petugas)
            ->test(RKATInputKategori::class)
            ->dispatch('prepare', id: 999999, nama: 'Kategori Uji Hilang', deskripsi: '')
            ->call('create')
            ->assertDispatched('data-not-found')
            ->assertNotDispatched('data-saved');

        $this->assertSame(0, Anggaran::query()->where('nama', 'Kategori Uji Hilang')->count());
    }
}
