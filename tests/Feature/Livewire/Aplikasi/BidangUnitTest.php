<?php

namespace Tests\Feature\Livewire\Aplikasi;

use App\Livewire\Pages\Aplikasi\BidangUnit;
use App\Models\Bidang;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: the organizational bidang tree.
 *
 * A pure read-only list - the actual create/edit write path is already
 * covered by InputBidangUnitTest. This suite pins that a root bidang's
 * descendants render nested beneath it (with('descendants') is what makes
 * that possible without an N+1 query per row) and that search still works
 * against the top-level query.
 *
 * "Bidang Baru" is not gated by @can in the Blade - unlike every sibling
 * list page in this batch, the button is always visible and the permission
 * check only happens server-side in InputBidangUnit::create(). Left as-is;
 * flagged here rather than assumed away.
 */
class BidangUnitTest extends TestCase
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
     */
    public function lists_a_root_bidang_with_its_descendant_nested(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');

        $induk = Bidang::create(['nama' => 'Bidang Uji Induk', 'parent_id' => null]);
        Bidang::create(['nama' => 'Bidang Uji Anak', 'parent_id' => $induk->id]);

        Livewire::actingAs($petugas)
            ->test(BidangUnit::class)
            ->assertSee('Bidang Uji Induk')
            ->assertSee('Bidang Uji Anak');
    }

    /**
     * @test
     *
     * search() only runs against the top-level (parent_id null) query, so a
     * search matching a child's name but not its parent's must still surface
     * the parent - the child only ever renders nested beneath it.
     */
    public function narrows_the_table_to_the_search_term(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');

        Bidang::create(['nama' => 'Bidang Uji Alpha', 'parent_id' => null]);
        Bidang::create(['nama' => 'Bidang Uji Beta', 'parent_id' => null]);

        Livewire::actingAs($petugas)
            ->test(BidangUnit::class)
            ->set('cari', 'Alpha')
            ->assertSee('Bidang Uji Alpha')
            ->assertDontSee('Bidang Uji Beta');
    }
}
