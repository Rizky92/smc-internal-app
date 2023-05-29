<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

if (!function_exists('rp')) {
    /**
     * @param  int|float $nominal = 0
     * @param  int $decimalCount = 0
     * 
     * @return string
     */
    function rp($nominal = 0, int $decimalCount = 0): string
    {
        return 'Rp. ' . number_format($nominal, $decimalCount, ',', '.');
    }
}

if (!function_exists('currency')) {
    /**
     * @param  int|float $nominal
     * @param  int $decimalCount
     * @param  string $name
     * 
     * @return string
     */
    function currency($nominal = 0, int $decimalCount = 0, string $name = 'Rp. '): string
    {
        return $name . number_format($nominal, $decimalCount, ',', '.');
    }
}

if (!function_exists('map_bulan')) {
    /**
     * @param  \Illuminate\Contracts\Support\Arrayable<int, mixed>|array<int, mixed>|null $data
     * @param  mixed $default
     * 
     * @return array<int, mixed>
     */
    function map_bulan($data, $default = 0)
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

if (!function_exists('tracker_start')) {
    /**
     * @param  string $connection
     * 
     * @return void
     */
    function tracker_start(string $connection = 'mysql_smc'): void
    {
        if (app('impersonate')->isImpersonating()) {
            return;
        }
        
        DB::connection($connection)->enableQueryLog();
    }
}

if (!function_exists('tracker_end')) {
    /**
     * @param  string $connection
     * 
     * @return void
     */
    function tracker_end(string $connection = 'mysql_smc'): void
    {
        if (app('impersonate')->isImpersonating()) {
            DB::connection($connection)->disableQueryLog();

            return;
        }

        foreach (DB::connection($connection)->getQueryLog() as $log) {
            foreach ($log['bindings'] as $pos => $value) {
                if (is_string($value)) {
                    $log['bindings'][$pos] = "'{$value}'";
                }
            }

            $sql = Str::of($log['query'])
                ->replaceArray('?', $log['bindings']);

            DB::connection('mysql_smc')->table('trackersql')->insert([
                'tanggal'    => now(),
                'sqle'       => (string) $sql,
                'usere'      => auth()->user()->nik,
                'ip'         => request()->ip(),
                'connection' => $connection,
            ]);
        }

        DB::connection($connection)->disableQueryLog();
    }
}

if (! function_exists('tracker_dispose')) {
    /**
     * @param  string $connection
     * 
     * @return void
     */
    function tracker_dispose(string $connection): void
    {
        DB::connection($connection)->disableQueryLog();
    }
}

if (! function_exists('func_get_named_args')) {
    /**
     * @param  object $object
     * @param  string $name
     * @param  array<string|int, mixed> $args
     * 
     * @return array<string|int, mixed>
     */
    function func_get_named_args($object, $name, $args): array
    {
        $func = new ReflectionMethod($object, $name);
        $res = [];

        foreach ($func->getParameters() as $param) {
            $res[$param->name] = $args[$param->getPosition()] ?? $param->getDefaultValue();
        }

        return $res;
    }
}

if (! function_exists('str')) {
    /**
     * @param  string $str
     * 
     * @return \Illuminate\Support\Stringable
     */
    function str($str = '')
    {
        return Str::of($str);
    }
}

if (! function_exists('maybe')) {
    /**
     * @param  mixed $obj
     * @param  callable $default
     * 
     * @return mixed
     */
    function maybe($obj, callable $default = null)
    {
        if (is_null($obj) && !is_null($default)) {
            return Closure::fromCallable($default);
        }

        if (is_null($obj)) {
            return null;
        }

        return $obj;
    }
}

if (! function_exists('is_between')) {
    /**
     * @param  float|int $value
     * @param  float|int $start
     * @param  float|int $end
     * 
     * @return bool
     */
    function is_between($value, $start = 0, $end = 0): bool
    {
        return ($value >= $start) && ($value <= $end);
    }
}