<?php

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

if (!function_exists('log_tracker')) {
    function log_tracker($query)
    {
        if (!($query instanceof \Illuminate\Database\Eloquent\Builder || $query instanceof \Illuminate\Database\Query\Builder)) {
            return;
        }

        if (is_array($query)) {
            foreach ($query as $q) {
                if (!($q instanceof \Illuminate\Database\Eloquent\Builder || $q instanceof \Illuminate\Database\Query\Builder)) {
                    continue;
                }

                $sql = Str::of($query->toSql())
                    ->replaceArray('?', $query->getBindings());

                Log::info($sql);
            }
        } else {
            $sql = Str::of($query->toSql())
                ->replaceArray('?', $query->getBindings());
    
            Log::info($sql);
        }
    }
}
