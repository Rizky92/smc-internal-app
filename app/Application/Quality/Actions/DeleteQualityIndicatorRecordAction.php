<?php

namespace App\Application\Quality\Actions;

use App\Domain\Quality\Repositories\QualityIndicatorRecordRepositoryInterface;

class DeleteQualityIndicatorRecordAction
{
    /** @var QualityIndicatorRecordRepositoryInterface */
    protected $repository;

    public function __construct(QualityIndicatorRecordRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $indicatorId, string $date): bool
    {
        tracker_start('mysql_smc');

        $result = $this->repository->delete($indicatorId, $date);

        tracker_end('mysql_smc');

        return $result;
    }
}
