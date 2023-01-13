<?php

use Illuminate\Auth\AuthenticationException;

if (!function_exists('rp')) {
    /**
     * @param  int|float $nominal
     * @param  int $decimalCount
     * 
     * @return string
     */
    function rp($nominal, $decimalCount = 0)
    {
        return 'Rp. ' . number_format($nominal, $decimalCount, ',', '.');
    }
}

if (!function_exists('map_bulan')) {
    /**
     * @param  \Illuminate\Contracts\Support\Arrayable<int,mixed>|array<int,mixed>|null $data
     * @param  mixed $default
     * 
     * @return array<int,mixed>
     */
    function map_bulan($data = [], $default = 0)
    {
        $arr = [
            1 => $default,
            2 => $default,
            3 => $default,
            4 => $default,
            5 => $default,
            6 => $default,
            7 => $default,
            8 => $default,
            9 => $default,
            10 => $default,
            11 => $default,
            12 => $default,
        ];

        if (empty($data)) {
            return $arr;
        }

        foreach ($data as $bulan => $item) {
            $arr[$bulan] = $item;
        }

        return $arr;
    }
}

if (! function_exists('tracker_start')) {
    function tracker_start(string $connection = 'mysql_sik')
    {
        DB::connection($connection)->enableQueryLog();
    }
}

if (!function_exists('tracker_end')) {
    function tracker_end(string $connection = 'mysql_sik')
    {
        foreach (DB::connection($connection)->getQueryLog() as $log) {
            $sql = Str::of($log['query'])
                ->replaceArray('?', $log['bindings']);

            DB::connection('mysql_smc')->table('trackersql')->insert([
                'tanggal' => now(),
                'sqle' => (string) $sql,
                'usere' => auth()->user()->nip,
            ]);
        }
        
        DB::connection($connection)->disableQueryLog();
    }
}
