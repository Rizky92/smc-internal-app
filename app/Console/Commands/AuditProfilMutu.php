<?php

namespace App\Console\Commands;

use App\Models\Kepegawaian\Departemen;
use App\Models\Quality\QualityIndicator;
use App\Models\Quality\QualityIndicatorCategory;
use App\Models\Quality\QualityIndicatorProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
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
        $this->kategoriBesertaIndikator($profiles);
        $this->indikatorTanpaPenanggungJawab();

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
