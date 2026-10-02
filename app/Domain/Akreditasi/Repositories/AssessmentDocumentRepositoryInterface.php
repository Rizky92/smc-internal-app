<?php

namespace App\Domain\Akreditasi\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AssessmentDocumentRepositoryInterface
{
    public function getByElement(int $assessmentElementId): LengthAwarePaginator;

    public function countByElement(int $assessmentElementId): int;

    public function findById(int $id): ?object;

    public function save(array $data): object;

    public function delete(int $id): bool;

    public function destroyFile(int $id): void;
}
