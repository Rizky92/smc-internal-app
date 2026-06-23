<?php

namespace App\Infrastructure\Quality\Repositories;

use App\Domain\Quality\Repositories\QualityIndicatorRepositoryInterface;
use App\Models\Quality\QualityIndicator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentQualityIndicatorRepository implements QualityIndicatorRepositoryInterface
{
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return QualityIndicator::query()
            ->with(['profile', 'departemen'])
            ->when($filters['dep_id'] ?? null, fn ($q, $depId) => $q->where('dep_id', $depId))
            ->when($filters['dep_ids'] ?? null, fn ($q, $depIds) => $q->whereIn('dep_id', $depIds))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->whereHas('profile', fn ($q) => $q->where('title', 'like', "%{$search}%")))
            ->paginate($perPage);
    }

    public function getAll(array $filters = []): Collection
    {
        return QualityIndicator::query()
            ->with(['profile', 'departemen'])
            ->when($filters['dep_id'] ?? null, fn ($q, $depId) => $q->where('dep_id', $depId))
            ->when($filters['dep_ids'] ?? null, fn ($q, $depIds) => $q->whereIn('dep_id', $depIds))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->whereHas('profile', fn ($q) => $q->where('title', 'like', "%{$search}%")))
            ->get();
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
