<?php

namespace App\Livewire\Pages\Keuangan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Bidang;
use App\Models\Keuangan\RKAT\PemakaianAnggaran;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class RKATPelaporan extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    /** @var string */
    public $tahun;

    /** @var int */
    public $bidang;

    protected function queryString(): array
    {
        return [
            'tglAwal'  => ['except' => now()->startOfMonth()->toDateString(), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->toDateString(), 'as' => 'tgl_akhir'],
            'tahun'    => ['except' => now()->format('Y')],
            'bidang'   => ['except' => -1],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getDataPenggunaanRKATProperty(): Paginator
    {
        return PemakaianAnggaran::query()
            ->penggunaanRKAT($this->bidang, $this->tahun, $this->cari)
            ->paginate($this->perpage);
    }

    public function getDataTahunProperty(): array
    {
        return collect(range((int) now()->format('Y'), 2023, -1))
            ->mapWithKeys(fn (int $v, int $_): array => [$v => $v])
            ->all();
    }

    public function getDataBidangProperty(): Collection
    {
        return Bidang::query()
            ->with('descendantsAndSelf')
            ->isRoot()
            ->get()
            ->map
            ->descendantsAndSelf
            ->flatten()
            ->mapWithKeys(fn (Bidang $model) => [
                $model->id => str($model->nama)
                    ->padLeft(strlen($model->nama) + (intval($model->depth) * 8), html_entity_decode('&nbsp;'))
                    ->value(),
            ]);
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.rkat-pelaporan')
            ->layout(BaseLayout::class, ['title' => 'Pelaporan Penggunaan RKAT']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->toDateString();
        $this->tglAkhir = now()->endOfMonth()->toDateString();
        $this->tahun = now()->format('Y');
        $this->bidang = -1;
    }

    /**
     * @psalm-return array{0: mixed}
     */
    protected function dataPerSheet(): array
    {
        return [
            PemakaianAnggaran::query()
                ->with('detail')
                ->penggunaanRKAT($this->bidang, $this->tahun, $this->cari)
                ->cursor()
                ->flatMap(fn (PemakaianAnggaran $model) => $model->detail->map(fn ($detail) => [
                    'bidang'      => $model->anggaranBidang->bidang->nama,
                    'judul'       => $model->judul,
                    'anggaran'    => $model->anggaranBidang->anggaran->nama,
                    'tahun'       => $model->anggaranBidang->tahun,
                    'tgl_dipakai' => $model->tgl_dipakai,
                    'keterangan'  => $detail->keterangan,
                    'nominal'     => floatval($detail->nominal),
                    'petugas'     => $model->user_id.' '.$model->petugas->nama,
                ]))
                ->all(),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Bidang',
            'Judul',
            'Anggaran',
            'Tahun',
            'Tgl. Dipakai',
            'Keterangan',
            'Nominal',
            'Petugas',
        ];
    }

    protected function pageHeaders(): array
    {
        $periodeAwal = carbon($this->tglAwal);
        $periodeAkhir = carbon($this->tglAkhir);

        $periode = 'Periode '.$periodeAwal->translatedFormat('d F Y').' s.d. '.$periodeAkhir->translatedFormat('d F Y');

        if ($periodeAwal->isSameDay($periodeAkhir)) {
            $periode = $periodeAwal->translatedFormat('d F Y');
        }

        return [
            'RS Samarinda Medika Citra',
            'Pelaporan RKAT tahun '.$this->tahun,
            'Per '.carbon($this->tglAkhir)->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
