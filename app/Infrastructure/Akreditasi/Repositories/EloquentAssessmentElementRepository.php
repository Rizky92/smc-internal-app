<?php

namespace App\Infrastructure\Akreditasi\Repositories;

use App\Domain\Akreditasi\Repositories\AssessmentElementRepositoryInterface;
use App\Models\Akreditasi\AssessmentElement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentAssessmentElementRepository implements AssessmentElementRepositoryInterface
{
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return AssessmentElement::query()
            ->with(['standard.focusArea', 'proofMethod'])
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('deskripsi', 'like', "%{$search}%"))
            ->when($filters['standard_id'] ?? null, fn ($q, $id) => $q->where('standard_id', $id))
            ->orderBy('urutan')
            ->paginate($perPage);
    }

    public function getByStandard(int $standardId): array
    {
        return AssessmentElement::query()
            ->with(['proofMethod', 'assessmentScore'])
            ->withCount('assessmentDocuments')
            ->where('standard_id', $standardId)
            ->orderBy('urutan')
            ->get()
            ->all();
    }

    public function findById(int $id): ?AssessmentElement
    {
        return AssessmentElement::query()
            ->with(['standard.focusArea', 'proofMethod', 'assessmentScore'])
            ->find($id);
    }

    public function save(array $data): AssessmentElement
    {
        return AssessmentElement::updateOrCreate(
            ['id' => $data['id'] ?? null],
            $data
        );
    }

    public function delete(int $id): bool
    {
        return AssessmentElement::destroy($id) > 0;
    }
}
