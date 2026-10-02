<?php

namespace App\Infrastructure\Akreditasi\Repositories;

use App\Domain\Akreditasi\Repositories\ProofMethodRepositoryInterface;
use App\Models\Akreditasi\ProofMethod;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentProofMethodRepository implements ProofMethodRepositoryInterface
{
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return ProofMethod::query()
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('nama', 'like', "%{$search}%"))
            ->orderBy('kode')
            ->paginate($perPage);
    }

    public function save(array $data): ProofMethod
    {
        return ProofMethod::updateOrCreate(
            ['id' => $data['id'] ?? null],
            $data
        );
    }

    public function delete(int $id): bool
    {
        return ProofMethod::destroy($id) > 0;
    }
}
