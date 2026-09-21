<?php

namespace Tests\Feature\Livewire\Concerns;

use Livewire\Livewire;
use Tests\Fixtures\Livewire\ReportHarness;
use Tests\TestCase;

/**
 * Filterable, inherited by 90 of the 116 page components.
 *
 * Every case reaches the trait by dispatching the event rather than calling the
 * method, because that is how the application reaches it: the filter buttons in
 * resources/views/components fire `searchData`, `resetFilters` and `fullRefresh`
 * from JavaScript, and the component picks them up through #[On]. Calling the
 * method directly would pass even if the listener were not registered at all,
 * which is precisely the failure the Livewire 3 migration kept producing.
 */
class FilterableTest extends TestCase
{
    private function harness()
    {
        return Livewire::actingAs($this->petugasWithPermissions())
            ->test(ReportHarness::class);
    }

    /**
     * @test
     */
    public function search_data_mengembalikan_paginasi_ke_halaman_pertama(): void
    {
        $this->harness()
            ->call('gotoPage', 3)
            ->assertSet('paginators.page', 3)
            ->dispatch('searchData')
            ->assertSet('paginators.page', 1);
    }

    /**
     * A report mounts deferred so the page paints before the Khanza query runs.
     * Searching is an explicit request for data, so it has to lift the deferral
     * as well — otherwise the user searches and the table stays empty.
     *
     * @test
     */
    public function search_data_mengangkat_deferred_loading(): void
    {
        $this->harness()
            ->assertSet('isDeferred', true)
            ->dispatch('searchData')
            ->assertSet('isDeferred', false);
    }

    /**
     * @test
     */
    public function reset_state_mengembalikan_nilai_default_komponen(): void
    {
        $this->harness()
            ->set('tglAwal', '2020-01-01')
            ->set('tglAkhir', '2020-01-31')
            ->dispatch('resetState')
            ->assertSet('tglAwal', now()->startOfMonth()->format('Y-m-d'))
            ->assertSet('tglAkhir', now()->endOfMonth()->format('Y-m-d'));
    }

    /**
     * The line between the two reset events, stated as a test because nothing
     * else states it: resetState runs only the component's own defaultValues(),
     * so the search term and page size a user chose survive it. resetFilters is
     * the one that also restores what the traits contribute.
     *
     * @test
     */
    public function reset_state_tidak_menyentuh_nilai_bawaan_trait(): void
    {
        $this->harness()
            ->set('cari', 'paracetamol')
            ->set('perpage', 100)
            ->dispatch('resetState')
            ->assertSet('cari', 'paracetamol')
            ->assertSet('perpage', 100);
    }

    /**
     * @test
     */
    public function reset_filters_mengembalikan_nilai_komponen_dan_nilai_bawaan_trait(): void
    {
        $this->harness()
            ->set('tglAwal', '2020-01-01')
            ->set('cari', 'paracetamol')
            ->set('perpage', 100)
            ->set('sortColumns', ['nama' => 'desc'])
            ->dispatch('resetFilters')
            ->assertSet('tglAwal', now()->startOfMonth()->format('Y-m-d'))
            ->assertSet('cari', '')
            ->assertSet('perpage', 25)
            ->assertSet('sortColumns', []);
    }

    /**
     * resetFilters ends by calling searchData, so it carries that method's two
     * side effects with it.
     *
     * @test
     */
    public function reset_filters_juga_mereset_paginasi_dan_mengangkat_deferral(): void
    {
        $this->harness()
            ->call('gotoPage', 4)
            ->dispatch('resetFilters')
            ->assertSet('paginators.page', 1)
            ->assertSet('isDeferred', false);
    }

    /**
     * fullRefresh derives a property name back from each getXxxProperty() method
     * and unsets it, which only works while its guess matches the name Livewire
     * memoised the value under. A mismatch does not fail loudly — it throws while
     * unsetting, taking the whole refresh with it.
     *
     * @test
     */
    public function full_refresh_membuang_computed_property_lalu_mereset_filter(): void
    {
        $this->harness()
            ->set('cari', 'paracetamol')
            ->set('tglAwal', '2020-01-01')
            ->dispatch('fullRefresh')
            ->assertOk()
            ->assertSet('cari', '')
            ->assertSet('tglAwal', now()->startOfMonth()->format('Y-m-d'));
    }

    /**
     * The other half of the same guess: after fullRefresh has unset it, the
     * computed property still has to resolve. If the derived name had collided
     * with a real public property, it would have been deleted rather than
     * un-memoised, and this is where that shows up.
     *
     * @test
     */
    public function computed_property_tetap_terbaca_setelah_full_refresh(): void
    {
        $harness = $this->harness()
            ->dispatch('fullRefresh')
            ->assertOk();

        $this->assertSame(['satu', 'dua'], $harness->instance()->baris);
    }
}
