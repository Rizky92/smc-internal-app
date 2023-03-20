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
use Illuminate\Support\Str;
use Livewire\Component;

class JurnalPiutangLunas extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    public $tglAwal;

    public $tglAkhir;

    public $kodeRekening;

    public $jenisPeriode;

    protected function queryString()
    {
        return [
            'tglAwal' => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function render()
    {
        return view('livewire.keuangan.jurnal-piutang-lunas')
            ->layout(BaseLayout::class, ['title' => 'Penarikan Data Penagihan Piutang Dibayar dari Jurnal']);
    }

    public function getAkunPenagihanPiutangProperty()
    {
        return DB::connection('mysql_sik')
            ->table('rekening')
            ->whereIn('kd_rek', PiutangDilunaskan::query()->groupBy('kd_rek')->pluck('kd_rek')->toArray())
            ->pluck('nm_rek', 'kd_rek')
            ->all();
    }

    public function tarikDataTerbaru()
    {
        PiutangDilunaskan::refreshModel();

        $this->fullRefresh();

        $this->flashSuccess('Data Berhasil Diperbaharui!');
    }

    public function getDataPiutangDilunaskanProperty()
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

    protected function defaultValues()
    {
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
                ->get()
                ->map(function (PiutangDilunaskan $piutang) {
                    return [
                        'no_jurnal' => $piutang->no_jurnal,
                        'waktu_jurnal' => carbon($piutang->waktu_jurnal)->format('Y-m-d'),
                        'no_rawat' => $piutang->no_rawat,
                        'no_rkm_medis' => $piutang->no_rkm_medis . ' ' . $piutang->nm_pasien . ' ' . "({$piutang->umur})",
                        'nama_penjamin' => $piutang->nama_penjamin,
                        'no_tagihan' => $piutang->no_tagihan,
                        'nik_penagih' => $piutang->nik_penagih . ' ' . $piutang->nama_penagih,
                        'nik_penyetuju' => $piutang->nik_penyetuju . ' ' . $piutang->nama_penyetuju,
                        'piutang_dibayar' => $piutang->piutang_dibayar,
                        'tgl_penagihan' => carbon($piutang->tgl_penagihan)->format('Y-m-d'),
                        'tgl_jatuh_tempo' => carbon($piutang->tgl_jatuh_tempo)->format('Y-m-d'),
                        'tgl_bayar' => carbon($piutang->tgl_bayar)->format('Y-m-d'),
                        'status' => $piutang->status,
                        'nik_validasi' => $piutang->nik_validasi . ' ' . $piutang->nama_pemvalidasi,
                        'kd_rek' => $piutang->kd_rek . ' ' . $piutang->nm_rek,
                        'keterangan' => $piutang->keterangan,
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
        return [
            'RS Samarinda Medika Citra',
            'Penarikan Data Penagihan Piutang Dibayar dari Jurnal',
            now()->format('d F Y'),
            'Berdasarkan Tgl. ' . Str::title($this->jenisPeriode) . ' periode ' . carbon($this->tglAwal)->format('d F Y') . ' - ' . carbon($this->tglAkhir)->format('d F Y'),
        ];
    }
}
