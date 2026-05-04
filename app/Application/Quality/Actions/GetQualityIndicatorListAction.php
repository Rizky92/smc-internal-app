<?php

namespace App\Application\Quality\Actions;

use App\Domain\Quality\Repositories\QualityIndicatorRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetQualityIndicatorListAction
{
    /** @var QualityIndicatorRepositoryInterface */
    protected $repository;

    public function __construct(QualityIndicatorRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getPaginated($filters, $perPage);
    }
}
