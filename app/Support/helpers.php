<?php

if (! function_exists('rp')) {
    function rp($nominal, $decimalCount = 0)
    {
        return 'Rp. ' . number_format($nominal, $decimalCount, ',', '.');
    }
}

if (! function_exists('bulan')) {
    /**
     * @param  int|string $ke
     * @return \Illuminate\Support\Collection<int, string>|array<int, string>|string
     */
    function bulan($ke = null)
    {
        $bulan = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];

        $lookupBulan = [
            'satu' => $bulan[0],
            'dua' => $bulan[1],
            'tiga' => $bulan[2],
            'empat' => $bulan[3],
            'lima' => $bulan[4],
            'enam' => $bulan[5],
            'tujuh' => $bulan[6],
            'delapan' => $bulan[7],
            'sembilan' => $bulan[8],
            'sepuluh' => $bulan[9],
            'sebelas' => $bulan[10],
            'dua belas' => $bulan[11],
        ];

        if (is_null($ke)) {
            return collect($bulan);
        }

        if (is_string($ke)) {
            return $lookupBulan[Str::lower($ke)];
        }

        return $bulan[$ke - 1];
    }
}

if (! function_exists('map_bulan')) {
    /**
     * @param  array $nilai
     * @return \Illuminate\Support\Collection<mixed, mixed>
     */
    function map_bulan($nilai = null)
    {
        $out = collect(bulan());

        if (is_null($nilai)) {
            return $out;
        }
    }
}