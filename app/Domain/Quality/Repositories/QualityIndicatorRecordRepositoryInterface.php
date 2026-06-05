<?php

namespace App\Domain\Quality\Repositories;

use Illuminate\Support\Collection;

interface QualityIndicatorRecordRepositoryInterface
{
    public function findByIndicatorAndDate(int $indicatorId, string $date): ?object;

    public function getByIndicatorInRange(int $indicatorId, string $startDate, string $endDate): Collection;

    public function save(array $data): object;

    public function delete(int $indicatorId, string $date): bool;
}
