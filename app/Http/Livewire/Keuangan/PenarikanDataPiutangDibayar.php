<?php

namespace App\Http\Livewire\Keuangan;

use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PenarikanDataPiutangDibayar extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    public $periodeAwal;

    public $periodeAkhir;

    protected function queryString()
    {
        return [
            'periodeAwal' => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'periodeAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function render()
    {
        return view('livewire.keuangan.penarikan-data-piutang-dibayar')
            ->layout(BaseLayout::class, ['title' => 'Penarikan Data Penagihan Piutang Dibayar dari Jurnal']);
    }

    public function getAkunPenagihanPiutangProperty()
    {
        return DB::connection('mysql_sik')->table('akun_penagihan_piutang')->first();
    }

    protected function defaultValues()
    {
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
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
            '#',
            'No. Jurnal',
            'Waktu',
            'No. Penagihan',
            'Keterangan',
            'Status',
            'Nominal (Rp)',
            'Akun Bayar',
            'Kode Rekening',
            'Nama Rekening',
            'Supplier',
            'Nama Pegawai',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            //
        ];
    }
}
