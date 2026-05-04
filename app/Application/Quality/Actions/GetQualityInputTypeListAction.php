<?php

namespace App\Application\Quality\Actions;

use App\Domain\Quality\Repositories\QualityInputTypeRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetQualityInputTypeListAction
{
    /** @var QualityInputTypeRepositoryInterface */
    protected $repository;

    public function __construct(QualityInputTypeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getPaginated($filters, $perPage);
    }
}
