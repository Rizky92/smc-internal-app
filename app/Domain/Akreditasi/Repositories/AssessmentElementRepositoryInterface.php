<?php

namespace App\Domain\Akreditasi\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AssessmentElementRepositoryInterface
{
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function getByStandard(int $standardId): array;

    public function findById(int $id): ?object;

    public function save(array $data): object;

    public function delete(int $id): bool;
}
