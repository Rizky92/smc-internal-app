<?php

namespace App\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Str;
use InvalidArgumentException;

class MixinQueryBuilder
{
    /**
     * @return \Closure(Expression|string, mixed, "asc"|"desc"): Builder
     */
    public function orderByField()
    {
        /** psalm-scope-this Illuminate\Database\Query\Builder */
        return function ($column, $values, $direction = 'asc') {
            if (! in_array($direction, ['asc', 'desc'], true)) {
                throw new InvalidArgumentException('Order direction must be "asc" or "desc".');
            }

            $binds = [];

            for ($i = 0; $i < count($values); $i++) {
                $binds[] = '?';
            }

            $binds = implode(', ', $binds);

            if ($column instanceof Expression) {
                $column = $column->getValue();
            }

            $direction = Str::lower($direction);

            $startsWith = sprintf('field(%s, ', $column);
            $endsWith = sprintf(') %s', $direction);

            return $this->orderByRaw(Str::wrap($binds, $startsWith, $endsWith), $values);
        };
    }

    /**
     * @return \Closure(Expression|string, mixed, "asc"|"desc"): Builder
     */
    public function orderByFieldFirst()
    {
        /** psalm-scope-this Illuminate\Database\Query\Builder */
        return function ($column, $values, $direction = 'asc') {
            $binds = [];

            for ($i = 0; $i < count($values); $i++) {
                $binds[] = '?';
            }

            $binds = implode(', ', $binds);

            if ($column instanceof Expression) {
                $column = $column->getValue();
            }

            $direction = Str::lower($direction);

            if (! in_array($direction, ['asc', 'desc'], true)) {
                throw new InvalidArgumentException('Order direction must be "asc" or "desc".');
            }

            $startsWith = sprintf('(field(%s, ', $column);
            $endsWith = ') != 0) desc';

            return $this
                ->orderByRaw(Str::wrap($binds, $startsWith, $endsWith), $values)
                ->orderByField($column, $values, $direction);
        };
    }
}
