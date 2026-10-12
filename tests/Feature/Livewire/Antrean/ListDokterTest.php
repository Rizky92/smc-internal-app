<?php

namespace Tests\Feature\Livewire\Antrean;

use App\Livewire\Pages\Antrean\ListDokter;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Public waiting-room display board (no route - embedded inside
 * AntreanPerPintu's Blade view via <livewire:pages.antrean.list-dokter>),
 * smoke-tested.
 */
class ListDokterTest extends TestCase
{
    /**
     * @test
     */
    public function mounts_without_error(): void
    {
        Livewire::test(ListDokter::class, ['kd_pintu' => 'UJI-PINTU'])->assertOk();
    }
}
