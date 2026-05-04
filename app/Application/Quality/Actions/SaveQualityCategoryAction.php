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
        return $this->repository->save($data->toArray());
    }
}
