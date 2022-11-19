<?php

namespace App\Http\Resources\Farmasi\DataTable;

use Illuminate\Http\Resources\Json\JsonResource;

class LaporanPenggunaanObatPerDokterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'no_resep'       => $this->no_resep,
            'tgl_resep'      => $this->tgl_peresepan,
            'nama_obat'      => $this->nama_brng,
            'jumlah'         => $this->jml,
            'dokter_peresep' => $this->nm_dokter,
        ];
    }
}
