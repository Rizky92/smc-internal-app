<?php

namespace Tests\Feature\Livewire\Informasi;

use App\Livewire\Pages\Informasi\DisplayJadwalDokter;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Public display board (no route middleware) - the same jadwalDokter() query
 * and hitungTotalRegistrasi() quota-split as JadwalDokter, just without the
 * filter chrome. See JadwalDokterTest for where the quota-split arithmetic
 * itself is covered against the model directly.
 */
class DisplayJadwalDokterTest extends TestCase
{
    /**
     * @test
     */
    public function mounts_without_error(): void
    {
        Livewire::test(DisplayJadwalDokter::class)->assertOk();
    }
}
