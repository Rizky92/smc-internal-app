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
            ->selectRaw('reg_periksa.*, penjab.png_jawab, pasien.nm_pasien, pasien.tgl_lahir, pasien.no_ktp, pasien.jk, pasien.agama, poliklinik.nm_poli')
            ->join('pasien', 'reg_periksa.no_rkm_medis', 'pasien.no_rkm_medis')
            ->join('poliklinik', 'reg_periksa.kd_poli', 'poliklinik.kd_poli')
            ->join('penjab', 'reg_periksa.kd_pj', 'penjab.kd_pj')
            ->with('penilaianHasilMcu')
            ->where('poliklinik.kd_poli', 'U0036')
            ->whereBetween('reg_periksa.tgl_registrasi', [$this->tglAwal, $this->tglAkhir])
            ->when($this->penjamin !== '-', fn (Builder $query) => $query->where('reg_periksa.kd_pj', $this->penjamin))
            ->search($this->cari, [
                'pasien.nm_pasien',
                'pasien.no_ktp',
                'pasien.tgl_lahir',
                'penjab.png_jawab',
            ])
            ->sortWithColumns($this->sortColumns)
            ->orderByRaw("case when reg_periksa.kd_pj = 'A09' then 0 else 1 end, reg_periksa.kd_pj")
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
            'Penjamin'                           => '',
            'No. Rawat'                          => '',
            'No. RM'                             => '',
            'Nama'                               => '',
            'Tgl. Lahir'                         => '',
            'Usia'                               => '',
            'Jenis Kelamin'                      => '',
            'Agama'                              => '',
            'NIK'                                => '',
            'Tgl. MCU'                           => '',
            'Poli'                               => '',
            'Riwayat Penyakit Sekarang'          => '',
            'Riwayat Penyakit Keluarga'          => '',
            'Riwayat Penyakit Dahulu'            => '',
            'Keadaan Umum'                       => '',
            'Kesadaran'                          => '',
            'TD'                                 => '',
            'HR/Nadi'                            => '',
            'RR/Nafas'                           => '',
            'Tinggi Badan (cm)'                  => '',
            'Berat Badan (kg)'                   => '',
            'Suhu (C)'                           => '',
            'BMI (BB/TB^2)'                      => '',
            'Klasifikasi BMI'                    => '',
            'Lingkar Pinggang'                   => '',
            'Risiko Berdasarkan LP'              => '',
            'Kelenjar Limfe - Submandibula'      => '',
            'Kelenjar Limfe - Axilla'            => '',
            'Kelenjar Limfe - Supraklavikula'    => '',
            'Kelenjar Limfe - Leher'             => '',
            'Kelenjar Limfe - Inguinal'          => '',
            'Muka - Oedema'                      => '',
            'Muka - Nyeri Tekan Sinus Frontalis' => '',
            'Muka - Nyeri Tekan Sinus Maxilaris' => '',
            'Rambut'                             => '',
            'Mata - Palpebra'                    => '',
            'Mata - Sklera'                      => '',
            'Mata - Kornea'                      => '',
            'Mata - Buta Warna'                  => '',
            'Mata - Konjungtiva'                 => '',
            'Mata - Lensa'                       => '',
            'Mata - Pupil'                       => '',
            'Mata - Kacamata'                    => '',
            'Mata - Visus'                       => '',
            'Mata - Luas Lapang Pandang'         => '',
            'Telinga - Lubang'                   => '',
            'Telinga - Daun'                     => '',
            'Telinga - Selaput Dengar'           => '',
            'Telinga - Proc. Mastoideus'         => '',
            'Hidung - Septum Nasi'               => '',
            'Hidung - Lubang Hidung'             => '',
            'Hidung - Sinus'                     => '',
            'Mulut - Bibir'                      => '',
            'Mulut - Gusi'                       => '',
            'Mulut - Gigi'                       => '',
            'Mulut - Caries'                     => '',
            'Mulut - Lidah'                      => '',
            'Mulut - Faring'                     => '',
            'Mulut - Tonsil'                     => '',
            'Leher - Kelenjar Limfe'             => '',
            'Leher - Kelenjar Gondok'            => '',
            'Paru - Gerakan Dada'                => '',
            'Paru - Vocal Fremitus'              => '',
            'Paru - Perkusi'                     => '',
            'Paru - Bunyi Nafas'                 => '',
            'Paru - Bunyi Tambahan'              => '',
            'Jantung - Ictus Cordis'             => '',
            'Jantung - Bunyi Jantung'            => '',
            'Jantung - Batas'                    => '',
            'Mammae'                             => '',
            'Abdomen - Inspeksi'                 => '',
            'Abdomen - Palpasi'                  => '',
            'Abdomen - Hepar'                    => '',
            'Abdomen - Perkusi'                  => '',
            'Abdomen - Auskultasi'               => '',
            'Abdomen - Limpa'                    => '',
            'Punggung - Nyeri ketok CVA'         => '',
            'Punggung - Scoliosis'               => '',
            'Kulit - Kondisi'                    => '',
            'Kulit - Penyakit'                   => '',
            'Anggota Gerak - Ekstremitas Atas'   => '',
            'Anggota Gerak - Ekstremitas Bawah'  => '',
            'Genitalia'                          => '',
            'Anus & Perianal'                    => '',
            'Radiologi'                          => '',
            'EKG'                                => '',
            'Spirometri'                         => '',
            'Audiometri'                         => '',
            'Treadmill'                          => '',
            'Romberg Test'                       => '',
            'Back Strength'                      => '',
            'Merokok'                            => '',
            'Alkohol'                            => '',
            'Lain-lain'                          => '',
            'Kesimpulan'                         => '',
            'Anjuran'                            => '',
            'Tindakan'                           => 'Satuan',
        ];

        foreach ($this->uniquePemeriksaan as $pemeriksaan) {
            $rowSatuan[$pemeriksaan] = $this->pemeriksaan[$this->dataPasienPoliMCU[0]->no_rawat][$pemeriksaan]->satuan ?? '-';
        }

        $data[] = $rowSatuan;

        foreach (['ld', 'la', 'pd', 'pa'] as $type) {
            $rowRujukan = [
                'Penjamin'                           => '',
                'No. Rawat'                          => '',
                'No. RM'                             => '',
                'Nama'                               => '',
                'Tgl. Lahir'                         => '',
                'Usia'                               => '',
                'Jenis Kelamin'                      => '',
                'Agama'                              => '',
                'NIK'                                => '',
                'Tgl. MCU'                           => '',
                'Poli'                               => '',
                'Riwayat Penyakit Sekarang'          => '',
                'Riwayat Penyakit Keluarga'          => '',
                'Riwayat Penyakit Dahulu'            => '',
                'Keadaan Umum'                       => '',
                'Kesadaran'                          => '',
                'TD'                                 => '',
                'HR/Nadi'                            => '',
                'RR/Nafas'                           => '',
                'Tinggi Badan (cm)'                  => '',
                'Berat Badan (kg)'                   => '',
                'Suhu (C)'                           => '',
                'BMI (BB/TB^2)'                      => '',
                'Klasifikasi BMI'                    => '',
                'Lingkar Pinggang'                   => '',
                'Risiko Berdasarkan LP'              => '',
                'Kelenjar Limfe - Submandibula'      => '',
                'Kelenjar Limfe - Axilla'            => '',
                'Kelenjar Limfe - Supraklavikula'    => '',
                'Kelenjar Limfe - Leher'             => '',
                'Kelenjar Limfe - Inguinal'          => '',
                'Muka - Oedema'                      => '',
                'Muka - Nyeri Tekan Sinus Frontalis' => '',
                'Muka - Nyeri Tekan Sinus Maxilaris' => '',
                'Rambut'                             => '',
                'Mata - Palpebra'                    => '',
                'Mata - Sklera'                      => '',
                'Mata - Kornea'                      => '',
                'Mata - Buta Warna'                  => '',
                'Mata - Konjungtiva'                 => '',
                'Mata - Lensa'                       => '',
                'Mata - Pupil'                       => '',
                'Mata - Kacamata'                    => '',
                'Mata - Visus'                       => '',
                'Mata - Luas Lapang Pandang'         => '',
                'Telinga - Lubang'                   => '',
                'Telinga - Daun'                     => '',
                'Telinga - Selaput Dengar'           => '',
                'Telinga - Proc. Mastoideus'         => '',
                'Hidung - Septum Nasi'               => '',
                'Hidung - Lubang Hidung'             => '',
                'Hidung - Sinus'                     => '',
                'Mulut - Bibir'                      => '',
                'Mulut - Gusi'                       => '',
                'Mulut - Gigi'                       => '',
                'Mulut - Caries'                     => '',
                'Mulut - Lidah'                      => '',
                'Mulut - Faring'                     => '',
                'Mulut - Tonsil'                     => '',
                'Leher - Kelenjar Limfe'             => '',
                'Leher - Kelenjar Gondok'            => '',
                'Paru - Gerakan Dada'                => '',
                'Paru - Vocal Fremitus'              => '',
                'Paru - Perkusi'                     => '',
                'Paru - Bunyi Nafas'                 => '',
                'Paru - Bunyi Tambahan'              => '',
                'Jantung - Ictus Cordis'             => '',
                'Jantung - Bunyi Jantung'            => '',
                'Jantung - Batas'                    => '',
                'Mammae'                             => '',
                'Abdomen - Inspeksi'                 => '',
                'Abdomen - Palpasi'                  => '',
                'Abdomen - Hepar'                    => '',
                'Abdomen - Perkusi'                  => '',
                'Abdomen - Auskultasi'               => '',
                'Abdomen - Limpa'                    => '',
                'Punggung - Nyeri ketok CVA'         => '',
                'Punggung - Scoliosis'               => '',
                'Kulit - Kondisi'                    => '',
                'Kulit - Penyakit'                   => '',
                'Anggota Gerak - Ekstremitas Atas'   => '',
                'Anggota Gerak - Ekstremitas Bawah'  => '',
                'Genitalia'                          => '',
                'Anus & Perianal'                    => '',
                'Radiologi'                          => '',
                'EKG'                                => '',
                'Spirometri'                         => '',
                'Audiometri'                         => '',
                'Treadmill'                          => '',
                'Romberg Test'                       => '',
                'Back Strength'                      => '',
                'Merokok'                            => '',
                'Alkohol'                            => '',
                'Lain-lain'                          => '',
                'Kesimpulan'                         => '',
                'Anjuran'                            => '',
                'Tindakan'                           => 'Nilai Rujukan ('.strtoupper($type).')',
            ];

            foreach ($this->uniquePemeriksaan as $pemeriksaan) {
                $rowRujukan[$pemeriksaan] = $this->pemeriksaan[$this->dataPasienPoliMCU[0]->no_rawat][$pemeriksaan]->{'nilai_rujukan_'.$type} ?? '-';
            }

            $data[] = $rowRujukan;
        }

        foreach ($this->dataPasienPoliMCU as $registrasi) {
            $row = [];

            $row['Penjamin']                           = $registrasi->png_jawab;
            $row['No. Rawat']                          = $registrasi->no_rawat;
            $row['No. RM']                             = $registrasi->no_rkm_medis;
            $row['Nama']                               = $registrasi->nm_pasien;
            $row['Tgl. Lahir']                         = $registrasi->tgl_lahir;
            $row['Usia']                               = $registrasi->umurdaftar.' '.$registrasi->sttsumur;
            $row['Jenis Kelamin']                      = $registrasi->jk;
            $row['Agama']                              = $registrasi->agama;
            $row['NIK']                                = $registrasi->no_ktp;
            $row['Tgl. MCU']                           = $registrasi->tgl_registrasi;
            $row['Poli']                               = $registrasi->nm_poli;
            $row['Riwayat Penyakit Sekarang']          = optional($registrasi->penilaianHasilMcu)->rps;
            $row['Riwayat Penyakit Keluarga']          = optional($registrasi->penilaianHasilMcu)->rpk;
            $row['Riwayat Penyakit Dahulu']            = optional($registrasi->penilaianHasilMcu)->rpd;
            $row['Keadaan Umum']                       = optional($registrasi->penilaianHasilMcu)->keadaan;
            $row['Kesadaran']                          = optional($registrasi->penilaianHasilMcu)->kesadaran;
            $row['TD']                                 = optional($registrasi->penilaianHasilMcu)->td;
            $row['HR/Nadi']                            = optional($registrasi->penilaianHasilMcu)->nadi;
            $row['RR/Nafas']                           = optional($registrasi->penilaianHasilMcu)->rr;
            $row['Tinggi Badan (cm)']                  = optional($registrasi->penilaianHasilMcu)->tb;
            $row['Berat Badan (kg)']                   = optional($registrasi->penilaianHasilMcu)->bb;
            $row['Suhu (C)']                           = optional($registrasi->penilaianHasilMcu)->suhu;
            $row['BMI (BB/TB^2)']                      = optional($registrasi->penilaianHasilMcu)->bmi;
            $row['Klasifikasi BMI']                    = optional($registrasi->penilaianHasilMcu)->kasifikasi_bmi;
            $row['Lingkar Pinggang']                   = optional($registrasi->penilaianHasilMcu)->lingkar_pinggang;
            $row['Risiko Berdasarkan LP']              = optional($registrasi->penilaianHasilMcu)->risiko_lingkar_pinggang;
            $row['Kelenjar Limfe - Submandibula']      = optional($registrasi->penilaianHasilMcu)->submandibula;
            $row['Kelenjar Limfe - Axilla']            = optional($registrasi->penilaianHasilMcu)->axilla;
            $row['Kelenjar Limfe - Supraklavikula']    = optional($registrasi->penilaianHasilMcu)->supraklavikula;
            $row['Kelenjar Limfe - Leher']             = optional($registrasi->penilaianHasilMcu)->leher;
            $row['Kelenjar Limfe - Inguinal']          = optional($registrasi->penilaianHasilMcu)->inguinal;
            $row['Muka - Oedema']                      = optional($registrasi->penilaianHasilMcu)->oedema;
            $row['Muka - Nyeri Tekan Sinus Frontalis'] = optional($registrasi->penilaianHasilMcu)->sinus_frontalis;
            $row['Muka - Nyeri Tekan Sinus Maxilaris'] = optional($registrasi->penilaianHasilMcu)->sinus_maxilaris;
            $row['Rambut']                             = optional($registrasi->penilaianHasilMcu)->rambut;
            $row['Mata - Palpebra']                    = optional($registrasi->penilaianHasilMcu)->palpebra;
            $row['Mata - Sklera']                      = optional($registrasi->penilaianHasilMcu)->sklera;
            $row['Mata - Kornea']                      = optional($registrasi->penilaianHasilMcu)->cornea;
            $row['Mata - Buta Warna']                  = optional($registrasi->penilaianHasilMcu)->buta_warna;
            $row['Mata - Konjungtiva']                 = optional($registrasi->penilaianHasilMcu)->konjungtiva;
            $row['Mata - Lensa']                       = optional($registrasi->penilaianHasilMcu)->lensa;
            $row['Mata - Pupil']                       = optional($registrasi->penilaianHasilMcu)->pupil;
            $row['Mata - Kacamata']                    = optional($registrasi->penilaianHasilMcu)->menggunakan_kacamata;
            $row['Mata - Visus']                       = optional($registrasi->penilaianHasilMcu)->visus;
            $row['Mata - Luas Lapang Pandang']         = optional($registrasi->penilaianHasilMcu)->luas_lapang_pandang.(empty(optional($registrasi->penilaianHasilMcu)->keterangan_luas_lapang_pandang) ? '' : ', '.optional($registrasi->penilaianHasilMcu)->keterangan_luas_lapang_pandang);
            $row['Telinga - Lubang']                   = optional($registrasi->penilaianHasilMcu)->lubang_telinga;
            $row['Telinga - Daun']                     = optional($registrasi->penilaianHasilMcu)->daun_telinga;
            $row['Telinga - Selaput Dengar']           = optional($registrasi->penilaianHasilMcu)->selaput_pendengaran;
            $row['Telinga - Proc. Mastoideus']         = optional($registrasi->penilaianHasilMcu)->proc_mastoideus;
            $row['Hidung - Septum Nasi']               = optional($registrasi->penilaianHasilMcu)->septum_nasi;
            $row['Hidung - Lubang Hidung']             = optional($registrasi->penilaianHasilMcu)->lubang_hidung;
            $row['Hidung - Sinus']                     = optional($registrasi->penilaianHasilMcu)->sinus;
            $row['Mulut - Bibir']                      = optional($registrasi->penilaianHasilMcu)->bibir;
            $row['Mulut - Gusi']                       = optional($registrasi->penilaianHasilMcu)->gusi;
            $row['Mulut - Gigi']                       = optional($registrasi->penilaianHasilMcu)->gigi;
            $row['Mulut - Caries']                     = optional($registrasi->penilaianHasilMcu)->caries;
            $row['Mulut - Lidah']                      = optional($registrasi->penilaianHasilMcu)->lidah;
            $row['Mulut - Faring']                     = optional($registrasi->penilaianHasilMcu)->faring;
            $row['Mulut - Tonsil']                     = optional($registrasi->penilaianHasilMcu)->tonsil;
            $row['Leher - Kelenjar Limfe']             = optional($registrasi->penilaianHasilMcu)->kelenjar_limfe;
            $row['Leher - Kelenjar Gondok']            = optional($registrasi->penilaianHasilMcu)->kelenjar_gondok;
            $row['Paru - Gerakan Dada']                = optional($registrasi->penilaianHasilMcu)->geakan_dada;
            $row['Paru - Vocal Fremitus']              = optional($registrasi->penilaianHasilMcu)->vocal_femitus;
            $row['Paru - Perkusi']                     = optional($registrasi->penilaianHasilMcu)->perkusi_dada;
            $row['Paru - Bunyi Nafas']                 = optional($registrasi->penilaianHasilMcu)->bunyi_napas;
            $row['Paru - Bunyi Tambahan']              = optional($registrasi->penilaianHasilMcu)->bunyi_tambahan;
            $row['Jantung - Ictus Cordis']             = optional($registrasi->penilaianHasilMcu)->ictus_cordis;
            $row['Jantung - Bunyi Jantung']            = optional($registrasi->penilaianHasilMcu)->bunyi_jantung;
            $row['Jantung - Batas']                    = optional($registrasi->penilaianHasilMcu)->batas;
            $row['Mammae']                             = optional($registrasi->penilaianHasilMcu)->mamae.(empty(optional($registrasi->penilaianHasilMcu)->keterangan_mamae) ? '' : ', '.optional($registrasi->penilaianHasilMcu)->keterangan_mamae);
            $row['Abdomen - Inspeksi']                 = optional($registrasi->penilaianHasilMcu)->inspeksi;
            $row['Abdomen - Palpasi']                  = optional($registrasi->penilaianHasilMcu)->palpasi;
            $row['Abdomen - Hepar']                    = optional($registrasi->penilaianHasilMcu)->hepar;
            $row['Abdomen - Perkusi']                  = optional($registrasi->penilaianHasilMcu)->perkusi;
            $row['Abdomen - Auskultasi']               = optional($registrasi->penilaianHasilMcu)->auskultasi;
            $row['Abdomen - Limpa']                    = optional($registrasi->penilaianHasilMcu)->limpa;
            $row['Punggung - Nyeri ketok CVA']         = optional($registrasi->penilaianHasilMcu)->costovertebral;
            $row['Punggung - Scoliosis']               = optional($registrasi->penilaianHasilMcu)->scoliosis;
            $row['Kulit - Kondisi']                    = optional($registrasi->penilaianHasilMcu)->kondisi_kulit;
            $row['Kulit - Penyakit']                   = optional($registrasi->penilaianHasilMcu)->penyakit_kulit;
            $row['Anggota Gerak - Ekstremitas Atas']   = optional($registrasi->penilaianHasilMcu)->ekstrimitas_atas.(empty(optional($registrasi->penilaianHasilMcu)->ekstrimitas_atas_ket) ? '' : ', '.optional($registrasi->penilaianHasilMcu)->ekstrimitas_atas_ket);
            $row['Anggota Gerak - Ekstremitas Bawah']  = optional($registrasi->penilaianHasilMcu)->ekstrimitas_bawah.(empty(optional($registrasi->penilaianHasilMcu)->ekstrimitas_bawah_ket) ? '' : ', '.optional($registrasi->penilaianHasilMcu)->ekstrimitas_bawah_ket);
            $row['Genitalia']                          = optional($registrasi->penilaianHasilMcu)->area_genitalia.(empty(optional($registrasi->penilaianHasilMcu)->keterangan_area_genitalia) ? '' : ', '.optional($registrasi->penilaianHasilMcu)->keterangan_area_genitalia);
            $row['Anus & Perianal']                    = optional($registrasi->penilaianHasilMcu)->anus_perianal.(empty(optional($registrasi->penilaianHasilMcu)->keterangan_anus_perianal) ? '' : ', '.optional($registrasi->penilaianHasilMcu)->keterangan_anus_perianal);
            $row['Radiologi']                          = optional($registrasi->penilaianHasilMcu)->radiologi;
            $row['EKG']                                = optional($registrasi->penilaianHasilMcu)->ekg;
            $row['Spirometri']                         = optional($registrasi->penilaianHasilMcu)->spirometri;
            $row['Audiometri']                         = optional($registrasi->penilaianHasilMcu)->audiometri;
            $row['Treadmill']                          = optional($registrasi->penilaianHasilMcu)->treadmill;
            $row['Romberg Test']                       = optional($registrasi->penilaianHasilMcu)->romberg_test;
            $row['Back Strength']                      = optional($registrasi->penilaianHasilMcu)->back_strength;
            $row['Merokok']                            = optional($registrasi->penilaianHasilMcu)->merokok;
            $row['Alkohol']                            = optional($registrasi->penilaianHasilMcu)->alkohol;
            $row['Lain-lain']                          = optional($registrasi->penilaianHasilMcu)->lainlain;
            $row['Kesimpulan']                         = optional($registrasi->penilaianHasilMcu)->kesimpulan;
            $row['Anjuran']                            = optional($registrasi->penilaianHasilMcu)->anjuran;
            $row['Tindakan']                           = '';

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
            'Riwayat Penyakit Sekarang',
            'Riwayat Penyakit Keluarga',
            'Riwayat Penyakit Dahulu',
            'Keadaan Umum',
            'Kesadaran',
            'TD',
            'HR/Nadi',
            'RR/Nafas',
            'Tinggi Badan (cm)',
            'Berat Badan (kg)',
            'Suhu (C)',
            'BMI (BB/TB^2)',
            'Klasifikasi BMI',
            'Lingkar Pinggang',
            'Risiko Berdasarkan LP',
            'Kelenjar Limfe - Submandibula',
            'Kelenjar Limfe - Axilla',
            'Kelenjar Limfe - Supraklavikula',
            'Kelenjar Limfe - Leher',
            'Kelenjar Limfe - Inguinal',
            'Muka - Oedema',
            'Muka - Nyeri Tekan Sinus Frontalis',
            'Muka - Nyeri Tekan Sinus Maxilaris',
            'Rambut',
            'Mata - Palpebra',
            'Mata - Sklera',
            'Mata - Kornea',
            'Mata - Buta Warna',
            'Mata - Konjungtiva',
            'Mata - Lensa',
            'Mata - Pupil',
            'Mata - Kacamata',
            'Mata - Visus',
            'Mata - Luas Lapang Pandang',
            'Telinga - Lubang',
            'Telinga - Daun',
            'Telinga - Selaput Dengar',
            'Telinga - Proc. Mastoideus',
            'Hidung - Septum Nasi',
            'Hidung - Lubang Hidung',
            'Hidung - Sinus',
            'Mulut - Bibir',
            'Mulut - Gusi',
            'Mulut - Gigi',
            'Mulut - Caries',
            'Mulut - Lidah',
            'Mulut - Faring',
            'Mulut - Tonsil',
            'Leher - Kelenjar Limfe',
            'Leher - Kelenjar Gondok',
            'Paru - Gerakan Dada',
            'Paru - Vocal Fremitus',
            'Paru - Perkusi',
            'Paru - Bunyi Nafas',
            'Paru - Bunyi Tambahan',
            'Jantung - Ictus Cordis',
            'Jantung - Bunyi Jantung',
            'Jantung - Batas',
            'Mammae',
            'Abdomen - Inspeksi',
            'Abdomen - Palpasi',
            'Abdomen - Hepar',
            'Abdomen - Perkusi',
            'Abdomen - Auskultasi',
            'Abdomen - Limpa',
            'Punggung - Nyeri ketok CVA',
            'Punggung - Scoliosis',
            'Kulit - Kondisi',
            'Kulit - Penyakit',
            'Anggota Gerak - Ekstremitas Atas',
            'Anggota Gerak - Ekstremitas Bawah',
            'Genitalia',
            'Anus & Perianal',
            'Radiologi',
            'EKG',
            'Spirometri',
            'Audiometri',
            'Treadmill',
            'Romberg Test',
            'Back Strength',
            'Merokok',
            'Alkohol',
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
            'Laporan Hasil Pemeriksaan '.$this->dataPenjamin->get($this->penjamin),
            now()->translatedFormat('d F Y'),
            $periode,
        ];

    }
}
