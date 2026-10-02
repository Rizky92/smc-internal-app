<?php

namespace App\Infrastructure\Akreditasi\Repositories;

use App\Domain\Akreditasi\Repositories\AssessmentScoreHistoryRepositoryInterface;
use App\Models\Akreditasi\AssessmentScoreHistory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentAssessmentScoreHistoryRepository implements AssessmentScoreHistoryRepositoryInterface
{
    public function getByElement(int $assessmentElementId): LengthAwarePaginator
    {
        return AssessmentScoreHistory::query()
            ->where('assessment_element_id', $assessmentElementId)
            ->orderByDesc('created_at')
            ->paginate();
    }

    public function save(array $data): AssessmentScoreHistory
    {
        return AssessmentScoreHistory::create($data);
    }
}
