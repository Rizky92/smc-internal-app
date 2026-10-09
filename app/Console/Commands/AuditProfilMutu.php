<?php

namespace App\Console\Commands;

use App\Models\Aplikasi\User;
use App\Models\Kepegawaian\Departemen;
use App\Models\Quality\QualityIndicator;
use App\Models\Quality\QualityIndicatorCategory;
use App\Models\Quality\QualityIndicatorProfile;
use App\Support\Mutu\StandardParser;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Laporan read-only data master Indikator Mutu untuk ditinjau bersama Komite Mutu
 * sebelum migrasi analisis agregat (lihat .scratch/analisis-agregat-indikator-mutu/spec.md).
 */
class AuditProfilMutu extends Command
{
    private const PERIODE_BAKU = [1, 3, 6, 12];

    /** @var string */
    protected $signature = 'mutu:audit-profil';

    /** @var string */
    protected $description = 'Laporkan data master Indikator Mutu yang perlu ditinjau (tidak mengubah data)';

    /** @var Collection<string, string>|null */
    private $namaDepartemen;

    public function handle(): int
    {
        $profiles = QualityIndicatorProfile::query()
            ->with('assignments')
            ->orderBy('id')
            ->get();

        $this->distribusiPeriode($profiles);
        $this->periodeTidakBaku($profiles);
        $this->standarGagalDiparse($profiles);
        $this->profilTanpaTarget($profiles);
        $this->kategoriBesertaIndikator($profiles);
        $this->indikatorTanpaPenanggungJawab();
        $this->indikatorTanpaPic();
        $this->indikatorDenganPicTidakAktif();
        $this->indikatorTanpaReviewer();

        return Command::SUCCESS;
    }

    /**
     * @param  Collection<int, QualityIndicatorProfile>  $profiles
     */
    private function distribusiPeriode(Collection $profiles): void
    {
        $this->judul('Distribusi periode analisis');

        $this->tabel(
            ['Periode analisis (bulan)', 'Jumlah profil'],
            $profiles
                ->countBy(fn (QualityIndicatorProfile $profile): string => (string) ($profile->analysis_period ?? '(kosong)'))
                ->sortKeys()
                ->map(fn (int $jumlah, string $periode): array => [$periode, $jumlah])
                ->values()
        );
    }

    /**
     * @param  Collection<int, QualityIndicatorProfile>  $profiles
     */
    private function periodeTidakBaku(Collection $profiles): void
    {
        $this->judul('Periode analisis di luar 1/3/6/12');

        $this->tabel(
            ['ID profil', 'Judul profil', 'Periode analisis'],
            $profiles
                ->reject(fn (QualityIndicatorProfile $profile): bool => $profile->analysis_period !== null
                    && in_array((int) $profile->analysis_period, self::PERIODE_BAKU, true))
                ->map(fn (QualityIndicatorProfile $profile): array => [
                    $profile->id,
                    $this->singkat($profile->title),
                    $profile->analysis_period ?? '(kosong)',
                ])
        );
    }

    /**
     * Teks `standard` yang tidak menghasilkan angka, sehingga `target_value` tidak terisi oleh backfill.
     *
     * @param  Collection<int, QualityIndicatorProfile>  $profiles
     */
    private function standarGagalDiparse(Collection $profiles): void
    {
        $this->judul('Standar yang gagal diparse');

        $this->tabel(
            ['ID profil', 'Judul profil', 'Standar (teks asli)'],
            $profiles
                ->filter(fn (QualityIndicatorProfile $profile): bool => StandardParser::parse($profile->standard) === null)
                ->map(fn (QualityIndicatorProfile $profile): array => [
                    $profile->id,
                    $this->singkat($profile->title),
                    $profile->standard ?? '(kosong)',
                ])
        );
    }

    /**
     * Tanpa operator atau nilai target, capaian indikator belum bisa dinilai.
     *
     * @param  Collection<int, QualityIndicatorProfile>  $profiles
     */
    private function profilTanpaTarget(Collection $profiles): void
    {
        foreach (['target_operator' => 'Profil tanpa operator target', 'target_value' => 'Profil tanpa nilai target'] as $kolom => $judul) {
            $this->judul($judul);

            if (! $this->kolomAda('quality_indicator_profiles', $kolom)) {
                $this->line("kolom {$kolom} belum ada (migrasi belum dijalankan)");

                continue;
            }

            $this->tabel(
                ['ID profil', 'Judul profil', 'Standar (teks asli)'],
                $profiles
                    ->filter(fn (QualityIndicatorProfile $profile): bool => blank($profile->getAttribute($kolom)))
                    ->map(fn (QualityIndicatorProfile $profile): array => [
                        $profile->id,
                        $this->singkat($profile->title),
                        $profile->standard ?? '(kosong)',
                    ])
            );
        }
    }

    /**
     * Kategori yang mencampur indikator INM dan non-INM menentukan letak kolom kelompok indikator.
     *
     * @param  Collection<int, QualityIndicatorProfile>  $profiles
     */
    private function kategoriBesertaIndikator(Collection $profiles): void
    {
        $this->judul('Kategori beserta indikatornya');

        $categories = QualityIndicatorCategory::query()->orderBy('id')->get();

        if ($categories->isEmpty()) {
            $this->line('tidak ada');

            return;
        }

        $profilPerKategori = $profiles->groupBy('quality_indicator_category_id');

        foreach ($categories as $category) {
            $this->line("Kategori: {$category->name} (#{$category->id})");

            $rows = $profilPerKategori
                ->get($category->id, collect())
                ->flatMap(fn (QualityIndicatorProfile $profile): array => $profile->assignments->isEmpty()
                    ? [[$profile->id, $this->singkat($profile->title), '-', '-', '-']]
                    : $profile->assignments
                        ->map(fn (QualityIndicator $indicator): array => [
                            $profile->id,
                            $this->singkat($profile->title),
                            $indicator->id,
                            $this->departemen($indicator->dep_id),
                            $indicator->status,
                        ])
                        ->all());

            $this->tabel(['ID profil', 'Judul profil', 'ID indikator', 'Unit', 'Status'], $rows, 'tidak ada profil');
        }
    }

    private function indikatorTanpaPenanggungJawab(): void
    {
        $this->judul('Indikator tanpa penanggung jawab');

        $this->tabel(
            ['ID indikator', 'Judul profil', 'Unit', 'Status'],
            QualityIndicator::query()
                ->with('profile')
                ->where(fn ($q) => $q->whereNull('person_in_charge')->orWhere('person_in_charge', ''))
                ->orderBy('id')
                ->get()
                ->map(fn (QualityIndicator $indicator): array => [
                    $indicator->id,
                    $this->singkat(optional($indicator->profile)->title),
                    $this->departemen($indicator->dep_id),
                    $indicator->status,
                ])
        );
    }

    private function indikatorTanpaPic(): void
    {
        $this->judul('Indikator tanpa PIC (pic_nik)');

        if (! $this->kolomAda('quality_indicators', 'pic_nik')) {
            $this->line('kolom pic_nik belum ada (migrasi belum dijalankan)');

            return;
        }

        $this->tabel(
            ['ID indikator', 'Judul profil', 'Unit', 'Jabatan PJ', 'Status'],
            QualityIndicator::query()
                ->with('profile')
                ->where(fn ($q) => $q->whereNull('pic_nik')->orWhere('pic_nik', ''))
                ->orderBy('id')
                ->get()
                ->map(fn (QualityIndicator $indicator): array => [
                    $indicator->id,
                    $this->singkat(optional($indicator->profile)->title),
                    $this->departemen($indicator->dep_id),
                    $indicator->person_in_charge ?: '-',
                    $indicator->status,
                ])
        );
    }

    /**
     * PIC yang pegawainya tidak berstatus AKTIF (termasuk CUTI) atau NIK-nya tidak ditemukan.
     */
    private function indikatorDenganPicTidakAktif(): void
    {
        $this->judul('Indikator dengan PIC tidak aktif');

        if (! $this->kolomAda('quality_indicators', 'pic_nik')) {
            $this->line('kolom pic_nik belum ada (migrasi belum dijalankan)');

            return;
        }

        $this->tabel(
            ['ID indikator', 'Judul profil', 'Unit', 'NIK PIC', 'Nama PIC', 'Status pegawai'],
            QualityIndicator::query()
                ->with(['profile', 'pic'])
                ->where('pic_nik', '<>', '')
                ->orderBy('id')
                ->get()
                ->filter(fn (QualityIndicator $indicator): bool => optional($indicator->pic)->stts_aktif !== 'AKTIF')
                ->map(fn (QualityIndicator $indicator): array => [
                    $indicator->id,
                    $this->singkat(optional($indicator->profile)->title),
                    $this->departemen($indicator->dep_id),
                    $indicator->pic_nik,
                    optional($indicator->pic)->nama ?? '-',
                    optional($indicator->pic)->stts_aktif ?? 'tidak ditemukan',
                ])
        );
    }

    /**
     * Indikator aktif yang tidak bisa direview siapa pun karena satu-satunya pemegang izin review adalah PIC-nya
     * sendiri, atau belum ada pemegang izin sama sekali.
     */
    private function indikatorTanpaReviewer(): void
    {
        $this->judul('Indikator tanpa reviewer yang memenuhi syarat');

        $izin = 'mutu.review-analisis.approve';
        $pemegangIzin = User::nikPemegangIzin($izin);

        $this->tabel(
            ['ID indikator', 'Judul profil', 'Unit', 'NIK PIC', 'Keterangan'],
            QualityIndicator::query()
                ->with('profile')
                ->where('status', 'active')
                ->orderBy('id')
                ->get()
                ->filter(fn (QualityIndicator $indicator): bool => $indicator->reviewerMemenuhiSyarat($pemegangIzin)->isEmpty())
                ->map(fn (QualityIndicator $indicator): array => [
                    $indicator->id,
                    $this->singkat(optional($indicator->profile)->title),
                    $this->departemen($indicator->dep_id),
                    $indicator->pic_nik ?: '-',
                    $pemegangIzin->isEmpty() ? "tidak ada pemegang izin {$izin}" : 'hanya PIC yang memegang izin review',
                ])
        );
    }

    /**
     * Audit dijalankan di production sebelum migrasi fase 1, jadi bagian yang membaca kolom baru
     * harus tetap jalan ketika kolomnya belum ada.
     */
    private function kolomAda(string $table, string $column): bool
    {
        return Schema::connection('mysql_smc')->hasColumn($table, $column);
    }

    private function judul(string $judul): void
    {
        $this->newLine();
        $this->line("== {$judul} ==");
    }

    /**
     * @param  string[]  $headers
     * @param  Collection<int, array>  $rows
     */
    private function tabel(array $headers, Collection $rows, string $kosong = 'tidak ada'): void
    {
        if ($rows->isEmpty()) {
            $this->line($kosong);

            return;
        }

        $this->table($headers, $rows->values()->all());
    }

    private function departemen(?string $depId): string
    {
        if (blank($depId)) {
            return '-';
        }

        $this->namaDepartemen ??= Departemen::query()->pluck('nama', 'dep_id');

        $nama = $this->namaDepartemen->get($depId);

        return $nama ? "{$depId} - {$nama}" : $depId;
    }

    private function singkat(?string $teks): string
    {
        return Str::limit((string) $teks, 60);
    }
}
