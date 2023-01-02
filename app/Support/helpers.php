<?php

if (! function_exists('rp')) {
    function rp($nominal, $decimalCount = 0)
    {
        return 'Rp. ' . number_format($nominal, $decimalCount, ',', '.');
    }
}

if (! function_exists('map_bulan')) {
    /**
     * @param  \Illuminate\Contracts\Support\Arrayable<int,mixed>|array<int,mixed>|null $data
     * 
     * @return array<int,mixed>
     */
    function map_bulan($data = null)
    {
        $arr = [
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 0,
            7 => 0,
            8 => 0,
            9 => 0,
            10 => 0,
            11 => 0,
            12 => 0,
        ];

        if (is_null($data)) {
            return $arr;
        }

        foreach ($data as $bulan => $item) {
            $arr[$bulan] = $item;
        }

        return $arr;
    }
}