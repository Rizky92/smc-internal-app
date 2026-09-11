<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Pages\Antrean\ListAntrean;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The per-pintu queue panel, nested inside the entrance displays.
 *
 * kd_pintu reaches it from /antrean-per-pintu/{kd_pintu} or from whichever pintu
 * the parent screen is iterating, so a code that has since been removed from
 * pintu_smc is an ordinary occurrence rather than a bug in the caller.
 */
class ListAntreanTest extends TestCase
{
    protected function tearDown(): void
    {
        DB::connection('mysql_sik')->table('pintu_smc')->where('kd_pintu', 'like', 'UJI-%')->delete();

        parent::tearDown();
    }

    private function createPintu(string $kdPintu, string $nama): void
    {
        DB::connection('mysql_sik')->table('pintu_smc')->insert([
            'kd_pintu' => $kdPintu,
            'nm_pintu' => $nama,
            'status'   => '1',
        ]);
    }

    /**
     * @test
     */
    public function shows_the_name_of_the_pintu(): void
    {
        $this->createPintu('UJI-A', 'Pintu Uji Utara');

        Livewire::test(ListAntrean::class, ['kd_pintu' => 'UJI-A'])
            ->assertOk()
            ->assertSee('Pintu Uji Utara');
    }

    /**
     * @test
     */
    public function survives_a_kode_pintu_that_does_not_exist(): void
    {
        Livewire::test(ListAntrean::class, ['kd_pintu' => 'TIDAKADA'])
            ->assertOk();
    }
}
