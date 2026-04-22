<?php

namespace App\Livewire\Pages\Keuangan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Keuangan\PaketOperasi;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class TarifOperasi extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    protected function queryString(): array
    {
        return [
            //
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getCollectionProperty()
    {
        return $this->isDeferred ? [] : PaketOperasi::query()
            ->tarifOperasi()
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.tarif-operasi')
            ->layout(BaseLayout::class, ['title' => 'Tarif Operasi']);
    }

    protected function defaultValues(): void
    {
        //
    }

    protected function dataPerSheet(): array
    {
        return [
            fn () => PaketOperasi::query()
                ->tarifOperasi()
                ->search($this->cari)
                ->cursor()
                ->map(fn (PaketOperasi $model): array => [
                    $model->kode_paket,
                    $model->nm_perawatan,
                    $model->kategori,
                    $model->operator1,
                    $model->operator2,
                    $model->operator3,
                    $model->asisten_operator1,
                    $model->asisten_operator2,
                    $model->asisten_operator3,
                    $model->instrumen,
                    $model->dokter_anestesi,
                    $model->asisten_anestesi,
                    $model->asisten_anestesi2,
                    $model->dokter_anak,
                    $model->perawaat_resusitas,
                    $model->bidan,
                    $model->bidan2,
                    $model->bidan3,
                    $model->perawat_luar,
                    $model->alat,
                    $model->sewa_ok,
                    $model->akomodasi,
                    $model->bagian_rs,
                    $model->omloop,
                    $model->omloop2,
                    $model->omloop3,
                    $model->omloop4,
                    $model->omloop5,
                    $model->sarpras,
                    $model->dokter_pjanak,
                    $model->dokter_umum,
                    $model->jumlah,
                    $model->png_jawab,
                    $model->kelas,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Kode Paket',
            'Nama Operasi',
            'Kategori',
            'Operator 1',
            'Operator 2',
            'Operator 3',
            'Asisten Op 1',
            'Asisten Op 2',
            'Asisten Op 3',
            'Instrumen',
            'dr Anestesi',
            'Asisten Anes 1',
            'Asisten Anes 2',
            'dr Anak',
            'Perawat Resus',
            'Bidan 1',
            'Bidan 2',
            'Bidan 3',
            'Perawat Luar',
            'Alat',
            'Sewa OK/VK',
            'Akomodasi',
            'N.M.S.',
            'Onloop 1',
            'Onloop 2',
            'Onloop 3',
            'Onloop 4',
            'Onloop 5',
            'Sarpras',
            'dr Pj Anak',
            'dr Umum',
            'Total',
            'Jenis Bayar',
            'Kelas',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Tarif Operasi',
        ];
    }
}
