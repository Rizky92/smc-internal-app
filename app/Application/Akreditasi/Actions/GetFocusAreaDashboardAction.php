<?php

namespace App\Application\Akreditasi\Actions;

use App\Application\Akreditasi\DTOs\FocusAreaDashboardData;
use App\Domain\Akreditasi\Repositories\FocusAreaRepositoryInterface;

class GetFocusAreaDashboardAction
{
    protected $repository;

    public function __construct(FocusAreaRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return FocusAreaDashboardData[]
     */
    public function execute(): array
    {
        $focusAreas = $this->repository->getAllWithStats();

        return array_map(fn (object $item) => FocusAreaDashboardData::from($item), $focusAreas);
    }
}
