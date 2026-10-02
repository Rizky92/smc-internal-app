<?php

namespace App\Domain\Akreditasi\Repositories;

interface AssessmentScoreRepositoryInterface
{
    public function findByElement(int $assessmentElementId): ?object;

    public function save(array $data): object;

    public function delete(int $id): bool;
}
