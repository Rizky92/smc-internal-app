<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Keuangan\NotaSelesai;
use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class LaporanSelesaiBillingPasien extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

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
     * @return \Illuminate\Contracts\Pagination\Paginator|array
     */
    public function getBillingYangDiselesaikanProperty()
    {
        return $this->isDeferred
            ? []
            : NotaSelesai::query()
                ->billingYangDiselesaikan($this->tglAwal, $this->tglAkhir)
                ->search($this->cari, [
                    "nota_selesai.no_rawat",
                    "pasien.no_rkm_medis",
                    "pasien.nm_pasien",
                    "ifnull(nota_pasien.no_nota, '-')",
                    "ifnull(kamar.kd_kamar, '-')",
                    "ifnull(bangsal.kd_bangsal, '-')",
                    "ifnull(bangsal.nm_bangsal, '-')",
                    "nota_selesai.status_pasien",
                    "nota_selesai.bentuk_bayar",
                    "penjab.png_jawab",
                    "nota_selesai.tgl_penyelesaian",
                    "nota_selesai.user_id",
                    "pegawai.nama",
                ])
                ->sortWithColumns($this->sortColumns, [
                    'nm_pasien'    => DB::raw("trim(pasien.nm_pasien)"),
                    'ruangan'      => DB::raw("ifnull(concat(kamar.kd_kamar, ' ', bangsal.nm_bangsal), '-')"),
                    'nama_pegawai' => DB::raw("concat(nota_selesai.user_id, ' ', pegawai.nama)"),
                    'besar_bayar'  => DB::raw("coalesce(nota_pasien.besar_bayar, piutang_pasien.totalpiutang)"),
                ])
                ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.keuangan.laporan-selesai-billing-pasien')
            ->layout(BaseLayout::class, ['title' => 'Laporan Penyelesaian Billing Pasien per Petugas']);
    }

    public function tarikDataTerbaru(): void
    {
        NotaSelesai::refreshModel();

        $this->fullRefresh();

        $this->flashSuccess("Data Berhasil Diperbaharui!");
    }

    protected function defaultValues(): void
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            NotaSelesai::query()
                ->billingYangDiselesaikan($this->tglAwal, $this->tglAkhir)
                ->get()
                ->map(fn (NotaSelesai $model) => [
                    'no_rawat'         => $model->no_rawat,
                    'no_rkm_medis'     => $model->no_rkm_medis,
                    'nm_pasien'        => $model->nm_pasien,
                    'no_nota'          => $model->no_nota,
                    'ruangan'          => $model->ruangan,
                    'status_pasien'    => $model->status_pasien,
                    'bentuk_bayar'     => $model->bentuk_bayar,
                    'besar_bayar'      => floatval($model->besar_bayar),
                    'png_jawab'        => $model->png_jawab,
                    'tgl_penyelesaian' => $model->tgl_penyelesaian,
                    'nama_pegawai'     => $model->nama_pegawai,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Rawat',
            'No. RM',
            'Pasien',
            'No. Nota',
            'Ruang Inap',
            'Jenis Perawatan',
            'Bentuk Pembayaran',
            'Nominal Yang Dibayarkan (RP)',
            'Asuransi',
            'Diselesaikan Pada',
            'Oleh Petugas',
        ];
    }

    protected function pageHeaders(): array
    {
        $periodeAwal = carbon($this->tglAwal);
        $periodeAkhir = carbon($this->tglAkhir);

        $periode = 'Periode ' . $periodeAwal->translatedFormat('d F Y') . ' s.d. ' . $periodeAkhir->translatedFormat('d F Y');

        if ($periodeAwal->isSameDay($periodeAkhir)) {
            $periode = $periodeAwal->translatedFormat('d F Y');
        }

        return [
            'RS Samarinda Medika Citra',
            'Laporan Penyelesaian Billing Pasien per Petugas',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
