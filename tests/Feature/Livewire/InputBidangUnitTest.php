<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Pages\Aplikasi\Modal\InputBidangUnit;
use App\Models\Bidang;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: the modal that creates and edits an organizational bidang.
 *
 * "Bidang Baru" only has a bare data-toggle="modal" button behind it, with no
 * action to reset the form - unlike a row click, which reaches prepare() via
 * JS. Reopening the modal for a new bidang right after editing one is the
 * only way this component's create/update split is actually exercised end to
 * end.
 */
class InputBidangUnitTest extends TestCase
{
    protected function tearDown(): void
    {
        $smc = DB::connection('mysql_smc');

        $smc->statement('set foreign_key_checks = 0');
        $smc->table('bidang')->where('nama', 'like', 'Bidang Uji%')->delete();
        $smc->statement('set foreign_key_checks = 1');

        parent::tearDown();
    }

    /**
     * @test
     *
     * bidang-unit.blade.php's "Bidang Baru" button dispatches prepare with no
     * options at all (see input-bidang-unit.blade.php's shown.bs.modal
     * handler) - prepare()'s own defaults (bidangId/parentId: -1, nama: '')
     * already match defaultValues(), so a bare dispatch resets it correctly.
     */
    public function reopening_for_a_new_bidang_resets_the_form(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');
        $existing = Bidang::create(['nama' => 'Bidang Uji Lama', 'parent_id' => null]);

        Livewire::actingAs($petugas)
            ->test(InputBidangUnit::class)
            ->dispatch('prepare', bidangId: $existing->id, parentId: -1, nama: $existing->nama)
            ->assertSet('bidangId', $existing->id)
            ->dispatch('prepare')
            ->assertSet('bidangId', -1)
            ->assertSet('parentId', -1)
            ->assertSet('nama', '');
    }

    /**
     * @test
     *
     * create() routes to update() whenever bidangId isn't -1. Before the
     * dispatch above ran on every "Bidang Baru" open, a stale bidangId left
     * over from editing $existing meant this call silently renamed $existing
     * instead of creating a new bidang.
     */
    public function creating_after_editing_another_bidang_does_not_touch_it(): void
    {
        $petugas = $this->petugasWithPermissions(['aplikasi.bidang-unit.create', 'aplikasi.bidang-unit.update'], '99999901');
        $existing = Bidang::create(['nama' => 'Bidang Uji Lama', 'parent_id' => null]);

        Livewire::actingAs($petugas)
            ->test(InputBidangUnit::class)
            ->dispatch('prepare', bidangId: $existing->id, parentId: -1, nama: $existing->nama)
            ->dispatch('prepare')
            ->set('nama', 'Bidang Uji Baru')
            ->call('create')
            ->assertDispatched('data-saved');

        $existing->refresh();

        $this->assertSame('Bidang Uji Lama', $existing->nama);
        $this->assertSame(1, Bidang::query()->where('nama', 'Bidang Uji Baru')->count());
    }
}
