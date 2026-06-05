<?php

namespace App\Application\Quality\Actions;

use App\Domain\Quality\Repositories\QualityIndicatorProfileRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetQualityIndicatorProfileListAction
{
    /** @var QualityIndicatorProfileRepositoryInterface */
    protected $repository;

    public function __construct(QualityIndicatorProfileRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getPaginated($filters, $perPage);
    }
}
