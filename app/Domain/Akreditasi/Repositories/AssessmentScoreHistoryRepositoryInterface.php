<?php

namespace App\Domain\Akreditasi\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AssessmentScoreHistoryRepositoryInterface
{
    public function getByElement(int $assessmentElementId): LengthAwarePaginator;

    public function save(array $data): object;
}
