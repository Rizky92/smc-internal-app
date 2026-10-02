<?php

namespace App\Application\Akreditasi\DTOs;

use Illuminate\Support\Collection;

class AssessmentElementDetailData
{
    public function __construct(
        public readonly int $id,
        public readonly string $kode,
        public readonly string $deskripsi,
        public readonly ?string $penjelasanKelengkapanBukti,
        public readonly ?string $proofMethodKode,
        public readonly int $urutan,
        public readonly int $standardId,
        public readonly string $standardJudul,
        public readonly string $standardKode,
        public readonly ?string $standardMaksudTujuan,
        public readonly string $focusAreaNama,
        public readonly string $focusAreaKode,
        public readonly ?string $skorStatus,
        public readonly ?int $skorNilai,
        public readonly Collection $documents,
    ) {}

    public static function from(object $ep, Collection $documents): self
    {
        return new self(
            id: $ep->id,
            kode: $ep->kode,
            deskripsi: $ep->deskripsi,
            penjelasanKelengkapanBukti: $ep->penjelasan_kelengkapan_bukti,
            proofMethodKode: $ep->proofMethod?->kode,
            urutan: $ep->urutan,
            standardId: $ep->standard_id ?? 0,
            standardJudul: $ep->standard->judul ?? '',
            standardKode: $ep->standard->kode ?? '',
            standardMaksudTujuan: $ep->standard->maksud_tujuan ?? null,
            focusAreaNama: $ep->standard->focusArea->nama ?? '',
            focusAreaKode: $ep->standard->focusArea->kode ?? '',
            skorStatus: $ep->assessmentScore?->skor,
            skorNilai: $ep->assessmentScore?->nilai,
            documents: $documents,
        );
    }
}
