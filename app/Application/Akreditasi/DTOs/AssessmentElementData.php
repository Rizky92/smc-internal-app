<?php

namespace App\Application\Akreditasi\DTOs;

class AssessmentElementData
{
    public ?int $id;

    public int $standardId;

    public string $kode;

    public string $deskripsi;

    public ?string $penjelasanKelengkapanBukti;

    public ?int $proofMethodId;

    public int $urutan;

    public function __construct(
        ?int $id,
        int $standardId,
        string $kode,
        string $deskripsi,
        ?string $penjelasanKelengkapanBukti,
        ?int $proofMethodId,
        int $urutan,
    ) {
        $this->id = $id;
        $this->standardId = $standardId;
        $this->kode = $kode;
        $this->deskripsi = $deskripsi;
        $this->penjelasanKelengkapanBukti = $penjelasanKelengkapanBukti;
        $this->proofMethodId = $proofMethodId;
        $this->urutan = $urutan;
    }

    public static function from(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            (int) $data['standard_id'],
            $data['kode'],
            $data['deskripsi'],
            $data['penjelasan_kelengkapan_bukti'] ?? null,
            isset($data['proof_method_id']) ? (int) $data['proof_method_id'] : null,
            (int) ($data['urutan'] ?? 0),
        );
    }

    public function toArray(): array
    {
        return [
            'id'                           => $this->id,
            'standard_id'                  => $this->standardId,
            'kode'                         => $this->kode,
            'deskripsi'                    => $this->deskripsi,
            'penjelasan_kelengkapan_bukti' => $this->penjelasanKelengkapanBukti,
            'proof_method_id'              => $this->proofMethodId,
            'urutan'                       => $this->urutan,
        ];
    }
}
