<?php

namespace Tests\Feature\Livewire\Keuangan;

use App\Livewire\Pages\Keuangan\RKATKategori;
use App\Models\Keuangan\RKAT\Anggaran;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: the RKAT budget category list.
 *
 * A pure read-only list - the actual create/edit write path is already
 * covered by RKATInputKategoriTest.
 */
class RKATKategoriTest extends TestCase
{
    protected function tearDown(): void
    {
        DB::connection('mysql_smc')->table('anggaran')->where('nama', 'Kategori Uji')->delete();

        parent::tearDown();
    }

    /**
     * @test
     */
    public function lists_every_kategori(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');

        Anggaran::create(['nama' => 'Kategori Uji']);

        Livewire::actingAs($petugas)
            ->test(RKATKategori::class)
            ->assertSee('Kategori Uji');
    }
}
