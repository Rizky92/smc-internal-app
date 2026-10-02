<?php

namespace App\Application\Akreditasi\DTOs;

class StandardData
{
    public ?int $id;

    public int $focusAreaId;

    public string $kode;

    public string $judul;

    public ?string $maksudTujuan;

    public int $urutan;

    public function __construct(?int $id, int $focusAreaId, string $kode, string $judul, ?string $maksudTujuan, int $urutan)
    {
        $this->id = $id;
        $this->focusAreaId = $focusAreaId;
        $this->kode = $kode;
        $this->judul = $judul;
        $this->maksudTujuan = $maksudTujuan;
        $this->urutan = $urutan;
    }

    public static function from(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            (int) $data['focus_area_id'],
            $data['kode'],
            $data['judul'],
            $data['maksud_tujuan'] ?? null,
            (int) ($data['urutan'] ?? 0),
        );
    }

    public function toArray(): array
    {
        return [
            'id'             => $this->id,
            'focus_area_id'  => $this->focusAreaId,
            'kode'           => $this->kode,
            'judul'          => $this->judul,
            'maksud_tujuan'  => $this->maksudTujuan,
            'urutan'         => $this->urutan,
        ];
    }
}
