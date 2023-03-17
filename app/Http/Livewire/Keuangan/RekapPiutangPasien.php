<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Keuangan\PiutangPasien;
use App\Models\RekamMedis\Penjamin;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class RekapPiutangPasien extends Component
{
    use WithPagination, FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker;

    public $caraBayar;

    public $tglAwal;

    public $tglAkhir;

    protected function queryString()
    {
        return [
            'tglAwal' => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
            'caraBayar' => ['except' => '', 'as' => 'kdpj'],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function getPenjaminProperty()
    {
        return Penjamin::where('status', '1')->pluck('png_jawab', 'kd_pj');
    }

    public function getPiutangPasienProperty()
    {
        return PiutangPasien::query()
            ->rekapPiutangPasien($this->tglAwal, $this->tglAkhir, $this->caraBayar)
            ->search($this->cari, [
                'piutang_pasien.no_rawat',
                'piutang_pasien.no_rkm_medis',
                'pasien.nm_pasien',
                'piutang_pasien.status',
                'penjab.png_jawab',
            ])
            ->sortWithColumns($this->sortColumns, [
                'total'     => 'piutang_pasien.totalpiutang',
                'uang_muka' => 'piutang_pasien.uangmuka',
                'terbayar'  => DB::raw('ifnull(sisa_piutang.sisa, 0)'),
                'sisa'      => DB::raw('(piutang_pasien.sisapiutang - ifnull(sisa_piutang.sisa, 0))'),
                'penjamin'  => 'penjab.png_jawab',
            ])
            ->paginate($this->perpage);
    }

    public function getTotalTagihanPiutangPasienProperty()
    {
        return PiutangPasien::rekapPiutangPasien(
            $this->tglAwal,
            $this->tglAkhir,
            $this->caraBayar,
            $this->cari
        )
            ->sum(DB::raw('round(piutang_pasien.sisapiutang - ifnull(sisa_piutang.sisa, 0))'));
    }

    public function render()
    {
        return view('livewire.keuangan.rekap-piutang-pasien')
            ->layout(BaseLayout::class, ['title' => 'Rekap Data Tagihan Piutang Pasien']);
    }

    protected function defaultValues()
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
        $this->caraBayar = '';
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        $query = PiutangPasien::rekapPiutangPasien($this->tglAwal, $this->tglAkhir, $this->caraBayar, '');

        return [
            // TODO: ubah cara berikut dengan callback
            collect($query->orderBy('penjab.png_jawab')->get()->toArray())
                ->merge([
                    [
                        'no_rawat' => 'TOTAL',
                        'no_rkm_medis' => '',
                        'nm_pasien' => '',
                        'tgl_piutang' => '',
                        'status' => '',
                        'total' => $query->sum(DB::raw('round(piutang_pasien.totalpiutang, 2)')),
                        'uang_muka' => $query->sum(DB::raw('round(piutang_pasien.uangmuka, 2)')),
                        'terbayar' => $query->sum(DB::raw('round(ifnull(sisa_piutang.sisa, 0), 2)')),
                        'sisa' => $query->sum(DB::raw('round(piutang_pasien.sisapiutang - ifnull(sisa_piutang.sisa, 0), 2)')),
                        'tgltempo' => '',
                        'penjamin' => '',
                    ]
                ])
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Rawat',
            'No. RM',
            'Pasien',
            'Tgl. Piutang',
            'Status',
            'Total (RP)',
            'Uang Muka (RP)',
            'Terbayar (RP)',
            'Sisa (RP)',
            'Tgl. Jatuh Tempo',
            'Penjamin',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Rekap Data Tagihan Piutang Pasien',
            now()->format('d F Y'),
        ];
    }
}
