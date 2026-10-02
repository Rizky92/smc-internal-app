<?php

namespace App\Application\Akreditasi\Actions;

use App\Domain\Akreditasi\Repositories\FocusAreaRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetFocusAreaListAction
{
    protected $repository;

    public function __construct(FocusAreaRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getPaginated($filters, $perPage);
    }
}
