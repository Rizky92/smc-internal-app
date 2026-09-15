<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Antrean;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Public landing page for the waiting-room queue (no route middleware) -
 * lists today's active doctors, grouped by poliklinik. Smoke-tested.
 */
class AntreanTest extends TestCase
{
    /**
     * @test
     */
    public function mounts_without_error(): void
    {
        Livewire::test(Antrean::class)->assertOk();
    }
}
