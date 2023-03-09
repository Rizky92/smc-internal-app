<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Keuangan\PiutangDilunaskan;
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

    public $kodeRekening;

    public $jenisPeriode;

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

    public function getDataPiutangDilunaskanProperty()
    {
        return PiutangDilunaskan::query()
            ->dataPiutangDilunaskan($this->periodeAwal, $this->periodeAkhir)
            // ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    protected function defaultValues()
    {
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            PiutangDilunaskan::query()
                ->dataPiutangDilunaskan($this->periodeAwal, $this->periodeAkhir)
                ->cursor()
                ->map(function (PiutangDilunaskan $piutang) {
                    return [
                        'no_jurnal' => $piutang->no_jurnal,
                        'waktu_jurnal' => carbon($piutang->waktu_jurnal)->format('Y-m-d'),
                        'no_rawat' => $piutang->no_rawat,
                        'pasien' => "{$piutang->pasien->nm_pasien} ({$piutang->registrasi->umurdaftar} {$piutang->registrasi->sttsumur})",
                        'penjamin' => $piutang->kd_pj . ' ' . optional($piutang->penjamin->nama_penjamin),
                        'no_tagihan' => $piutang->no_tagihan,
                        'penagih' => $piutang->nik_penagih . ' ' . optional($piutang->penagih)->nama,
                        'verifikasi' => $piutang->nik_menyetujui . ' ' . optional($piutang->penyetuju)->nama,
                        'nominal' => $piutang->piutang_dibayar,
                        'tgl_tagihan' => carbon($piutang->tgl_tagihan)->format('Y-m-d'),
                        'tgl_jatuh_tempo' => carbon($piutang->tgl_jatuh_tempo)->format('Y-m-d'),
                        'tgl_dibayar' => carbon($piutang->tgl_bayar)->format('Y-m-d'),
                        'status' => $piutang->status,
                        'keterangan' => $piutang->jurnal->keterangan,
                        'validasi' => $piutang->nik_validasi . ' ' . optional($piutang->pemvalidasi)->nama,
                        'kd_rek' => $piutang->kd_rek,
                        'nm_rek' => $piutang->nm_akun,                        
                    ];
                }),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Jurnal',
            'Tgl. Jurnal',
            'No. Rawat',
            'Pasien',
            'Penjamin',
            'No. Tagihan',
            'Penagih',
            'Verifikasi oleh',
            'Nominal (Rp)',
            'Tgl. Tagihan',
            'Tgl. Jatuh Tempo',
            'Tgl. Dibayar',
            'Status',
            'Keterangan',
            'Validasi oleh',
            'Kode Rekening',
            'Nama Rekening',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Penarikan Data Penagihan Piutang Dibayar dari Jurnal',
            now()->format('d F Y'),
            carbon($this->periodeAwal)->format('d F Y') . ' - ' . carbon($this->periodeAkhir)->format('d F Y'),
        ];
    }
}
