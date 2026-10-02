<?php

namespace App\Infrastructure\Akreditasi\Repositories;

use App\Domain\Akreditasi\Repositories\AssessmentDocumentRepositoryInterface;
use App\Models\Akreditasi\AssessmentDocument;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class EloquentAssessmentDocumentRepository implements AssessmentDocumentRepositoryInterface
{
    public function getByElement(int $assessmentElementId): LengthAwarePaginator
    {
        return AssessmentDocument::query()
            ->where('assessment_element_id', $assessmentElementId)
            ->orderByDesc('created_at')
            ->paginate();
    }

    public function countByElement(int $assessmentElementId): int
    {
        return AssessmentDocument::query()
            ->where('assessment_element_id', $assessmentElementId)
            ->count();
    }

    public function findById(int $id): ?AssessmentDocument
    {
        return AssessmentDocument::find($id);
    }

    public function save(array $data): AssessmentDocument
    {
        return AssessmentDocument::updateOrCreate(
            ['id' => $data['id'] ?? null],
            $data
        );
    }

    public function delete(int $id): bool
    {
        $doc = $this->findById($id);

        if (! $doc) {
            return false;
        }

        if ($doc->file_path && Storage::disk('akreditasi')->exists($doc->file_path)) {
            Storage::disk('akreditasi')->delete($doc->file_path);
        }

        return $doc->delete() !== false;
    }

    public function destroyFile(int $id): void
    {
        $this->delete($id);
    }
}
