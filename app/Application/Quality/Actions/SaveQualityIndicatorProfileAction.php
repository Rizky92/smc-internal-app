<?php

namespace App\Application\Quality\Actions;

use App\Application\Quality\DTOs\QualityIndicatorProfileData;
use App\Domain\Quality\Repositories\QualityIndicatorProfileRepositoryInterface;

class SaveQualityIndicatorProfileAction
{
    /** @var QualityIndicatorProfileRepositoryInterface */
    protected $repository;

    public function __construct(QualityIndicatorProfileRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(QualityIndicatorProfileData $data): object
    {
        tracker_start('mysql_smc');

        $profile = $this->repository->save($data->toArray());

        tracker_end('mysql_smc');

        return $profile;
    }
}
