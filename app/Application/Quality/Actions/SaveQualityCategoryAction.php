<?php

namespace App\Application\Quality\Actions;

use App\Application\Quality\DTOs\QualityCategoryData;
use App\Domain\Quality\Repositories\QualityCategoryRepositoryInterface;

class SaveQualityCategoryAction
{
    /** @var QualityCategoryRepositoryInterface */
    protected $repository;

    public function __construct(QualityCategoryRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(QualityCategoryData $data): object
    {
        tracker_start('mysql_smc');

        $category = $this->repository->save($data->toArray());

        tracker_end('mysql_smc');

        return $category;
    }
}
