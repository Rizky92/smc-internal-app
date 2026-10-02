<?php

namespace App\Application\Akreditasi\DTOs;

class AssessmentElementByStandardData
{
    public function __construct(
        public readonly int $id,
        public readonly string $kode,
        public readonly string $deskripsi,
        public readonly ?string $penjelasanKelengkapanBukti,
        public readonly ?string $proofMethodKode,
        public readonly int $urutan,
        public readonly int $totalDokumen,
        public readonly ?string $skorStatus,
        public readonly ?int $skorNilai,
    ) {}

    public static function from(object $ep): self
    {
        return new self(
            id: $ep->id,
            kode: $ep->kode,
            deskripsi: $ep->deskripsi,
            penjelasanKelengkapanBukti: $ep->penjelasan_kelengkapan_bukti,
            proofMethodKode: $ep->proofMethod?->kode,
            urutan: $ep->urutan,
            totalDokumen: $ep->assessment_documents_count ?? 0,
            skorStatus: $ep->assessmentScore?->skor,
            skorNilai: $ep->assessmentScore?->nilai,
        );
    }
}
