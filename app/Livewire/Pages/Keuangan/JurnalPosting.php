<?php

namespace App\Livewire\Pages\Keuangan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Keuangan\Jurnal\Jurnal;
use App\View\Components\BaseLayout;
use Hash;
use Illuminate\View\View;
use Livewire\Component;

class JurnalPosting extends Component
{
    use DeferredLoading;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    /** @var "-"|"U"|"P" */
    public $jenis;

    protected function queryString(): array
    {
        return [
            'jenis'    => ['except' => '-', 'as' => 'jenis'],
            'tglAwal'  => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getDataJurnalPostingProperty()
    {
        return $this->isDeferred ? [] : Jurnal::query()
            ->jurnalPosting($this->tglAwal, $this->tglAkhir)
            ->search($this->cari, ['no_jurnal', 'tgl_jurnal', 'jam_jurnal', 'keterangan'])
            ->paginate($this->perpage);
    }

    public function getTotalDebetKreditProperty()
    {
        return $this->isDeferred ? [] : Jurnal::query()
            ->jumlahDebetKreditJurnalPosting($this->tglAwal, $this->tglAkhir)
            ->search($this->cari)
            ->first();
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.jurnal-posting')
            ->layout(BaseLayout::class, ['title' => 'Posting Jurnal']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->jenis = '-';
    }

    public function cetak(): void
    {
        $hashed = base64_encode(Jurnal::query()
            ->jurnalPosting($this->tglAwal, $this->tglAkhir)
            ->search($this->cari, ['no_jurnal', 'tgl_jurnal', 'jam_jurnal', 'keterangan'])
            ->pluck('no_jurnal'));
        
        $this->redirectRoute('admin.keuangan.cetak-posting-jurnal', [
            'dataJurnal' => $hashed,
        ]);
    }
}
