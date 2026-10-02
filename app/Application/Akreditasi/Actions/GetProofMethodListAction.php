<?php

namespace App\Application\Akreditasi\Actions;

use App\Domain\Akreditasi\Repositories\ProofMethodRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetProofMethodListAction
{
    protected $repository;

    public function __construct(ProofMethodRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getPaginated($filters, $perPage);
    }
}
