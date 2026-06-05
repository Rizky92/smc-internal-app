<?php

namespace App\Infrastructure\Quality\Repositories;

use App\Domain\Quality\Repositories\QualityInputTypeRepositoryInterface;
use App\Models\Quality\QualityIndicatorInputType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentQualityInputTypeRepository implements QualityInputTypeRepositoryInterface
{
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return QualityIndicatorInputType::query()
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->paginate($perPage);
    }

    public function save(array $data): QualityIndicatorInputType
    {
        return QualityIndicatorInputType::updateOrCreate(
            ['id' => $data['id'] ?? null],
            $data
        );
    }

    public function delete(int $id): bool
    {
        return QualityIndicatorInputType::destroy($id) > 0;
    }
}
