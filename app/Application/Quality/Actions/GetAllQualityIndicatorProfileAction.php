<?php

namespace App\Application\Quality\Actions;

use App\Domain\Quality\Repositories\QualityIndicatorProfileRepositoryInterface;
use Illuminate\Support\Collection;

class GetAllQualityIndicatorProfileAction
{
    /** @var QualityIndicatorProfileRepositoryInterface */
    protected $repository;

    public function __construct(QualityIndicatorProfileRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(array $filters = []): Collection
    {
        return $this->repository->getAll($filters);
    }
}
