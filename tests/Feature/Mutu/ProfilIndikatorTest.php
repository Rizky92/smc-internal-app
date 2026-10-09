<?php

namespace Tests\Feature\Mutu;

use App\Livewire\Pages\Mutu\Modal\InputProfilIndikator;
use App\Livewire\Pages\Mutu\ProfilIndikator;
use App\Models\Quality\QualityIndicatorProfile;
use Database\Factories\Quality\QualityIndicatorCategoryFactory;
use Database\Factories\Quality\QualityIndicatorInputTypeFactory;
use Database\Factories\Quality\QualityIndicatorProfileFactory;
use Livewire\Livewire;

class ProfilIndikatorTest extends MutuTestCase
{
    /**
     * @return array<string, mixed>
     */
    private function profilLengkap(): array
    {
        return [
            'title'                  => 'Kepatuhan Identifikasi Pasien',
            'dimension'              => 'Keselamatan',
            'objective'              => 'Mengukur kepatuhan identifikasi',
            'definition'             => 'Identifikasi dengan dua penanda',
            'inclusion'              => 'Semua pasien rawat inap',
            'exclusion'              => 'Pasien meninggal',
            'frequency'              => 'Harian',
            'analysis_period'        => 3,
            'numerator'              => 'Jumlah pasien teridentifikasi benar',
            'denominator'            => 'Jumlah pasien yang diobservasi',
            'standard'               => '100%',
            'rationale'              => 'Mencegah salah pasien',
            'indicator_type'         => 'Proses',
            'measurement_unit'       => 'Persen',
            'formula'                => 'N / D x 100%',
            'data_collection_method' => 'Observasi',
            'instrument'             => 'Formulir observasi',
            'sample_size'            => '30',
            'sampling_method'        => 'Acak',
            'data_presentation'      => 'Run chart',
        ];
    }

    public function test_admin_bisa_menambah_profil_indikator_lengkap(): void
    {
        $category = QualityIndicatorCategoryFactory::new()->create();
        $inputType = QualityIndicatorInputTypeFactory::new()->create();

        $component = Livewire::actingAs($this->createUser())
            ->test(InputProfilIndikator::class)
            ->call('loadProfile')
            ->set('quality_indicator_category_id', $category->id)
            ->set('quality_indicator_input_type_id', $inputType->id);

        foreach ($this->profilLengkap() as $field => $value) {
            $component->set($field, $value);
        }

        $component
            ->call('save')
            ->assertHasNoErrors()
            ->assertEmitted('profile-saved');

        $this->assertDatabaseHas('quality_indicator_profiles', array_merge($this->profilLengkap(), [
            'quality_indicator_category_id'   => $category->id,
            'quality_indicator_input_type_id' => $inputType->id,
        ]), 'mysql_smc');
    }

    public function test_admin_bisa_mengubah_profil_indikator_dengan_isian_terisi(): void
    {
        $profile = QualityIndicatorProfileFactory::new()->create($this->profilLengkap());

        $component = Livewire::actingAs($this->createUser())
            ->test(InputProfilIndikator::class)
            ->call('loadProfile', $profile->id)
            ->assertSet('quality_indicator_category_id', $profile->quality_indicator_category_id)
            ->assertSet('quality_indicator_input_type_id', $profile->quality_indicator_input_type_id);

        foreach ($this->profilLengkap() as $field => $value) {
            $component->assertSet($field, $value);
        }

        $component
            ->set('title', 'Judul Baru')
            ->set('standard', '95%')
            ->call('save')
            ->assertHasNoErrors()
            ->assertEmitted('profile-saved');

        $profile->refresh();

        $this->assertSame('Judul Baru', $profile->title);
        $this->assertSame('95%', $profile->standard);
        $this->assertSame('Keselamatan', $profile->dimension);
        $this->assertSame(1, QualityIndicatorProfile::whereKey($profile->id)->count());
    }

    public function test_kategori_judul_frekuensi_dan_standar_wajib_diisi(): void
    {
        Livewire::actingAs($this->createUser())
            ->test(InputProfilIndikator::class)
            ->call('loadProfile')
            ->call('save')
            ->assertHasErrors([
                'quality_indicator_category_id' => 'required',
                'title'                         => 'required',
                'frequency'                     => 'required',
                'standard'                      => 'required',
            ])
            ->assertNotEmitted('profile-saved');
    }

    public function test_pilihan_periode_analisis_sama_dengan_periode_yang_dipahami_period_for(): void
    {
        $component = Livewire::actingAs($this->createUser())
            ->test(InputProfilIndikator::class)
            ->call('loadProfile');

        $this->assertSame(
            array_keys(QualityIndicatorProfile::ANALYSIS_PERIODS),
            array_keys($component->get('analysisPeriodOptions'))
        );

        foreach (array_keys(QualityIndicatorProfile::ANALYSIS_PERIODS) as $months) {
            $profile = QualityIndicatorProfileFactory::new()->create(['analysis_period' => null]);

            Livewire::actingAs($this->createUser())
                ->test(InputProfilIndikator::class)
                ->call('loadProfile', $profile->id)
                ->set('analysis_period', (string) $months)
                ->call('save')
                ->assertHasNoErrors();

            $this->assertSame($months, (int) $profile->refresh()->analysis_period);
        }
    }

    public function test_periode_analisis_boleh_kosong(): void
    {
        $profile = QualityIndicatorProfileFactory::new()->create(['analysis_period' => 3]);

        Livewire::actingAs($this->createUser())
            ->test(InputProfilIndikator::class)
            ->call('loadProfile', $profile->id)
            ->set('analysis_period', '')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNull($profile->refresh()->analysis_period);
    }

    public function test_periode_analisis_di_luar_1_3_6_12_ditolak(): void
    {
        $profile = QualityIndicatorProfileFactory::new()->create(['analysis_period' => 1]);

        Livewire::actingAs($this->createUser())
            ->test(InputProfilIndikator::class)
            ->call('loadProfile', $profile->id)
            ->set('analysis_period', '7')
            ->call('save')
            ->assertHasErrors(['analysis_period' => 'in'])
            ->assertNotEmitted('profile-saved');

        $this->assertSame(1, (int) $profile->refresh()->analysis_period);
    }

    public function test_profil_lama_dengan_periode_tidak_baku_tetap_bisa_dibuka_tetapi_wajib_dipilih_ulang(): void
    {
        $profile = QualityIndicatorProfileFactory::new()->create(['analysis_period' => 2]);

        $component = Livewire::actingAs($this->createUser())
            ->test(InputProfilIndikator::class)
            ->call('loadProfile', $profile->id)
            ->assertSet('analysis_period', 2)
            ->assertSee('2 bulan (tidak berlaku, pilih ulang)')
            ->set('title', 'Judul Diubah')
            ->call('save')
            ->assertHasErrors(['analysis_period' => 'in'])
            ->assertSee('Periode analisis harus Bulanan, Triwulan, Semester, atau Tahunan.');

        $profile->refresh();
        $this->assertSame(2, (int) $profile->analysis_period);
        $this->assertNotSame('Judul Diubah', $profile->title);

        $component
            ->set('analysis_period', '3')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(3, (int) $profile->refresh()->analysis_period);
    }

    public function test_admin_bisa_mengisi_target_terstruktur(): void
    {
        $profile = QualityIndicatorProfileFactory::new()->create(['standard' => '≤ 5%']);

        Livewire::actingAs($this->createUser())
            ->test(InputProfilIndikator::class)
            ->call('loadProfile', $profile->id)
            ->assertSet('target_operator', null)
            ->set('target_operator', 'lte')
            ->set('target_value', '5')
            ->call('save')
            ->assertHasNoErrors();

        $profile->refresh();
        $this->assertSame('lte', $profile->target_operator);
        $this->assertSame(5.0, $profile->target_value);
        $this->assertSame('≤ 5%', $profile->standard);

        Livewire::actingAs($this->createUser())
            ->test(InputProfilIndikator::class)
            ->call('loadProfile', $profile->id)
            ->assertSet('target_operator', 'lte')
            ->assertSet('target_value', 5.0);
    }

    public function test_operator_target_boleh_kosong_tanpa_default(): void
    {
        $profile = QualityIndicatorProfileFactory::new()->create(['target_operator' => 'gte', 'target_value' => 80]);

        Livewire::actingAs($this->createUser())
            ->test(InputProfilIndikator::class)
            ->call('loadProfile', $profile->id)
            ->set('target_operator', '')
            ->call('save')
            ->assertHasNoErrors();

        $profile->refresh();
        $this->assertNull($profile->target_operator);
        $this->assertSame(80.0, $profile->target_value);
    }

    public function test_operator_dan_nilai_target_divalidasi(): void
    {
        Livewire::actingAs($this->createUser())
            ->test(InputProfilIndikator::class)
            ->call('loadProfile')
            ->set('target_operator', 'eq')
            ->set('target_value', 'delapan puluh')
            ->call('save')
            ->assertHasErrors(['target_operator' => 'in', 'target_value' => 'numeric']);
    }

    public function test_admin_bisa_mencari_profil_indikator_berdasarkan_judul(): void
    {
        QualityIndicatorProfileFactory::new()->create(['title' => 'Kepatuhan Cuci Tangan']);
        QualityIndicatorProfileFactory::new()->create(['title' => 'Waktu Tunggu Rawat Jalan']);

        Livewire::actingAs($this->createUser())
            ->test(ProfilIndikator::class)
            ->call('loadProperties')
            ->set('cari', 'cuci tangan')
            ->assertSee('Kepatuhan Cuci Tangan')
            ->assertDontSee('Waktu Tunggu Rawat Jalan');
    }
}
