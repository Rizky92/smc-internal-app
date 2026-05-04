<?php

namespace App\Infrastructure\Quality\Repositories;

use App\Domain\Quality\Repositories\QualityIndicatorRecordRepositoryInterface;
use App\Models\Quality\QualityIndicatorRecord;
use Illuminate\Support\Collection;

class EloquentQualityIndicatorRecordRepository implements QualityIndicatorRecordRepositoryInterface
{
    public function findByIndicatorAndDate(int $indicatorId, string $date): ?QualityIndicatorRecord
    {
        return QualityIndicatorRecord::where('indicator_id', $indicatorId)
            ->where('recorded_date', $date)
            ->first();
    }

    public function getByIndicatorInRange(int $indicatorId, string $startDate, string $endDate): Collection
    {
        return QualityIndicatorRecord::where('indicator_id', $indicatorId)
            ->whereBetween('recorded_date', [$startDate, $endDate])
            ->get();
    }

    public function save(array $data): QualityIndicatorRecord
    {
        return QualityIndicatorRecord::updateOrCreate(
            [
                'indicator_id'  => $data['indicator_id'],
                'recorded_date' => $data['recorded_date'],
            ],
            $data
        );
    }
}
