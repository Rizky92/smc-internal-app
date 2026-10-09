<?php

namespace App\Support\Mutu;

/**
 * Mengambil nilai target dari teks `quality_indicator_profiles.standard` ("80%", "≥ 85 %", "97,5%").
 * Dipakai migrasi backfill `target_value` dan `mutu:audit-profil`, supaya keduanya membaca teks yang sama
 * dengan cara yang sama. Arah target (≥/≤) sengaja tidak diambil: harus dipastikan Komite Mutu.
 */
final class StandardParser
{
    /**
     * Angka pertama di teks; koma atau titik sebagai pemisah desimal. Null bila tidak ada angka.
     */
    public static function parse(?string $standard): ?float
    {
        if ($standard === null || ! preg_match('/\d+(?:[.,]\d+)?/', $standard, $match)) {
            return null;
        }

        return (float) str_replace(',', '.', $match[0]);
    }
}
