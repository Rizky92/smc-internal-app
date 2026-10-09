<?php

namespace Tests\Unit;

use App\Models\Quality\QualityIndicatorProfile;
use PHPUnit\Framework\TestCase;

class QualityIndicatorProfileAchievementTest extends TestCase
{
    /**
     * @return array<string, array{string|null, float|null, float|null, string}>
     */
    public static function statuses(): array
    {
        return [
            '≥, di atas target'             => ['gte', 85.0, 90.0, 'tercapai'],
            '≥, sama dengan target'         => ['gte', 85.0, 85.0, 'tercapai'],
            '≥, di bawah target'            => ['gte', 85.0, 84.99, 'tidak_tercapai'],
            '≥, pembulatan 2 desimal'       => ['gte', 85.0, 84.999, 'tercapai'],
            '≤, di bawah target'            => ['lte', 5.0, 3.0, 'tercapai'],
            '≤, sama dengan target'         => ['lte', 5.0, 5.0, 'tercapai'],
            '≤, di atas target'             => ['lte', 5.0, 5.01, 'tidak_tercapai'],
            '≤, nol'                        => ['lte', 5.0, 0.0, 'tercapai'],
            'operator null, di atas nilai'  => [null, 85.0, 90.0, 'belum_dinilai'],
            'operator null, di bawah nilai' => [null, 85.0, 10.0, 'belum_dinilai'],
            'nilai target null'             => ['gte', null, 90.0, 'belum_dinilai'],
            'target kosong semua'           => [null, null, 90.0, 'belum_dinilai'],
            '≥, capaian null'               => ['gte', 85.0, null, 'belum_dinilai'],
            '≤, capaian null'               => ['lte', 5.0, null, 'belum_dinilai'],
        ];
    }

    /**
     * @dataProvider statuses
     */
    public function test_achievement_status(?string $operator, ?float $target, ?float $achievement, string $expected): void
    {
        $profile = new QualityIndicatorProfile(['target_operator' => $operator, 'target_value' => $target]);

        $this->assertSame($expected, $profile->achievementStatus($achievement));
    }

    /**
     * @return array<string, array{string|null, float|null, string|null}>
     */
    public static function labels(): array
    {
        return [
            '≥'                => ['gte', 85.0, 'Target ≥ 85%'],
            '≤ dengan desimal' => ['lte', 2.5, 'Target ≤ 2.5%'],
            'operator null'    => [null, 85.0, null],
            'nilai null'       => ['gte', null, null],
        ];
    }

    /**
     * @dataProvider labels
     */
    public function test_target_label(?string $operator, ?float $target, ?string $expected): void
    {
        $profile = new QualityIndicatorProfile(['target_operator' => $operator, 'target_value' => $target]);

        $this->assertSame($expected, $profile->targetLabel());
    }
}
