<?php

namespace App\Infrastructure\Quality\Repositories;

use App\Domain\Quality\Repositories\QualityCategoryRepositoryInterface;
use App\Models\Quality\QualityIndicatorCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentQualityCategoryRepository implements QualityCategoryRepositoryInterface
{
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return QualityIndicatorCategory::query()
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->paginate($perPage);
    }

    public function save(array $data): QualityIndicatorCategory
    {
        return QualityIndicatorCategory::updateOrCreate(
            ['id' => $data['id'] ?? null],
            $data
        );
    }

    public function delete(int $id): bool
    {
        return QualityIndicatorCategory::destroy($id) > 0;
    }
}
