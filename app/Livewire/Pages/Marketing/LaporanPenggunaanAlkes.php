<?php

namespace App\Livewire\Pages\Marketing;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Perawatan\RegistrasiPasien;
use App\Models\Perawatan\TindakanRalanDokter;
use App\Models\Perawatan\TindakanRalanDokterPerawat;
use App\Models\Perawatan\TindakanRalanPerawat;
use App\Models\Perawatan\TindakanRanapDokter;
use App\Models\Perawatan\TindakanRanapDokterPerawat;
use App\Models\Perawatan\TindakanRanapPerawat;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class LaporanPenggunaanAlkes extends Component
{
    use FlashComponent;
    use Filterable;
    use ExcelExportable;
    use LiveTable;
    use MenuTracker;
    use DeferredLoading;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    protected function queryString(): array
    {
        return [
            'tglAwal'  => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    /**
     * @return array|\Illuminate\Pagination\Paginator
     */
    public function getDataPenggunaanAlkesAudiometriProperty()
    {
        $ralanDr = TindakanRalanDokter::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'audiometri');
        $ralanDrPr = TindakanRalanDokterPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'audiometri');
        $ralanPr = TindakanRalanPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'audiometri');

        $ranapDr = TindakanRanapDokter::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'audiometri');
        $ranapDrPr = TindakanRanapDokterPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'audiometri');
        $ranapPr = TindakanRanapPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'audiometri');

        return $this->isDeferred ? [] : $ralanDr
            ->unionAll($ralanDrPr)
            ->unionAll($ralanPr)
            ->unionAll($ranapDr)
            ->unionAll($ranapDrPr)
            ->unionAll($ranapPr)
            ->paginate($this->perpage);
    }

    /**
     * @return array|\Illuminate\Pagination\Paginator
     */
    public function getDataPenggunaanAlkesSpirometriProperty()
    {
        $ralanDr = TindakanRalanDokter::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'spirometri');
        $ralanDrPr = TindakanRalanDokterPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'spirometri');
        $ralanPr = TindakanRalanPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'spirometri');

        $ranapDr = TindakanRanapDokter::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'spirometri');
        $ranapDrPr = TindakanRanapDokterPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'spirometri');
        $ranapPr = TindakanRanapPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'spirometri');

        return $this->isDeferred ? [] : $ralanDr
            ->unionAll($ralanDrPr)
            ->unionAll($ralanPr)
            ->unionAll($ranapDr)
            ->unionAll($ranapDrPr)
            ->unionAll($ranapPr)
            ->paginate($this->perpage);
    }

    /**
     * @return array|\Illuminate\Pagination\Paginator
     */
    public function getDataPenggunaanAlkesTreadmillProperty()
    {
        $ralanDr = TindakanRalanDokter::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'treadmill');
        $ralanDrPr = TindakanRalanDokterPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'treadmill');
        $ralanPr = TindakanRalanPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'treadmill');

        $ranapDr = TindakanRanapDokter::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'treadmill');
        $ranapDrPr = TindakanRanapDokterPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'treadmill');
        $ranapPr = TindakanRanapPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'treadmill');

        return $this->isDeferred ? [] : $ralanDr
            ->unionAll($ralanDrPr)
            ->unionAll($ralanPr)
            ->unionAll($ranapDr)
            ->unionAll($ranapDrPr)
            ->unionAll($ranapPr)
            ->paginate($this->perpage);
    }

    /**
     * @return array|\Illuminate\Pagination\Paginator
     */
    public function getDataPenggunaanAlkesEKGProperty()
    {
        $ralanDr = TindakanRalanDokter::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'ekg');
        $ralanDrPr = TindakanRalanDokterPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'ekg');
        $ralanPr = TindakanRalanPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'ekg');

        $ranapDr = TindakanRanapDokter::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'ekg');
        $ranapDrPr = TindakanRanapDokterPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'ekg');
        $ranapPr = TindakanRanapPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'ekg');

        return $this->isDeferred ? [] : $ralanDr
            ->unionAll($ralanDrPr)
            ->unionAll($ralanPr)
            ->unionAll($ranapDr)
            ->unionAll($ranapDrPr)
            ->unionAll($ranapPr)
            ->paginate($this->perpage);
    }

    /**
     * @return array|\Illuminate\Pagination\Paginator
     */
    public function getDataPenggunaanAlkesEEGProperty()
    {
        $ralanDr = TindakanRalanDokter::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'eeg');
        $ralanDrPr = TindakanRalanDokterPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'eeg');
        $ralanPr = TindakanRalanPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'eeg');

        $ranapDr = TindakanRanapDokter::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'eeg');
        $ranapDrPr = TindakanRanapDokterPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'eeg');
        $ranapPr = TindakanRanapPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'eeg');

        return $this->isDeferred ? [] : $ralanDr
            ->unionAll($ralanDrPr)
            ->unionAll($ralanPr)
            ->unionAll($ranapDr)
            ->unionAll($ranapDrPr)
            ->unionAll($ranapPr)
            ->paginate($this->perpage);
    }

    /**
     * @return array|\Illuminate\Pagination\Paginator
     */
    public function getDataPenggunaanAlkesEchoProperty()
    {
        $ralanDr = TindakanRalanDokter::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'echo');
        $ralanDrPr = TindakanRalanDokterPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'echo');
        $ralanPr = TindakanRalanPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'echo');

        $ranapDr = TindakanRanapDokter::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'echo');
        $ranapDrPr = TindakanRanapDokterPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'echo');
        $ranapPr = TindakanRanapPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'echo');

        return $this->isDeferred ? [] : $ralanDr
            ->unionAll($ralanDrPr)
            ->unionAll($ralanPr)
            ->unionAll($ranapDr)
            ->unionAll($ranapDrPr)
            ->unionAll($ranapPr)
            ->paginate($this->perpage);
    }

    public function getDataPenggunaanAlkesUSGProperty()
    {
        return $this->isDeferred ? [] : RegistrasiPasien::query();
    }

    public function getDataPenggunaanAlkesThoraxProperty()
    {
        return $this->isDeferred ? [] : RegistrasiPasien::query();
    }

    public function getDataPenggunaanAlkesCTScanProperty()
    {
        return $this->isDeferred ? [] : RegistrasiPasien::query();
    }

    public function getDataPenggunaanAlkesLumbalProperty()
    {
        return $this->isDeferred ? [] : RegistrasiPasien::query();
    }

    public function getDataPenggunaanAlkesPanoramikProperty()
    {
        return $this->isDeferred ? [] : RegistrasiPasien::query();
    }

    public function getDataPenggunaanAlkesMRIProperty()
    {
        return $this->isDeferred ? [] : RegistrasiPasien::query();
    }

    public function render(): View
    {
        return view('livewire.pages.marketing.laporan-penggunaan-alkes')
            ->layout(BaseLayout::class, ['title' => 'LaporanPenggunaanAlkes']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            //
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            //
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            //
        ];
    }
}
