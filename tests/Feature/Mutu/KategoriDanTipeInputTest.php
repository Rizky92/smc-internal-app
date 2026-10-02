<?php

namespace Tests\Feature\Mutu;

use App\Livewire\Pages\Mutu\KategoriIndikator;
use App\Livewire\Pages\Mutu\Modal\InputKategoriIndikator;
use App\Livewire\Pages\Mutu\Modal\InputTipeInputIndikator;
use App\Livewire\Pages\Mutu\TipeInputIndikator;
use App\Models\Quality\QualityIndicatorCategory;
use App\Models\Quality\QualityIndicatorInputType;
use Database\Factories\Quality\QualityIndicatorCategoryFactory;
use Database\Factories\Quality\QualityIndicatorInputTypeFactory;
use Livewire\Livewire;

class KategoriDanTipeInputTest extends MutuTestCase
{
    public function test_admin_bisa_menambah_kategori_indikator(): void
    {
        Livewire::actingAs($this->createUser())
            ->test(InputKategoriIndikator::class)
            ->call('loadCategory')
            ->set('name', 'Keselamatan Pasien')
            ->call('save')
            ->assertHasNoErrors()
            ->assertEmitted('category-saved');

        $this->assertDatabaseHas('quality_indicator_categories', ['name' => 'Keselamatan Pasien'], 'mysql_smc');
    }

    public function test_admin_bisa_mengubah_kategori_indikator(): void
    {
        $category = QualityIndicatorCategoryFactory::new()->create(['name' => 'Nama Lama']);

        Livewire::actingAs($this->createUser())
            ->test(InputKategoriIndikator::class)
            ->call('loadCategory', $category->id)
            ->assertSet('name', 'Nama Lama')
            ->set('name', 'Nama Baru')
            ->call('save')
            ->assertEmitted('category-saved');

        $this->assertSame('Nama Baru', $category->fresh()->name);
        $this->assertSame(1, QualityIndicatorCategory::where('name', 'like', 'Nama %')->count());
    }

    public function test_admin_bisa_mencari_kategori_indikator(): void
    {
        QualityIndicatorCategoryFactory::new()->create(['name' => 'Keselamatan Pasien']);
        QualityIndicatorCategoryFactory::new()->create(['name' => 'Efisiensi Layanan']);

        Livewire::actingAs($this->createUser())
            ->test(KategoriIndikator::class)
            ->call('loadProperties')
            ->set('cari', 'keselamatan')
            ->assertSee('Keselamatan Pasien')
            ->assertDontSee('Efisiensi Layanan');
    }

    public function test_admin_bisa_menambah_tipe_input_indikator(): void
    {
        Livewire::actingAs($this->createUser())
            ->test(InputTipeInputIndikator::class)
            ->call('loadInputType')
            ->set('name', 'Sensus Harian')
            ->call('save')
            ->assertHasNoErrors()
            ->assertEmitted('input-type-saved');

        $this->assertDatabaseHas('quality_indicator_input_types', ['name' => 'Sensus Harian'], 'mysql_smc');
    }

    public function test_admin_bisa_mengubah_tipe_input_indikator(): void
    {
        $inputType = QualityIndicatorInputTypeFactory::new()->create(['name' => 'Nama Lama']);

        Livewire::actingAs($this->createUser())
            ->test(InputTipeInputIndikator::class)
            ->call('loadInputType', $inputType->id)
            ->assertSet('name', 'Nama Lama')
            ->set('name', 'Nama Baru')
            ->call('save')
            ->assertEmitted('input-type-saved');

        $this->assertSame('Nama Baru', $inputType->fresh()->name);
        $this->assertSame(1, QualityIndicatorInputType::where('name', 'like', 'Nama %')->count());
    }

    public function test_admin_bisa_mencari_tipe_input_indikator(): void
    {
        QualityIndicatorInputTypeFactory::new()->create(['name' => 'Sensus Harian']);
        QualityIndicatorInputTypeFactory::new()->create(['name' => 'Survei Bulanan']);

        Livewire::actingAs($this->createUser())
            ->test(TipeInputIndikator::class)
            ->call('loadProperties')
            ->set('cari', 'sensus')
            ->assertSee('Sensus Harian')
            ->assertDontSee('Survei Bulanan');
    }
}
