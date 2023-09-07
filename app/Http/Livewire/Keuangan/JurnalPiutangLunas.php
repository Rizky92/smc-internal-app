<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Keuangan\PiutangDilunaskan;
use App\Support\Livewire\Concerns\DeferredLoading;
use App\Support\Livewire\Concerns\ExcelExportable;
use App\Support\Livewire\Concerns\Filterable;
use App\Support\Livewire\Concerns\FlashComponent;
use App\Support\Livewire\Concerns\LiveTable;
use App\Support\Livewire\Concerns\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

class JurnalPiutangLunas extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    /** @var string */
    public $kodeRekening;

    /** @var string */
    public $jenisPeriode;

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

    public function render(): View
    {
        return view('livewire.keuangan.jurnal-piutang-lunas')
            ->layout(BaseLayout::class, ['title' => 'Penarikan Data Penagihan Piutang Dibayar dari Jurnal']);
    }

    public function getAkunPenagihanPiutangProperty(): array
    {
        return DB::connection('mysql_sik')
            ->table('rekening')
            ->whereIn('kd_rek', PiutangDilunaskan::query()->groupBy('kd_rek')->pluck('kd_rek')->toArray())
            ->pluck('nm_rek', 'kd_rek')
            ->all();
    }

    public function tarikDataTerbaru(): void
    {
        PiutangDilunaskan::refreshModel();

        $this->fullRefresh();

        $this->flashSuccess('Data Berhasil Diperbaharui!');
    }

    public function getDataPiutangDilunaskanProperty(): LengthAwarePaginator
    {
        return PiutangDilunaskan::query()
            ->dataPiutangDilunaskan($this->tglAwal, $this->tglAkhir, $this->kodeRekening, $this->jenisPeriode)
            ->search($this->cari, [
                'piutang_dilunaskan.no_jurnal',
                'jurnal.keterangan',
                'piutang_dilunaskan.no_rawat',
                'piutang_dilunaskan.no_tagihan',
                'piutang_dilunaskan.no_rkm_medis',
                'pasien.nm_pasien',
                'piutang_dilunaskan.kd_pj',
                "if(penjamin.nama_perusahaan = '' or penjamin.nama_perusahaan = '-', penjamin.png_jawab, penjamin.nama_perusahaan)",
                'piutang_dilunaskan.nik_penagih',
                "ifnull(penagih.nama, '-')",
                'piutang_dilunaskan.nik_menyetujui',
                "ifnull(penyetuju.nama, '-')",
                'piutang_dilunaskan.nik_validasi',
                "ifnull(pemvalidasi.nama, '-')",
                'piutang_dilunaskan.kd_rek',
                'piutang_dilunaskan.nm_rek',
            ])
            ->sortWithColumns($this->sortColumns, [
                'nama_penjamin' => DB::raw("if(penjamin.nama_perusahaan = '' or penjamin.nama_perusahaan = '-', penjamin.png_jawab, penjamin.nama_perusahaan)"),
            ])
            ->paginate($this->perpage);
    }

    protected function defaultValues(): void
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
        $this->kodeRekening = '112010';
        $this->jenisPeriode = 'jurnal';
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            PiutangDilunaskan::query()
                ->dataPiutangDilunaskan($this->tglAwal, $this->tglAkhir, $this->kodeRekening, $this->jenisPeriode)
                ->cursor()
                ->map(fn (PiutangDilunaskan $model): array => [
                    'no_jurnal'       => $model->no_jurnal,
                    'waktu_jurnal'    => carbon($model->waktu_jurnal)->format('Y-m-d'),
                    'no_rawat'        => $model->no_rawat,
                    'no_rkm_medis'    => $model->no_rkm_medis . ' ' . $model->nm_pasien . ' ' . "({$model->umur})",
                    'nama_penjamin'   => $model->nama_penjamin,
                    'no_tagihan'      => $model->no_tagihan,
                    'nik_penagih'     => $model->nik_penagih . ' ' . $model->nama_penagih,
                    'nik_penyetuju'   => $model->nik_penyetuju . ' ' . $model->nama_penyetuju,
                    'piutang_dibayar' => floatval($model->piutang_dibayar),
                    'tgl_penagihan'   => carbon($model->tgl_penagihan)->format('Y-m-d'),
                    'tgl_jatuh_tempo' => carbon($model->tgl_jatuh_tempo)->format('Y-m-d'),
                    'tgl_bayar'       => carbon($model->tgl_bayar)->format('Y-m-d'),
                    'status'          => $model->status,
                    'nik_validasi'    => $model->nik_validasi . ' ' . $model->nama_pemvalidasi,
                    'kd_rek'          => $model->kd_rek . ' ' . $model->nm_rek,
                    'keterangan'      => $model->keterangan,
                ])
                ->all(),
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
            'Verifikasi',
            'Nominal',
            'Tgl. Tagihan',
            'Tgl. Jatuh Tempo',
            'Tgl. Dibayar',
            'Status',
            'Validasi oleh',
            'Rekening',
            'Keterangan',
        ];
    }

    protected function pageHeaders(): array
    {
        $periodeAwal = carbon($this->tglAwal);
        $periodeAkhir = carbon($this->tglAkhir);

        $periode = 'periode ' . $periodeAwal->translatedFormat('d F Y') . ' s.d. ' . $periodeAkhir->translatedFormat('d F Y');

        if ($periodeAwal->isSameDay($periodeAkhir)) {
            $periode = $periodeAwal->translatedFormat('d F Y');
        }

        return [
            'RS Samarinda Medika Citra',
            'Penarikan Data Penagihan Piutang Dibayar dari Jurnal',
            now()->translatedFormat('d F Y'),
            'Berdasarkan Tgl. ' . Str::title($this->jenisPeriode) . ', ' . $periode,
        ];
    }
}
