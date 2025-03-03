<?php

namespace App\Support;

use Illuminate\Database\Query\Expression;
use Illuminate\Support\Str;
use InvalidArgumentException;

class MixinQueryBuilder
{
    /**
     * @return \Closure(\Illuminate\Database\Query\Expression|string, mixed, "asc"|"desc"): \Illuminate\Database\Query\Builder
     */
    public function orderByField()
    {
        return function ($column, $values, $direction = 'asc') {
            /** @var \Illuminate\Database\Query\Builder $this */
            if (! in_array($direction, ['asc', 'desc'], true)) {
                throw new InvalidArgumentException('Order direction must be "asc" or "desc".');
            }
            
            $binds = [];

            for ($i = 0; $i < count($values); $i++) {
                $binds[] = '?';
            }

            $binds = implode(', ', $binds);

            if ($column instanceof Expression) {
                $column = $column->getValue($this->getGrammar());
            }

            $direction = Str::lower($direction);

            $startsWith = sprintf('field(%s, ', $column);
            $endsWith = sprintf(') %s', $direction);

            return $this->orderByRaw(Str::wrap($binds, $startsWith, $endsWith), $values);
        };
    }

    /**
     * @return \Closure(\Illuminate\Database\Query\Expression|string, mixed, "asc"|"desc"): \Illuminate\Database\Query\Builder
     */
    public function orderByFieldFirst()
    {
        return function ($column, $values, $direction = 'asc') {
            /** @var \Illuminate\Database\Query\Builder $this */        
            $binds = [];

            for ($i = 0; $i < count($values); $i++) {
                $binds[] = '?';
            }

            $binds = implode(', ', $binds);

            if ($column instanceof Expression) {
                $column = $column->getValue($this->getGrammar());
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
