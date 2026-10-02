<?php

namespace App\Application\Akreditasi\Actions;

use App\Domain\Akreditasi\Repositories\AssessmentElementRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetAssessmentElementListAction
{
    protected $repository;

    public function __construct(AssessmentElementRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getPaginated($filters, $perPage);
    }
}
