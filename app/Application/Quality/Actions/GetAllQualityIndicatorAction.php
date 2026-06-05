<?php

namespace App\Application\Quality\Actions;

use App\Domain\Quality\Repositories\QualityIndicatorRepositoryInterface;
use Illuminate\Support\Collection;

class GetAllQualityIndicatorAction
{
    /** @var QualityIndicatorRepositoryInterface */
    protected $repository;

    public function __construct(QualityIndicatorRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(array $filters = []): Collection
    {
        return $this->repository->getAll($filters);
    }
}
