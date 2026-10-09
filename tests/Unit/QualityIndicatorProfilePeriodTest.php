<?php

namespace Tests\Unit;

use App\Models\Quality\QualityIndicatorProfile;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class QualityIndicatorProfilePeriodTest extends TestCase
{
    /**
     * @return array<string, array{int|null, string, string, string, string}>
     */
    public static function periods(): array
    {
        return [
            'bulanan, tengah bulan'           => [1, '2026-03-15', 'monthly', '2026-03-01', '2026-03-31'],
            'bulanan, Februari kabisat'       => [1, '2028-02-10', 'monthly', '2028-02-01', '2028-02-29'],
            'bulanan, hari terakhir tahun'    => [1, '2026-12-31', 'monthly', '2026-12-01', '2026-12-31'],
            'triwulan I, hari pertama'        => [3, '2026-01-01', 'quarterly', '2026-01-01', '2026-03-31'],
            'triwulan I, hari terakhir'       => [3, '2026-03-31', 'quarterly', '2026-01-01', '2026-03-31'],
            'triwulan II, hari pertama'       => [3, '2026-04-01', 'quarterly', '2026-04-01', '2026-06-30'],
            'triwulan IV'                     => [3, '2026-11-20', 'quarterly', '2026-10-01', '2026-12-31'],
            'semester I, akhir Juni'          => [6, '2026-06-30', 'semester', '2026-01-01', '2026-06-30'],
            'semester II, awal Juli'          => [6, '2026-07-01', 'semester', '2026-07-01', '2026-12-31'],
            'tahunan'                         => [12, '2026-08-17', 'yearly', '2026-01-01', '2026-12-31'],
            'kosong diperlakukan bulanan'     => [null, '2026-05-09', 'monthly', '2026-05-01', '2026-05-31'],
            'nilai lama tidak baku = bulanan' => [2, '2026-05-09', 'monthly', '2026-05-01', '2026-05-31'],
        ];
    }

    /**
     * @dataProvider periods
     */
    public function test_period_for(?int $analysisPeriod, string $date, string $type, string $start, string $end): void
    {
        $profile = new QualityIndicatorProfile(['analysis_period' => $analysisPeriod]);

        $period = $profile->periodFor(Carbon::parse($date.' 13:45:00'));

        $this->assertSame($type, $period['type']);
        $this->assertSame($start, $period['start']->toDateString());
        $this->assertSame($end, $period['end']->toDateString());
    }

    public function test_daftar_periode_baku(): void
    {
        $this->assertSame([1, 3, 6, 12], array_keys(QualityIndicatorProfile::ANALYSIS_PERIODS));
    }
}
