<?php

namespace App\Application\Quality\Actions;

use App\Application\Quality\DTOs\QualityIndicatorData;
use App\Domain\Quality\Repositories\QualityIndicatorRepositoryInterface;

class SaveQualityIndicatorAction
{
    /** @var QualityIndicatorRepositoryInterface */
    protected $repository;

    public function __construct(QualityIndicatorRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(QualityIndicatorData $data): object
    {
        tracker_start('mysql_smc');

        $indicator = $this->repository->save($data->toArray());

        tracker_end('mysql_smc');

        return $indicator;
    }
}
