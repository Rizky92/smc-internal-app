<?php

namespace App\Application\Akreditasi\DTOs;

class FocusAreaData
{
    public ?int $id;

    public string $kode;

    public string $nama;

    public ?string $deskripsi;

    public int $urutan;

    public function __construct(?int $id, string $kode, string $nama, ?string $deskripsi, int $urutan)
    {
        $this->id = $id;
        $this->kode = $kode;
        $this->nama = $nama;
        $this->deskripsi = $deskripsi;
        $this->urutan = $urutan;
    }

    public static function from(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['kode'],
            $data['nama'],
            $data['deskripsi'] ?? null,
            (int) ($data['urutan'] ?? 0),
        );
    }

    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'kode'      => $this->kode,
            'nama'      => $this->nama,
            'deskripsi' => $this->deskripsi,
            'urutan'    => $this->urutan,
        ];
    }
}
