<?php

namespace Tests\Feature\Livewire\Informasi;

use App\Livewire\Pages\Informasi\DashboardDokter;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Public display board (no route middleware). mount() groups today's active
 * doctors' schedules by poliklinik name then by doctor code - smoke-tested
 * since it's a display grouping, not a calculation.
 */
class DashboardDokterTest extends TestCase
{
    /**
     * @test
     */
    public function mounts_without_error(): void
    {
        Livewire::test(DashboardDokter::class)->assertOk();
    }
}
