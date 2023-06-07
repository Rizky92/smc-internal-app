<?php

namespace Tests\Feature\Menu;

use App\Http\Livewire\Perawatan\DaftarPasienRanap;
use App\Models\Aplikasi\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire;
use Tests\TestCase;

class DaftarPasienRanapTest extends TestCase
{
    /**
     * @test
     *
     * @return void
     */
    public function test_user_bisa_mengunjungi_daftar_pasien_ranap()
    {
        $user = User::findByNRP('88888888');

        $this
            ->actingAs($user)
            ->get('/admin/perawatan/daftar-pasien-ranap')
            ->assertOk();
    }

    public function test_user_tidak_bisa_mengunjungi_daftar_pasien_ranap()
    {
        $user = User::findByNRP('221203');

        $this
            ->actingAs($user)
            ->get('/admin/perawatan/daftar-pasien-ranap')
            ->assertNotFound();
    }

    public function test_halaman_memiliki_component_livewire()
    {
        $user = User::findByNRP('88888888');

        $this
            ->actingAs($user)
            ->get('/admin/perawatan/daftar-pasien-ranap')
            ->assertSeeLivewire(DaftarPasienRanap::class);
    }

    public function test_user_bisa_mengubah_data_pasien()
    {
        $user = User::findByNRP('88888888');

        $this->assertTrue($user->can('perawatan.daftar-pasien-ranap.update-harga-kamar'));

        Livewire::actingAs($user)
            ->test(DaftarPasienRanap::class)
            ->call('updateHargaKamar', '2023/02/15/000583', '386D', '2023-02-15', '10:29:54', '1', '1')
            ->assertDispatchedBrowserEvent('data-updated');
    }

    public function test_user_tidak_bisa_mengubah_data_pasien()
    {
        $user = User::findByNRP('221203');

        $this->assertFalse($user->can('perawatan.daftar-pasien-ranap.update-harga-kamar'));

        Livewire::actingAs($user)
            ->test(DaftarPasienRanap::class)
            ->call('updateHargaKamar', '2023/02/15/000583', '386D', '2023-02-15', '10:29:54', '1', '1')
            ->assertNotDispatchedBrowserEvent('data-updated');
    }

    public function test_livewire_component_bisa_export_file_ke_excel()
    {
        $user = User::findByNRP('88888888');

        $time = Carbon::now();

        Carbon::setTestNow($time);

        $file = $time->format('Ymd_His') . '_' . 'daftar_pasien_ranap' . '.xlsx';

        Livewire::actingAs($user)
            ->test(DaftarPasienRanap::class)
            ->call('exportToExcel')
            ->assertEmitted('beginExcelExport')
            ->assertFileDownloaded($file);
    }
}
