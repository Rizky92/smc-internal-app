<?php

namespace App\Domain\Quality\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface QualityInputTypeRepositoryInterface
{
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function save(array $data): object;

    public function delete(int $id): bool;
}
