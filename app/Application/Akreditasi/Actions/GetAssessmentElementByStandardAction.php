<?php

namespace App\Application\Akreditasi\Actions;

use App\Application\Akreditasi\DTOs\AssessmentElementByStandardData;
use App\Domain\Akreditasi\Repositories\AssessmentElementRepositoryInterface;

class GetAssessmentElementByStandardAction
{
    protected $repository;

    public function __construct(AssessmentElementRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return AssessmentElementByStandardData[]
     */
    public function execute(int $standardId): array
    {
        $elements = $this->repository->getByStandard($standardId);

        return array_map(fn (object $ep) => AssessmentElementByStandardData::from($ep), $elements);
    }
}
