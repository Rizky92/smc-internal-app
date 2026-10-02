<?php

namespace App\Application\Akreditasi\Actions;

use App\Domain\Akreditasi\Repositories\AssessmentDocumentRepositoryInterface;
use Illuminate\Http\UploadedFile;

class UploadAssessmentDocumentAction
{
    protected $repository;

    public function __construct(AssessmentDocumentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $assessmentElementId, UploadedFile $file, string $judul, ?string $keterangan, string $uploadedBy): object
    {
        $path = $file->store('ep-'.$assessmentElementId, 'akreditasi');

        return $this->repository->save([
            'assessment_element_id' => $assessmentElementId,
            'judul_dokumen'         => $judul,
            'keterangan'            => $keterangan,
            'file_path'             => $path,
            'file_size'             => $file->getSize(),
            'mime_type'             => $file->getMimeType(),
            'uploaded_by'           => $uploadedBy,
        ]);
    }
}
