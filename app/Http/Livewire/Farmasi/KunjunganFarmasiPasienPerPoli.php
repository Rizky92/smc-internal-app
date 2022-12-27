<?php

namespace App\Http\Livewire\Farmasi;

use App\View\Components\BaseLayout;
use Livewire\Component;
use Livewire\WithPagination;

class KunjunganFarmasiPasienPerPoli extends Component
{
    use WithPagination;

    public $cari;

    public $periodeAwal;

    public $periodeAkhir;

    public $perpage;

    protected $listeners = [
        'beginExcelExport',
    ];

    public function mount()
    {
        $this->cari = '';
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->perpage = 25;
    }

    public function getColumnHeadersProperty()
    {
        return [
            'No. Resep',
            'No. Rawat',
            'Pasien',
            'Jenis Perawatan',
            'Asal Poli',
            'Tgl. Peresepan',
            'Tgl. Validasi',
            'Jumlah Obat',
            'Total',
        ];
    }

    public function render()
    {
        return view('livewire.farmasi.kunjungan-farmasi-pasien-per-poli')
            ->layout(BaseLayout::class, ['title' => 'Kunjungan Farmasi Pasien per Poli']);
    }
}
