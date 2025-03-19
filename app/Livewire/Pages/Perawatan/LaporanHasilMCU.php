<?php

namespace App\Livewire\Pages\Perawatan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Laboratorium\PeriksaLab;
use App\Models\Perawatan\RegistrasiPasien;
use App\Models\RekamMedis\Penjamin;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class LaporanHasilMCU extends Component
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
    public $penjamin;

    protected function queryString(): array
    {
        return [
            'tglAwal'  => ['except' => now()->startOfMonth()->toDateString(), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->toDateString(), 'as' => 'tgl_akhir'],
            'penjamin' => ['except' => '-'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getDataPasienPoliMCUProperty(): Paginator
    {
        return RegistrasiPasien::query()
            ->with([
                'pasien',
                'poliklinik',
                'penjamin',
                'penilaianHasilMcu',
            ])
            ->whereBetween('tgl_registrasi', [$this->tglAwal, $this->tglAkhir])
            ->where('kd_poli', 'U0036')
            ->when($this->penjamin !== '-', fn (Builder $query) => $query->where('kd_pj', $this->penjamin))
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->orderByRaw("case when kd_pj = 'UMUM / PERSONAL' then 0 else 1 end, kd_pj")
            ->paginate($this->perpage);
    }

    public function getUniquePemeriksaanProperty(): array
    {
        $uniquePemeriksaan = [];

        foreach ($this->pemeriksaan as $no_rawat => $hasilPeriksaLab) {
            foreach ($hasilPeriksaLab as $pemeriksaan) {
                $uniquePemeriksaan[$pemeriksaan->Pemeriksaan] = $pemeriksaan->Pemeriksaan;
            }
        }

        return $uniquePemeriksaan;
    }

    public function getPemeriksaanProperty(): array
    {
        if ($this->isDeferred) {
            return [];
        }

        $pemeriksaan = [];

        foreach ($this->dataPasienPoliMCU as $pasien) {
            $pemeriksaanPasien = PeriksaLab::laporanTindakanLabDetail($this->tglAwal, $this->tglAkhir)
                ->where('periksa_lab.no_rawat', $pasien->no_rawat)
                ->where('reg_periksa.kd_poli', 'U0036')
                ->search($this->cari)
                ->get()
                ->keyBy('Pemeriksaan');

            $pemeriksaan[$pasien->no_rawat] = $pemeriksaanPasien;
        }

        return $pemeriksaan;
    }

    public function getDataPenjaminProperty(): Collection
    {
        return Penjamin::query()
            ->pluck('png_jawab', 'kd_pj')
            ->put('-', 'Semua Jaminan');
    }

    public function render(): View
    {
        return view('livewire.pages.perawatan.laporan-hasil-m-c-u')
            ->layout(BaseLayout::class, ['title' => 'Laporan Hasil MCU']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->toDateString();
        $this->tglAkhir = now()->endOfMonth()->toDateString();
        $this->penjamin = '-';
    }

    /**
     * @return (mixed|string)[][][]
     *
     * @psalm-return array{0: non-empty-list<array<mixed|string>>}
     */
    protected function dataPerSheet(): array
    {
        $data = [];

        $rowSatuan = [
            'Penjamin'      => '',
            'No. Rawat'     => '',
            'No. RM'        => '',
            'Nama'          => '',
            'Tgl. Lahir'    => '',
            'Usia'          => '',
            'Jenis Kelamin' => '',
            'Agama'         => '',
            'NIK'           => '',
            'Tgl. MCU'      => '',
            'Poli'          => '',
            'Radiologi'     => '',
            'EKG'           => '',
            'Spirometri'    => '',
            'Audiometri'    => '',
            'Treadmill'     => '',
            'TB'            => '',
            'BB'            => '',
            'TD'            => '',
            'Nadi'          => '',
            'Respirasi'     => '',
            'Merokok'       => '',
            'Alkohol'       => '',
            'Buta Warna'    => '',
            'Lain-lain'     => '',
            'Kesimpulan'    => '',
            'Anjuran'       => '',
            'Tindakan'      => 'Satuan',
        ];

        foreach ($this->uniquePemeriksaan as $pemeriksaan) {
            $rowSatuan[$pemeriksaan] = $this->pemeriksaan[$this->dataPasienPoliMCU[0]->no_rawat][$pemeriksaan]->satuan ?? '-';
        }

        $data[] = $rowSatuan;

        foreach (['ld', 'la', 'pd', 'pa'] as $type) {
            $rowRujukan = [
                'Penjamin'      => '',
                'No. Rawat'     => '',
                'No. RM'        => '',
                'Nama'          => '',
                'Tgl. Lahir'    => '',
                'Usia'          => '',
                'Jenis Kelamin' => '',
                'Agama'         => '',
                'NIK'           => '',
                'Tgl. MCU'      => '',
                'Poli'          => '',
                'Radiologi'     => '',
                'EKG'           => '',
                'Spirometri'    => '',
                'Audiometri'    => '',
                'Treadmill'     => '',
                'TB'            => '',
                'BB'            => '',
                'TD'            => '',
                'Nadi'          => '',
                'Respirasi'     => '',
                'Merokok'       => '',
                'Alkohol'       => '',
                'Buta Warna'    => '',
                'Lain-lain'     => '',
                'Kesimpulan'    => '',
                'Anjuran'       => '',
                'Tindakan'      => 'Nilai Rujukan (' . strtoupper($type) . ')',
            ];

            foreach ($this->uniquePemeriksaan as $pemeriksaan) {
                $rowRujukan[$pemeriksaan] = $this->pemeriksaan[$this->dataPasienPoliMCU[0]->no_rawat][$pemeriksaan]->{'nilai_rujukan_'.$type} ?? '-';
            }

            $data[] = $rowRujukan;
        }

        foreach ($this->dataPasienPoliMCU as $registrasi) {
            $row = [];
            
            $row['Penjamin']      = $registrasi->penjamin->png_jawab;
            $row['No. Rawat']     = $registrasi->no_rawat;
            $row['No. RM']        = $registrasi->pasien->no_rkm_medis;
            $row['Nama']          = $registrasi->pasien->nm_pasien;
            $row['Tgl. Lahir']    = $registrasi->pasien->tgl_lahir;
            $row['Usia']          = $registrasi->umurdaftar.' '.$registrasi->sttsumur;
            $row['Jenis Kelamin'] = $registrasi->pasien->jk;
            $row['Agama']         = $registrasi->pasien->agama;
            $row['NIK']           = $registrasi->pasien->no_ktp;
            $row['Tgl. MCU']      = $registrasi->tgl_registrasi;
            $row['Poli']          = $registrasi->poliklinik->nm_poli;
            $row['Radiologi']     = optional($registrasi->penilaianHasilMcu)->radiologi;
            $row['EKG']           = optional($registrasi->penilaianHasilMcu)->ekg;
            $row['Spirometri']    = optional($registrasi->penilaianHasilMcu)->spirometri;
            $row['Audiometri']    = optional($registrasi->penilaianHasilMcu)->audiometri;
            $row['Treadmill']     = optional($registrasi->penilaianHasilMcu)->treadmill;
            $row['TB']            = optional($registrasi->penilaianHasilMcu)->tb;
            $row['BB']            = optional($registrasi->penilaianHasilMcu)->bb;
            $row['TD']            = optional($registrasi->penilaianHasilMcu)->td;
            $row['Nadi']          = optional($registrasi->penilaianHasilMcu)->nadi;
            $row['Respirasi']     = optional($registrasi->penilaianHasilMcu)->rr;
            $row['Merokok']       = optional($registrasi->penilaianHasilMcu)->merokok;
            $row['Alkohol']       = optional($registrasi->penilaianHasilMcu)->alkohol;
            $row['Buta Warna']    = optional($registrasi->penilaianHasilMcu)->buta_warna;
            $row['Lain-lain']     = optional($registrasi->penilaianHasilMcu)->lainlain;
            $row['Kesimpulan']    = optional($registrasi->penilaianHasilMcu)->kesimpulan;
            $row['Anjuran']       = optional($registrasi->penilaianHasilMcu)->anjuran;
            $row['Tindakan']      = '';

            foreach ($this->uniquePemeriksaan as $pemeriksaan) {
                $row[$pemeriksaan] = $this->pemeriksaan[$registrasi->no_rawat][$pemeriksaan]->nilai ?? '-';
            }

            $data[] = $row;
        }

        return [$data];
    }

    protected function columnHeaders(): array
    {
        $headers = [
            'Penjamin',
            'No. Rawat',
            'No. RM',
            'Nama',
            'Tgl. Lahir',
            'Usia',
            'Jenis Kelamin',
            'Agama',
            'NIK',
            'Tgl. MCU',
            'Poli',
            'Radiologi',
            'EKG',
            'Spirometri',
            'Audiometri',
            'Treadmill',
            'TB',
            'BB',
            'TD',
            'Nadi',
            'Respirasi',
            'Merokok',
            'Alkohol',
            'Buta Warna',
            'Lain-lain',
            'Kesimpulan',
            'Anjuran',
            'Tindakan',
        ];

        foreach ($this->uniquePemeriksaan as $pemeriksaan) {
            $headers[] = $pemeriksaan;
        }

        return $headers;
    }

    protected function pageHeaders(): array
    {
        $periodeAwal = carbon($this->tglAwal);
        $periodeAkhir = carbon($this->tglAkhir);

        $periode = 'Periode '.$periodeAwal->translatedFormat('d F Y').' s/d '.$periodeAkhir->translatedFormat('d F Y');

        if ($periodeAwal->isSameDay($periodeAkhir)) {
            $periode = 'Periode '.$periodeAwal->translatedFormat('d F Y');
        }

        return [
            'RS Samarinda Medika Citra',
            'Laporan Hasil Pemeriksaan '. $this->dataPenjamin->get($this->penjamin),
            now()->translatedFormat('d F Y'),
            $periode,
        ];

    }
}
