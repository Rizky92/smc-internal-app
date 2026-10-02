<?php

namespace App\Domain\Akreditasi\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface FocusAreaRepositoryInterface
{
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function getAllWithStats(): array;

    public function save(array $data): object;

    public function delete(int $id): bool;
}
