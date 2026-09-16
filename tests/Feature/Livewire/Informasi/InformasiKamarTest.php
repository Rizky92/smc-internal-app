<?php

namespace Tests\Feature\Livewire\Informasi;

use App\Livewire\Pages\Informasi\InformasiKamar;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: read-only public-facing room-availability board, smoke-tested per
 * this pass's RO convention.
 */
class InformasiKamarTest extends TestCase
{
    private const PERMISSION = 'informasi.informasi-kamar.read';

    /**
     * @test
     */
    public function mounts_without_error(): void
    {
        $petugas = $this->petugasWithPermissions([self::PERMISSION], '99999901');

        Livewire::actingAs($petugas)->test(InformasiKamar::class)->assertOk();
    }
}
