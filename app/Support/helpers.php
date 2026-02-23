<?php

use App\Database\Eloquent\Authenticatable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

if (! function_exists('clamp')) {
    /**
     * Clamp the given number between the given minimum and maximum.
     *
     * @param  int|float  $number
     * @param  int|float  $min
     * @param  int|float  $max
     * @return int|float
     */
    function clamp($number, $min, $max)
    {
        return min(max($number, $min), $max);
    }
}

if (! function_exists('hari')) {
    /**
     * @template TDateTime of \DateTime|\DateTimeImmutable
     *
     * @param  TDateTime|string|null  $date
     */
    function hari($date = null): string
    {
        return str(carbon($date)->translatedFormat('l'))
            ->upper()
            ->replace('MINGGU', 'AKHAD')
            ->value();
    }
}

if (! function_exists('user')) {
    function user(?string $guard = 'web'): Authenticatable
    {
        /** @var Authenticatable|null */
        $user = Auth::guard($guard)->user();

        if (! $user) {
            throw new AuthenticationException('Unauthenticated', [$guard]);
        }

        return $user;
    }
}

if (! function_exists('time_length')) {
    /**
     * @param  Carbon|\DateTimeInterface|string  $start
     * @param  Carbon|\DateTimeInterface|string|null  $end
     */
    function time_length($start, $end): ?string
    {
        if (empty($start) || empty($end)) {
            return null;
        }

        if (is_string($start)) {
            $start = carbon($start);
        }

        if (is_string($end)) {
            $end = carbon($end);
        }

        $length = $start->diff($end);

        $format = '%R %h jam %i menit %s detik';

        if ((int) $length->format('%d') >= 1) {
            $format = '%R %d hari %h jam %i menit %s detik';
        }

        return $length->format($format);
    }
}

if (! function_exists('rp')) {
    /**
     * @param  int|float  $nominal
     */
    function rp($nominal = 0, int $decimalCount = 0): string
    {
        return money($nominal, $decimalCount, 'Rp. ');
    }
}

if (! function_exists('money')) {
    /**
     * @param  int|float  $nominal
     */
    function money($nominal = 0, int $decimalCount = 0, string $denom = ''): string
    {
        switch (round($nominal) <=> 0) {
            case -1:
                return '-'.$denom.number_format(abs($nominal), $decimalCount, ',', '.');
            case 0:
                return $denom.'0';
            case 1:
                return $denom.number_format($nominal, $decimalCount, ',', '.');
        }
    }
}

if (! function_exists('map_bulan')) {
    /**
     * @param  Collection<int, int|float>|array<int, int|float>|null  $data
     * @param  mixed  $default
     * @return mixed[]
     */
    function map_bulan($data, $default = 0)
    {
        $arr = [
            1  => $default,
            2  => $default,
            3  => $default,
            4  => $default,
            5  => $default,
            6  => $default,
            7  => $default,
            8  => $default,
            9  => $default,
            10 => $default,
            11 => $default,
            12 => $default,
        ];

        $namaBulan = collect(
            carbon()->startOfYear()->toPeriod(carbon()->endOfYear(), '1 month')->toArray()
        )
            ->map
            ->translatedFormat('F');

        if (empty($data)) {
            return $arr;
        }

        foreach ($data as $bulan => $item) {
            $arr[$bulan] = $item;
        }

        $arr = array_values($arr);

        return $namaBulan
            ->mapWithKeys(fn (string $bulan, int $key): array => [$bulan => $arr[$key]])
            ->all();
    }
}

if (! function_exists('trackersql')) {
    /**
     * @param  \Closure|callable|null  $callable
     */
    function trackersql(string $connection = 'mysql_smc', ?string $userId = null, $callable = null): void
    {
        if (app('impersonate')->isImpersonating() || app()->runningUnitTests() || ! is_callable($callable)) {
            return;
        }

        DB::connection($connection)->enableQueryLog();

        $callable();

        foreach (DB::connection($connection)->getQueryLog() as $log) {
            foreach ($log['bindings'] as $pos => $value) {
                if (is_string($value)) {
                    $log['bindings'][$pos] = "'{$value}'";
                }

                $log['bindings'] = collect($log['bindings'])->map(function ($value, $key) {
                    if (! is_string($value)) {
                        return $value;
                    }

                    $value = str($value);

                    if ($value->contains('\'')) {
                        $value = $value->replace('\'', '\\\'');
                    }

                    if ($value->contains('?')) {
                        $value = $value->replace('?', '\?');
                    }

                    return "'{$value->value()}'";
                })->all();
            }

            $sql = str($log['query'])
                ->replaceArray('?', $log['bindings']);

            DB::connection('mysql_smc')->table('trackersql')->insert([
                'tanggal'    => now(),
                'sqle'       => (string) $sql,
                'usere'      => $userId ?? user()->nik,
                'ip'         => request()->ip(),
                'connection' => $connection,
            ]);
        }

        DB::connection($connection)->flushQueryLog();
        DB::connection($connection)->disableQueryLog();
    }
}

if (! function_exists('tracker_start')) {
    function tracker_start(string $connection = 'mysql_smc'): void
    {
        if (app('impersonate')->isImpersonating() || app()->runningUnitTests()) {
            return;
        }

        DB::connection($connection)->enableQueryLog();
    }
}

if (! function_exists('tracker_end')) {
    function tracker_end(string $connection = 'mysql_smc', ?string $userId = null): void
    {
        if (! DB::connection($connection)->logging()) {
            return;
        }

        if (app('impersonate')->isImpersonating() || app()->runningUnitTests()) {
            DB::connection($connection)->disableQueryLog();

            return;
        }

        foreach (DB::connection($connection)->getQueryLog() as $log) {
            foreach ($log['bindings'] as $pos => $value) {
                if (is_string($value)) {
                    $log['bindings'][$pos] = "'{$value}'";
                }
            }

            $sql = str($log['query'])
                ->replaceArray('?', $log['bindings'])
                ->value();

            DB::connection('mysql_smc')->table('trackersql')->insert([
                'tanggal'    => now(),
                'sqle'       => $sql,
                'usere'      => $userId ?? user()->nik,
                'ip'         => request()->ip(),
                'connection' => $connection,
            ]);
        }

        DB::connection($connection)->flushQueryLog();
        DB::connection($connection)->disableQueryLog();
    }
}

if (! function_exists('tracker_dispose')) {
    function tracker_dispose(string $connection): void
    {
        DB::connection($connection)->flushQueryLog();
        DB::connection($connection)->disableQueryLog();
    }
}

if (! function_exists('func_get_named_args')) {
    /**
     * @param  object  $object
     * @param  string  $name
     * @param  mixed[]  $args
     * @return mixed[]
     */
    function func_get_named_args($object, $name, $args): array
    {
        $method = new \ReflectionMethod($object, $name);
        $res = [];

        foreach ($method->getParameters() as $param) {
            $res[$param->name] = $args[$param->getPosition()] ?? $param->getDefaultValue();
        }

        return $res;
    }
}

if (! function_exists('str')) {
    /**
     * @template T of string
     *
     * @param  T  $value
     * @return Stringable|string|mixed
     *
     * @psalm-return (T is null ? object : Stringable)
     */
    function str($value = '')
    {
        if (func_num_args() === 0) {
            return new class
            {
                /**
                 * @param  string  $method
                 * @param  mixed  $parameters
                 */
                public function __call($method, $parameters)
                {
                    return Str::$method(...$parameters);
                }

                public function __toString()
                {
                    return '';
                }
            };
        }

        return Str::of($value);
    }
}

if (! function_exists('maybe')) {
    /**
     * @param  mixed  $obj
     * @param  \Closure|callable  $default
     * @return mixed
     */
    function maybe($obj, $default = null)
    {
        if (is_null($obj) && ! is_null($default)) {
            return \Closure::fromCallable($default);
        }

        if (is_null($obj) && is_null($default)) {
            return null;
        }

        return $obj;
    }
}

if (! function_exists('between')) {
    /**
     * @param  float|int  $value
     * @param  float|int  $start
     * @param  float|int  $end
     */
    function between($value, $start = 0, $end = 0, bool $equal = false): bool
    {
        if ($equal) {
            return ($value >= $start) && ($value <= $end);
        }

        return ($value > $start) && ($value < $end);
    }
}

if (! function_exists('attr')) {
    function attr(string $name, array $attributes): string
    {
        $attr = collect($attributes)->filter()->keys()->first();

        return $name.'='.Str::wrap($attr, '"');
    }
}
