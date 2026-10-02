<?php

namespace App\Application\Akreditasi\DTOs;

class StandardListData
{
    public function __construct(
        public int $id,
        public string $kode,
        public string $judul,
        public ?string $maksudTujuan,
        public int $urutan,
        public int $totalEp,
        public int $epTerisi,
        public int $totalDokumen,
        public float $progressPersen,
    ) {}

    public static function from(object $model): self
    {
        $total = (int) ($model->total_ep ?? 0);

        return new self(
            id: $model->id,
            kode: $model->kode,
            judul: $model->judul,
            maksudTujuan: $model->maksud_tujuan ?? null,
            urutan: (int) ($model->urutan ?? 0),
            totalEp: $total,
            epTerisi: (int) ($model->ep_terisi ?? 0),
            totalDokumen: (int) ($model->total_dokumen ?? 0),
            progressPersen: $total > 0 ? round(((int) ($model->ep_terisi ?? 0) / $total) * 100, 1) : 0,
        );
    }
}
