<?php

namespace App\Application\Quality\Actions;

use App\Domain\Quality\Repositories\QualityCategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetQualityCategoryListAction
{
    /** @var QualityCategoryRepositoryInterface */
    protected $repository;

    public function __construct(QualityCategoryRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getPaginated($filters, $perPage);
    }
}
