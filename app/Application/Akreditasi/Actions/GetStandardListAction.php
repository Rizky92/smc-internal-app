<?php

namespace App\Application\Akreditasi\Actions;

use App\Domain\Akreditasi\Repositories\StandardRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetStandardListAction
{
    protected $repository;

    public function __construct(StandardRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getPaginated($filters, $perPage);
    }
}
