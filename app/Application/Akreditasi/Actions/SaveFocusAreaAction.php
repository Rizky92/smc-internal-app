<?php

namespace App\Application\Akreditasi\Actions;

use App\Application\Akreditasi\DTOs\FocusAreaData;
use App\Domain\Akreditasi\Repositories\FocusAreaRepositoryInterface;

class SaveFocusAreaAction
{
    protected $repository;

    public function __construct(FocusAreaRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(FocusAreaData $data): object
    {
        tracker_start('mysql_smc');

        $focusArea = $this->repository->save($data->toArray());

        tracker_end('mysql_smc');

        return $focusArea;
    }
}
