<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        //
        // '/smc/livewire/message/farmasi.kunjungan-resep-pasien',
        // '/smc/livewire/message/farmasi.laporan-produksi-tahunan',
        // '/smc/livewire/message/farmasi.penggunaan-obat-perdokter',
        // '/smc/livewire/message/farmasi.stok-darurat-farmasi',
        // '/smc/livewire/message/logistik.stok-darurat-logistik',
        // '/smc/livewire/message/logistik.stok-input-minmax-barang',
        // '/smc/livewire/message/perawatan.perawatan/daftar-pasien-ranap',
        // '/smc/livewire/message/rekam-medis.laporan-statistik-rekam-medis',
        // '/smc/livewire/message/user.manajemen-user',
        // '/smc/livewire/message/user.set-hak-akses',
    ];
}
