<?php

namespace Tests\Feature\Menu;

use App\Models\Aplikasi\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LaporanPasienRanapTest extends TestCase
{
    public function test_user_bisa_mengunjungi_laporan_pasien_ranap()
    {
        $user = User::findByNRP('88888888');

        $test = $this->actingAs($user);

        $test->assertTrue($user->can('perawatan.laporan-pasien-ranap.read'));

        $test->get('admin/perawatan/laporan-pasien-ranap')
            ->assertOk();
    }

    public function test_user_tidak_bisa_mengunjungi_laporan_pasien_ranap()
    {
        $user = User::findByNRP('221203');

        $test = $this->actingAs($user);

        $test->assertFalse($user->can('perawatan.laporan-pasien-ranap.read'));

        $test->get('admin/perawatan/laporan-pasien-ranap')
            ->assertNotFound();
    }
}
