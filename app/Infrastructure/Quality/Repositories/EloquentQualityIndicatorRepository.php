<?php

namespace App\Infrastructure\Quality\Repositories;

use App\Domain\Quality\Repositories\QualityIndicatorRepositoryInterface;
use App\Models\Quality\QualityIndicator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentQualityIndicatorRepository implements QualityIndicatorRepositoryInterface
{
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return QualityIndicator::query()
            ->when($filters['unit_id'] ?? null, fn ($q, $unitId) => $q->where('bidang_id', $unitId))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('title', 'like', "%{$search}%"))
            ->paginate($perPage);
    }

    public function findById(int $id): ?QualityIndicator
    {
        return QualityIndicator::find($id);
    }

    public function save(array $data): QualityIndicator
    {
        return QualityIndicator::updateOrCreate(
            ['id' => $data['id'] ?? null],
            $data
        );
    }

    public function delete(int $id): bool
    {
        return QualityIndicator::destroy($id) > 0;
    }
}
