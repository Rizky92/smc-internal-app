<?php

namespace App\Domain\Quality\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface QualityIndicatorRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * @param  array<string, mixed>  $filters
     */
    public function getAll(array $filters = []): Collection;

    public function findById(int $id): ?object;

    public function save(array $data): object;

    public function delete(int $id): bool;
}
