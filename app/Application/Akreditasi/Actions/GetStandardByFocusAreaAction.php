<?php

namespace App\Application\Akreditasi\Actions;

use App\Application\Akreditasi\DTOs\StandardListData;
use App\Domain\Akreditasi\Repositories\StandardRepositoryInterface;

class GetStandardByFocusAreaAction
{
    protected $repository;

    public function __construct(StandardRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return StandardListData[]
     */
    public function execute(int $focusAreaId): array
    {
        $standards = $this->repository->getByFocusAreaWithStats($focusAreaId);

        return array_map(fn (object $item) => StandardListData::from($item), $standards);
    }
}
