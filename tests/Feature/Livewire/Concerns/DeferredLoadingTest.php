<?php

namespace Tests\Feature\Livewire\Concerns;

use Livewire\Livewire;
use Tests\Fixtures\Livewire\ReportHarness;
use Tests\TestCase;

/**
 * DeferredLoading, inherited by 73 of the 116 page components.
 *
 * The trait is the reason a report page paints before its Khanza query runs:
 * the component mounts with $isDeferred true, the view renders an empty table,
 * and wire:init="loadProperties" asks for the data on a second round trip. Every
 * report view in resources/views/livewire/pages opens with that attribute, so
 * loadProperties is the single most-called action in the application.
 */
class DeferredLoadingTest extends TestCase
{
    private function harness()
    {
        return Livewire::actingAs($this->petugasWithPermissions())
            ->test(ReportHarness::class);
    }

    /**
     * @test
     */
    public function komponen_mount_dalam_keadaan_deferred(): void
    {
        $this->harness()->assertSet('isDeferred', true);
    }

    /**
     * @test
     */
    public function load_properties_mengangkat_deferral(): void
    {
        $this->harness()
            ->call('loadProperties')
            ->assertSet('isDeferred', false);
    }

    /**
     * @test
     */
    public function undefer_mengembalikan_komponen_ke_keadaan_deferred(): void
    {
        $this->harness()
            ->call('loadProperties')
            ->call('undefer')
            ->assertSet('isDeferred', true);
    }

    /**
     * loadProperties is called from wire:init on the root element, so it runs on
     * a component whose deferral is still set. Calling it a second time must not
     * flip the state back — the report would blank out on any later refresh.
     *
     * @test
     */
    public function load_properties_idempoten(): void
    {
        $this->harness()
            ->call('loadProperties')
            ->call('loadProperties')
            ->assertSet('isDeferred', false);
    }
}
