<?php

namespace Tests\Feature\Mutu;

use App\Livewire\Pages\Mutu\DetailIndikatorMutu;
use App\Livewire\Pages\Mutu\IndikatorMutu;
use App\Livewire\Pages\Mutu\Modal\InputIndikatorMutu;
use App\Models\Quality\QualityIndicator;
use Database\Factories\Quality\QualityIndicatorFactory;
use Database\Factories\Quality\QualityIndicatorProfileFactory;
use Database\Factories\Quality\QualityIndicatorRecordFactory;
use Livewire\Livewire;

class IndikatorMutuTest extends MutuTestCase
{
    private const DEP_LAIN = 'ADM';

    private function indikator(string $title, string $depId = self::DEP_ID): QualityIndicator
    {
        return QualityIndicatorFactory::new()->create([
            'quality_indicator_profile_id' => QualityIndicatorProfileFactory::new()->create(['title' => $title]),
            'dep_id'                       => $depId,
        ]);
    }

    public function test_admin_bisa_memetakan_profil_ke_departemen(): void
    {
        $profile = QualityIndicatorProfileFactory::new()->create();

        Livewire::actingAs($this->createUser())
            ->test(InputIndikatorMutu::class)
            ->call('loadIndicator')
            ->set('quality_indicator_profile_id', $profile->id)
            ->set('dep_id', self::DEP_LAIN)
            ->set('person_in_charge', 'Kepala Unit')
            ->set('data_source', 'Rekam medis')
            ->set('status', 'active')
            ->call('save')
            ->assertHasNoErrors()
            ->assertEmitted('indicator-saved');

        $this->assertDatabaseHas('quality_indicators', [
            'quality_indicator_profile_id' => $profile->id,
            'dep_id'                       => self::DEP_LAIN,
            'person_in_charge'             => 'Kepala Unit',
            'data_source'                  => 'Rekam medis',
            'status'                       => 'active',
        ], 'mysql_smc');
    }

    public function test_admin_bisa_mengubah_mapping_indikator(): void
    {
        $indicator = $this->indikator('Indikator Lama');

        Livewire::actingAs($this->createUser())
            ->test(InputIndikatorMutu::class)
            ->call('loadIndicator', $indicator->id)
            ->assertSet('quality_indicator_profile_id', $indicator->quality_indicator_profile_id)
            ->assertSet('dep_id', self::DEP_ID)
            ->assertSet('person_in_charge', $indicator->person_in_charge)
            ->set('person_in_charge', 'PJ Baru')
            ->set('status', 'inactive')
            ->call('save')
            ->assertHasNoErrors()
            ->assertEmitted('indicator-saved');

        $indicator->refresh();

        $this->assertSame('PJ Baru', $indicator->person_in_charge);
        $this->assertSame('inactive', $indicator->status);
        $this->assertSame(1, QualityIndicator::where('quality_indicator_profile_id', $indicator->quality_indicator_profile_id)->count());
    }

    public function test_mapping_indikator_wajib_memiliki_profil_dan_departemen(): void
    {
        Livewire::actingAs($this->createUser())
            ->test(InputIndikatorMutu::class)
            ->call('loadIndicator')
            ->call('save')
            ->assertHasErrors([
                'quality_indicator_profile_id' => 'required',
                'dep_id'                       => 'required',
            ])
            ->assertNotEmitted('indicator-saved');
    }

    public function test_tanpa_filter_user_melihat_indikator_departemennya_sendiri(): void
    {
        $this->indikator('Indikator Departemen Sendiri');
        $this->indikator('Indikator Departemen Lain', self::DEP_LAIN);

        Livewire::actingAs($this->createUser())
            ->test(IndikatorMutu::class)
            ->call('loadProperties')
            ->assertSee('Indikator Departemen Sendiri')
            ->assertDontSee('Indikator Departemen Lain');
    }

    public function test_user_bisa_memfilter_indikator_berdasarkan_departemen(): void
    {
        $this->indikator('Indikator Departemen Sendiri');
        $this->indikator('Indikator Departemen Lain', self::DEP_LAIN);

        Livewire::actingAs($this->createUser())
            ->test(IndikatorMutu::class)
            ->call('loadProperties')
            ->set('depId', self::DEP_LAIN)
            ->assertSee('Indikator Departemen Lain')
            ->assertDontSee('Indikator Departemen Sendiri');
    }

    public function test_user_bisa_mencari_indikator_berdasarkan_judul_profil(): void
    {
        $this->indikator('Kepatuhan Cuci Tangan');
        $this->indikator('Waktu Tunggu Rawat Jalan');

        Livewire::actingAs($this->createUser())
            ->test(IndikatorMutu::class)
            ->call('loadProperties')
            ->set('cari', 'cuci')
            ->assertSee('Kepatuhan Cuci Tangan')
            ->assertDontSee('Waktu Tunggu Rawat Jalan');
    }

    public function test_detail_menampilkan_record_dalam_rentang_tanggal_secara_berurutan(): void
    {
        $indicator = $this->indikator('Indikator Detail');
        $lain = $this->indikator('Indikator Lain');

        foreach (['2026-03-20', '2026-03-05', '2026-03-12', '2026-02-27', '2026-04-01'] as $date) {
            QualityIndicatorRecordFactory::new()->create(['indicator_id' => $indicator->id, 'recorded_date' => $date]);
        }

        QualityIndicatorRecordFactory::new()->create(['indicator_id' => $lain->id, 'recorded_date' => '2026-03-15']);

        $component = Livewire::actingAs($this->createUser())
            ->test(DetailIndikatorMutu::class, ['indicatorId' => $indicator->id])
            ->set('tglAwal', '2026-03-01')
            ->set('tglAkhir', '2026-03-31')
            ->call('loadProperties')
            ->assertSeeInOrder(['05-03-2026', '12-03-2026', '20-03-2026'])
            ->assertDontSee('27-02-2026')
            ->assertDontSee('01-04-2026')
            ->assertDontSee('15-03-2026');

        $this->assertSame(
            ['2026-03-05', '2026-03-12', '2026-03-20'],
            $component->instance()->records->pluck('recorded_date')->map(fn ($d) => carbon($d)->format('Y-m-d'))->all()
        );
    }

    public function test_admin_bisa_menghapus_indikator_dari_halaman_detail(): void
    {
        $indicator = $this->indikator('Indikator Dihapus');

        Livewire::actingAs($this->createUser())
            ->test(DetailIndikatorMutu::class, ['indicatorId' => $indicator->id])
            ->call('delete')
            ->assertRedirect(route('admin.mutu.indikator-mutu'));

        $this->assertDatabaseMissing('quality_indicators', ['id' => $indicator->id], 'mysql_smc');
    }
}
