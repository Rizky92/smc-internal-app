<?php

namespace Tests\Feature\Livewire\Concerns;

use Livewire\Livewire;
use Tests\Fixtures\Livewire\ReportHarness;
use Tests\TestCase;

/**
 * LiveTable, inherited by 83 of the 116 page components.
 *
 * sortBy is reachable two ways and both are exercised here. The column header
 * in resources/views/components/table/th.blade.php calls it directly through
 * wire:click, passing the direction the column is *currently* sorted by and
 * letting the component work out the next one; the #[On('sortBy')] listener is
 * the other door, for anything dispatching the event instead.
 */
class LiveTableTest extends TestCase
{
    private function harness()
    {
        return Livewire::actingAs($this->petugasWithPermissions())
            ->test(ReportHarness::class);
    }

    /**
     * @test
     */
    public function nilai_bawaan_terpasang_saat_mount(): void
    {
        $this->harness()
            ->assertSet('cari', '')
            ->assertSet('perpage', 25)
            ->assertSet('sortColumns', []);
    }

    /**
     * The three-state cycle a column goes through as the header is clicked:
     * unsorted, ascending, descending, unsorted again. The header passes the
     * current state in, so the sequence below is exactly what a user clicking
     * the same column three times produces.
     *
     * @test
     */
    public function kolom_berputar_dari_belum_terurut_ke_asc_ke_desc_lalu_hilang(): void
    {
        $harness = $this->harness()
            ->call('sortBy', 'nama', null)
            ->assertSet('sortColumns', ['nama' => 'asc']);

        $harness->call('sortBy', 'nama', 'asc')
            ->assertSet('sortColumns', ['nama' => 'desc']);

        $harness->call('sortBy', 'nama', 'desc')
            ->assertSet('sortColumns', []);
    }

    /**
     * An empty string reaches the component whenever the column has never been
     * sorted, because @js(null) on an unset array key renders as "". It has to
     * be treated as "not yet sorted", not as an unknown direction — which is the
     * branch that would silently drop the column instead of sorting it.
     *
     * @test
     */
    public function arah_string_kosong_diperlakukan_sama_dengan_null(): void
    {
        $this->harness()
            ->call('sortBy', 'nama', '')
            ->assertSet('sortColumns', ['nama' => 'asc']);
    }

    /**
     * @test
     */
    public function beberapa_kolom_menumpuk_dan_urutannya_dipertahankan(): void
    {
        $harness = $this->harness()
            ->call('sortBy', 'tanggal', null)
            ->call('sortBy', 'nama', null)
            ->assertSet('sortColumns', ['tanggal' => 'asc', 'nama' => 'asc']);

        // Advancing one column must leave the other where it was, and must not
        // move it to the end of the list: the order of the keys is the order the
        // ORDER BY clause is built in.
        $harness->call('sortBy', 'tanggal', 'asc')
            ->assertSet('sortColumns', ['tanggal' => 'desc', 'nama' => 'asc']);
    }

    /**
     * @test
     */
    public function kolom_yang_dibuang_tidak_menghapus_kolom_lain(): void
    {
        $this->harness()
            ->call('sortBy', 'tanggal', null)
            ->call('sortBy', 'nama', null)
            ->call('sortBy', 'tanggal', 'desc')
            ->assertSet('sortColumns', ['nama' => 'asc']);
    }

    /**
     * @test
     */
    public function sort_by_dapat_dicapai_lewat_listener_dengan_parameter_bernama(): void
    {
        $this->harness()
            ->dispatch('sortBy', column: 'nama', direction: null)
            ->assertSet('sortColumns', ['nama' => 'asc']);
    }

    /**
     * @test
     */
    public function sort_by_dapat_dicapai_lewat_listener_dengan_parameter_posisional(): void
    {
        $this->harness()
            ->dispatch('sortBy', 'nama', 'asc')
            ->assertSet('sortColumns', ['nama' => 'desc']);
    }
}
