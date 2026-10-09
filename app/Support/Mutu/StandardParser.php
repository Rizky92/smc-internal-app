<?php

namespace App\Support\Mutu;

/**
 * Mengambil nilai target dari teks `quality_indicator_profiles.standard` ("80%", "≥ 85 %", "97,5%").
 * Dipakai migrasi backfill `target_value` dan `mutu:audit-profil`, supaya keduanya membaca teks yang sama
 * dengan cara yang sama. Arah target (≥/≤) hanya diambil bila ditulis eksplisit; selain itu dipastikan Komite Mutu.
 */
final class StandardParser
{
    private const GTE = '/(?:≥|>=|\bminimal\b)/iu';

    private const LTE = '/(?:≤|<=|\bmaksimal\b)/iu';

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

    /**
     * `gte` bila teks memuat ≥, >= atau kata "minimal"; `lte` bila memuat ≤, <= atau "maksimal".
     * Null bila arah tidak ditulis, hanya > / <, atau keduanya muncul sekaligus.
     */
    public static function parseOperator(?string $standard): ?string
    {
        if ($standard === null) {
            return null;
        }

        $gte = preg_match(self::GTE, $standard) === 1;
        $lte = preg_match(self::LTE, $standard) === 1;

        if ($gte === $lte) {
            return null;
        }

        return $gte ? 'gte' : 'lte';
    }
}
