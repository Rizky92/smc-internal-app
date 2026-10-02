<?php

namespace Tests\Feature\Mutu;

use Database\Factories\Quality\QualityIndicatorFactory;

class HalamanMutuTest extends MutuTestCase
{
    /**
     * @return array<string, array{0: string}>
     */
    public function halamanMutu(): array
    {
        return [
            'kategori indikator'   => ['/admin/mutu/kategori-indikator'],
            'tipe input indikator' => ['/admin/mutu/tipe-input-indikator'],
            'profil indikator'     => ['/admin/mutu/profil-indikator'],
            'indikator mutu'       => ['/admin/mutu/indikator-mutu'],
            'validasi data'        => ['/admin/mutu/validasi-data'],
        ];
    }

    /**
     * @dataProvider halamanMutu
     */
    public function test_user_berizin_bisa_membuka_halaman_mutu(string $url): void
    {
        $this->actingAs($this->createUser())
            ->get($url)
            ->assertOk();
    }

    /**
     * @dataProvider halamanMutu
     */
    public function test_user_tanpa_izin_tidak_bisa_membuka_halaman_mutu(string $url): void
    {
        $this->actingAs($this->createUser(self::NIK, []))
            ->get($url)
            ->assertNotFound();
    }

    public function test_user_berizin_bisa_membuka_detail_indikator(): void
    {
        $indicator = QualityIndicatorFactory::new()->create();

        $this->actingAs($this->createUser())
            ->get("/admin/mutu/indikator-mutu/{$indicator->id}")
            ->assertOk()
            ->assertSee($indicator->profile->title);
    }

    public function test_user_bisa_membuka_dashboard_mutu(): void
    {
        QualityIndicatorFactory::new()->create();

        $this->actingAs($this->createUser())
            ->get('/admin/informasi/dashboard-mutu')
            ->assertOk();
    }
}
