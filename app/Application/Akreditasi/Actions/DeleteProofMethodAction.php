<?php

namespace App\Application\Akreditasi\Actions;

use App\Domain\Akreditasi\Repositories\ProofMethodRepositoryInterface;

class DeleteProofMethodAction
{
    protected $repository;

    public function __construct(ProofMethodRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id): bool
    {
        tracker_start('mysql_smc');

        $result = $this->repository->delete($id);

        tracker_end('mysql_smc');

        return $result;
    }
}
