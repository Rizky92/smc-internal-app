<?php

namespace App\Application\Akreditasi\Actions;

use App\Domain\Akreditasi\Repositories\AssessmentDocumentRepositoryInterface;

class DeleteAssessmentDocumentAction
{
    protected $repository;

    public function __construct(AssessmentDocumentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $documentId): bool
    {
        return $this->repository->delete($documentId);
    }
}
