<?php

namespace App\Application\Akreditasi\Actions;

use App\Application\Akreditasi\DTOs\StandardData;
use App\Domain\Akreditasi\Repositories\StandardRepositoryInterface;

class SaveStandardAction
{
    protected $repository;

    public function __construct(StandardRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(StandardData $data): object
    {
        tracker_start('mysql_smc');

        $standard = $this->repository->save($data->toArray());

        tracker_end('mysql_smc');

        return $standard;
    }
}
