<?php

namespace Tests\Feature\Livewire;

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
}
