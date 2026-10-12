<?php

namespace Tests\Feature\Livewire\Aplikasi\Modal;

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

    /**
     * @test
     *
     * The -1 placeholder for "no parent" must never reach the column: a bidang
     * created at the top of the tree is stored with a null parent, which is
     * what whereNull('parent_id') and isRoot() look for.
     */
    public function a_new_top_level_bidang_is_stored_with_a_null_parent(): void
    {
        $petugas = $this->petugasWithPermissions(['aplikasi.bidang-unit.create'], '99999901');

        Livewire::actingAs($petugas)
            ->test(InputBidangUnit::class)
            ->set('nama', 'Bidang Uji Induk')
            ->call('create')
            ->assertDispatched('data-saved');

        $this->assertNull(Bidang::query()->where('nama', 'Bidang Uji Induk')->sole()->parent_id);
    }

    /**
     * @test
     */
    public function a_new_sub_bidang_is_stored_under_its_parent(): void
    {
        $petugas = $this->petugasWithPermissions(['aplikasi.bidang-unit.create'], '99999901');
        $induk = Bidang::create(['nama' => 'Bidang Uji Induk']);

        Livewire::actingAs($petugas)
            ->test(InputBidangUnit::class)
            ->set('nama', 'Bidang Uji Anak')
            ->set('parentId', $induk->id)
            ->call('create')
            ->assertDispatched('data-saved');

        $this->assertSame((int) $induk->id, (int) Bidang::query()->where('nama', 'Bidang Uji Anak')->sole()->parent_id);
    }

    /**
     * @test
     */
    public function refuses_a_new_bidang_without_the_permission(): void
    {
        Livewire::actingAs($this->petugasWithPermissions([], '99999901'))
            ->test(InputBidangUnit::class)
            ->set('nama', 'Bidang Uji Ditolak')
            ->call('create')
            ->assertDispatched('data-denied');

        $this->assertSame(0, Bidang::query()->where('nama', 'Bidang Uji Ditolak')->count());
    }

    /**
     * @test
     */
    public function updates_a_sub_bidang_and_moves_it_to_another_parent(): void
    {
        $petugas = $this->petugasWithPermissions(['aplikasi.bidang-unit.update'], '99999901');
        $indukLama = Bidang::create(['nama' => 'Bidang Uji Induk Lama']);
        $indukBaru = Bidang::create(['nama' => 'Bidang Uji Induk Baru']);
        $anak = Bidang::create(['nama' => 'Bidang Uji Anak', 'parent_id' => $indukLama->id]);

        Livewire::actingAs($petugas)
            ->test(InputBidangUnit::class)
            ->dispatch('prepare', bidangId: $anak->id, parentId: $indukLama->id, nama: $anak->nama)
            ->set('nama', 'Bidang Uji Anak Diubah')
            ->set('parentId', $indukBaru->id)
            ->call('create')
            ->assertDispatched('data-saved');

        $anak->refresh();

        $this->assertSame('Bidang Uji Anak Diubah', $anak->nama);
        $this->assertSame((int) $indukBaru->id, (int) $anak->parent_id);
    }

    /**
     * @test
     */
    public function refuses_an_update_without_the_update_permission(): void
    {
        $petugas = $this->petugasWithPermissions(['aplikasi.bidang-unit.create'], '99999901');
        $induk = Bidang::create(['nama' => 'Bidang Uji Induk']);
        $anak = Bidang::create(['nama' => 'Bidang Uji Anak', 'parent_id' => $induk->id]);

        Livewire::actingAs($petugas)
            ->test(InputBidangUnit::class)
            ->dispatch('prepare', bidangId: $anak->id, parentId: $induk->id, nama: $anak->nama)
            ->set('nama', 'Bidang Uji Anak Diubah')
            ->call('create')
            ->assertDispatched('data-denied');

        $this->assertSame('Bidang Uji Anak', $anak->refresh()->nama);
    }

    /**
     * update() used to write the -1 "no parent" placeholder straight into
     * bidang.parent_id, which is BIGINT UNSIGNED. Strict mode refused it as out
     * of range, uncaught, so renaming any top-level bidang — Keuangan, Marketing,
     * SDM and the rest on the dev schema — failed and changed nothing.
     *
     * @test
     */
    public function renaming_a_top_level_bidang_keeps_it_at_the_top(): void
    {
        $petugas = $this->petugasWithPermissions(['aplikasi.bidang-unit.update'], '99999901');
        $induk = Bidang::create(['nama' => 'Bidang Uji Induk']);

        Livewire::actingAs($petugas)
            ->test(InputBidangUnit::class)
            ->dispatch('prepare', bidangId: $induk->id, parentId: -1, nama: $induk->nama)
            ->set('nama', 'Bidang Uji Induk Diubah')
            ->call('create')
            ->assertDispatched('data-saved');

        $induk->refresh();

        $this->assertSame('Bidang Uji Induk Diubah', $induk->nama);
        $this->assertNull($induk->parent_id);
    }

    /**
     * Picking "-" in the parent dropdown does not send the integer prepare()
     * uses. It sends placeholderValue, the string "-1", which a strict -1 check
     * lets straight through to the unsigned column.
     *
     * @test
     */
    public function moving_a_sub_bidang_to_the_top_through_the_dropdown_clears_its_parent(): void
    {
        $petugas = $this->petugasWithPermissions(['aplikasi.bidang-unit.update'], '99999901');
        $induk = Bidang::create(['nama' => 'Bidang Uji Induk']);
        $anak = Bidang::create(['nama' => 'Bidang Uji Anak', 'parent_id' => $induk->id]);

        Livewire::actingAs($petugas)
            ->test(InputBidangUnit::class)
            ->dispatch('prepare', bidangId: $anak->id, parentId: $induk->id, nama: $anak->nama)
            ->set('parentId', '-1')
            ->call('create')
            ->assertDispatched('data-saved');

        $this->assertNull($anak->refresh()->parent_id);
    }

    /**
     * The same string reaches create() when a parent is chosen for a new bidang
     * and then set back to "-" before saving.
     *
     * @test
     */
    public function a_new_bidang_set_back_to_no_parent_in_the_dropdown_is_stored_at_the_top(): void
    {
        $petugas = $this->petugasWithPermissions(['aplikasi.bidang-unit.create'], '99999901');
        $induk = Bidang::create(['nama' => 'Bidang Uji Induk']);

        Livewire::actingAs($petugas)
            ->test(InputBidangUnit::class)
            ->set('nama', 'Bidang Uji Baru')
            ->set('parentId', (string) $induk->id)
            ->set('parentId', '-1')
            ->call('create')
            ->assertDispatched('data-saved');

        $this->assertNull(Bidang::query()->where('nama', 'Bidang Uji Baru')->sole()->parent_id);
    }

    /**
     * And a parent chosen from the dropdown arrives as a string id too.
     *
     * @test
     */
    public function a_parent_chosen_in_the_dropdown_is_stored_as_the_parent(): void
    {
        $petugas = $this->petugasWithPermissions(['aplikasi.bidang-unit.create'], '99999901');
        $induk = Bidang::create(['nama' => 'Bidang Uji Induk']);

        Livewire::actingAs($petugas)
            ->test(InputBidangUnit::class)
            ->set('nama', 'Bidang Uji Anak Baru')
            ->set('parentId', (string) $induk->id)
            ->call('create')
            ->assertDispatched('data-saved');

        $this->assertSame((int) $induk->id, (int) Bidang::query()->where('nama', 'Bidang Uji Anak Baru')->sole()->parent_id);
    }

    /**
     * @test
     */
    public function deletes_a_bidang_that_has_no_sub_bidang(): void
    {
        $petugas = $this->petugasWithPermissions(['aplikasi.bidang-unit.delete'], '99999901');
        $bidang = Bidang::create(['nama' => 'Bidang Uji Hapus']);

        Livewire::actingAs($petugas)
            ->test(InputBidangUnit::class)
            ->dispatch('prepare', bidangId: $bidang->id, parentId: -1, nama: $bidang->nama)
            ->call('delete')
            ->assertDispatched('data-success');

        $this->assertNull(Bidang::find($bidang->id));
    }

    /**
     * @test
     *
     * Deleting a parent would orphan its children — parent_id carries no
     * foreign key to stop it — so the component refuses on its own.
     */
    public function refuses_to_delete_a_bidang_that_still_has_sub_bidang(): void
    {
        $petugas = $this->petugasWithPermissions(['aplikasi.bidang-unit.delete'], '99999901');
        $induk = Bidang::create(['nama' => 'Bidang Uji Induk']);
        Bidang::create(['nama' => 'Bidang Uji Anak', 'parent_id' => $induk->id]);

        Livewire::actingAs($petugas)
            ->test(InputBidangUnit::class)
            ->dispatch('prepare', bidangId: $induk->id, parentId: -1, nama: $induk->nama)
            ->call('delete')
            ->assertDispatched('data-denied')
            ->assertNotDispatched('data-success');

        $this->assertNotNull(Bidang::find($induk->id));
    }

    /**
     * @test
     */
    public function refuses_a_delete_without_the_delete_permission(): void
    {
        $petugas = $this->petugasWithPermissions(['aplikasi.bidang-unit.update'], '99999901');
        $bidang = Bidang::create(['nama' => 'Bidang Uji Hapus']);

        Livewire::actingAs($petugas)
            ->test(InputBidangUnit::class)
            ->dispatch('prepare', bidangId: $bidang->id, parentId: -1, nama: $bidang->nama)
            ->call('delete')
            ->assertDispatched('data-denied');

        $this->assertNotNull(Bidang::find($bidang->id));
    }

    /**
     * @test
     */
    public function reports_a_bidang_that_no_longer_exists(): void
    {
        Livewire::actingAs($this->petugasWithPermissions(['aplikasi.bidang-unit.delete'], '99999901'))
            ->test(InputBidangUnit::class)
            ->dispatch('prepare', bidangId: 999999, parentId: -1, nama: 'Bidang Uji Hilang')
            ->call('delete')
            ->assertDispatched('data-not-found');
    }
}
