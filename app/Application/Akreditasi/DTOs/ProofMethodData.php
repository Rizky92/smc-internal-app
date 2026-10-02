<?php

namespace App\Application\Akreditasi\DTOs;

class ProofMethodData
{
    public ?int $id;

    public string $kode;

    public string $nama;

    public function __construct(?int $id, string $kode, string $nama)
    {
        $this->id = $id;
        $this->kode = $kode;
        $this->nama = $nama;
    }

    public static function from(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['kode'],
            $data['nama'],
        );
    }

    public function toArray(): array
    {
        return [
            'id'   => $this->id,
            'kode' => $this->kode,
            'nama' => $this->nama,
        ];
    }
}
