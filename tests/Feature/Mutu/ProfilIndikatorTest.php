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
