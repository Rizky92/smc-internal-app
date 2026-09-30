<?php

return [
    /*
     * Lama file hasil export disimpan sebelum dihapus oleh exports:clean.
     */
    'retention_hours' => (int) env('EXPORT_RETENTION_HOURS', 24),

    /*
     * Ruang kosong minimal (GB) di folder sementara sistem (/tmp) sebelum
     * export mulai menulis. Bila kurang, export langsung gagal alih-alih
     * memenuhi disk di tengah jalan.
     *
     * xlswriter selalu menulis file sementara ke /tmp (tidak bisa diatur),
     * dan sort MySQL untuk query yang sama umumnya juga di sana. Untuk periode
     * 2024 (~8,8 juta baris) keduanya bersama terukur memuncak 11,94 GB.
     * /tmp jangan berupa tmpfs: MySQL berada di server yang sama.
     */
    'min_free_gb' => (float) env('EXPORT_MIN_FREE_GB', 15),
];
