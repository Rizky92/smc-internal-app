<?php

namespace App\Infrastructure\Akreditasi\Repositories;

use App\Domain\Akreditasi\Repositories\AssessmentScoreRepositoryInterface;
use App\Models\Akreditasi\AssessmentScore;

class EloquentAssessmentScoreRepository implements AssessmentScoreRepositoryInterface
{
    public function findByElement(int $assessmentElementId): ?AssessmentScore
    {
        return AssessmentScore::query()
            ->where('assessment_element_id', $assessmentElementId)
            ->first();
    }

    public function save(array $data): AssessmentScore
    {
        return AssessmentScore::updateOrCreate(
            ['id' => $data['id'] ?? null],
            $data
        );
    }

    public function delete(int $id): bool
    {
        return AssessmentScore::destroy($id) > 0;
    }
}
