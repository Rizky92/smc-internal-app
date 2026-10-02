<?php

namespace App\Application\Akreditasi\Actions;

use App\Domain\Akreditasi\Repositories\AssessmentDocumentRepositoryInterface;

class UpdateAssessmentDocumentAction
{
    protected $repository;

    public function __construct(AssessmentDocumentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $documentId, string $judul, ?string $keterangan): object
    {
        return $this->repository->save([
            'id'            => $documentId,
            'judul_dokumen' => $judul,
            'keterangan'    => $keterangan,
        ]);
    }
}
