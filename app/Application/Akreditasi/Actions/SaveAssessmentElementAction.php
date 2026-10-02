<?php

namespace App\Application\Akreditasi\Actions;

use App\Application\Akreditasi\DTOs\AssessmentElementData;
use App\Domain\Akreditasi\Repositories\AssessmentElementRepositoryInterface;

class SaveAssessmentElementAction
{
    protected $repository;

    public function __construct(AssessmentElementRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(AssessmentElementData $data): object
    {
        tracker_start('mysql_smc');

        $element = $this->repository->save($data->toArray());

        tracker_end('mysql_smc');

        return $element;
    }
}
