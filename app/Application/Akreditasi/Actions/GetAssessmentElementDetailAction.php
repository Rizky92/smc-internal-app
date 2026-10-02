<?php

namespace App\Application\Akreditasi\Actions;

use App\Application\Akreditasi\DTOs\AssessmentElementDetailData;
use App\Domain\Akreditasi\Repositories\AssessmentDocumentRepositoryInterface;
use App\Domain\Akreditasi\Repositories\AssessmentElementRepositoryInterface;

class GetAssessmentElementDetailAction
{
    protected $elementRepository;

    protected $documentRepository;

    public function __construct(
        AssessmentElementRepositoryInterface $elementRepository,
        AssessmentDocumentRepositoryInterface $documentRepository,
    ) {
        $this->elementRepository = $elementRepository;
        $this->documentRepository = $documentRepository;
    }

    public function execute(int $assessmentElementId): ?AssessmentElementDetailData
    {
        $ep = $this->elementRepository->findById($assessmentElementId);

        if (! $ep) {
            return null;
        }

        $documents = $this->documentRepository->getByElement($assessmentElementId);

        $allDocs = collect($documents->items());

        return AssessmentElementDetailData::from($ep, $allDocs);
    }
}
