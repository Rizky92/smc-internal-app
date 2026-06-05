<?php

namespace App\Application\Quality\Actions;

use App\Application\Quality\DTOs\QualityInputTypeData;
use App\Domain\Quality\Repositories\QualityInputTypeRepositoryInterface;

class SaveQualityInputTypeAction
{
    /** @var QualityInputTypeRepositoryInterface */
    protected $repository;

    public function __construct(QualityInputTypeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(QualityInputTypeData $data): object
    {
        tracker_start('mysql_smc');

        $inputType = $this->repository->save($data->toArray());

        tracker_end('mysql_smc');

        return $inputType;
    }
}
