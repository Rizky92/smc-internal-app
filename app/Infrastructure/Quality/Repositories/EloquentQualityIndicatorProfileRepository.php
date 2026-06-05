<?php

namespace App\Infrastructure\Quality\Repositories;

use App\Domain\Quality\Repositories\QualityIndicatorProfileRepositoryInterface;
use App\Models\Quality\QualityIndicatorProfile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentQualityIndicatorProfileRepository implements QualityIndicatorProfileRepositoryInterface
{
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return QualityIndicatorProfile::query()
            ->with('category')
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('title', 'like', "%{$search}%"))
            ->paginate($perPage);
    }

    public function getAll(array $filters = []): Collection
    {
        return QualityIndicatorProfile::query()
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('title', 'like', "%{$search}%"))
            ->get();
    }

    public function findById(int $id): ?QualityIndicatorProfile
    {
        return QualityIndicatorProfile::find($id);
    }

    public function save(array $data): QualityIndicatorProfile
    {
        return QualityIndicatorProfile::updateOrCreate(
            ['id' => $data['id'] ?? null],
            $data
        );
    }

    public function delete(int $id): bool
    {
        return QualityIndicatorProfile::destroy($id) > 0;
    }
}
