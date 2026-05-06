<?php

namespace App\Application\Quality\Actions;

use App\Application\Quality\DTOs\QualityIndicatorRecordData;
use App\Domain\Quality\Repositories\QualityIndicatorRecordRepositoryInterface;

class SaveQualityIndicatorRecordAction
{
    /** @var QualityIndicatorRecordRepositoryInterface */
    protected $repository;

    public function __construct(QualityIndicatorRecordRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(QualityIndicatorRecordData $data): object
    {
        $payload = $data->toArray();

        if (is_null($payload['recorded_by'])) {
            $payload['recorded_by'] = auth()->id();
        }

        tracker_start('mysql_smc');

        $record = $this->repository->save($payload);

        tracker_end('mysql_smc');

        return $record;
    }
}
