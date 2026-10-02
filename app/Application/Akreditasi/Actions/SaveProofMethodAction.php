<?php

namespace App\Application\Akreditasi\Actions;

use App\Application\Akreditasi\DTOs\ProofMethodData;
use App\Domain\Akreditasi\Repositories\ProofMethodRepositoryInterface;

class SaveProofMethodAction
{
    protected $repository;

    public function __construct(ProofMethodRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(ProofMethodData $data): object
    {
        tracker_start('mysql_smc');

        $proofMethod = $this->repository->save($data->toArray());

        tracker_end('mysql_smc');

        return $proofMethod;
    }
}
