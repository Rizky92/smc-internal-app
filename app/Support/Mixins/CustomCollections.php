<?php

namespace App\Support\Mixins;

use Closure;
use Illuminate\Support\Collection;

class CustomCollections
{
    public function whereLike(): Closure
    {
        return function (?string $search = null, int $looseRange = 1): Collection {
            /** @var \Illuminate\Support\Collection $this */

            return $this->filter(function (string $v) use ($search, $looseRange): bool {
                if (empty($search)) return true;

                return levenshtein($v, $search) <= $looseRange;
            });
        };
    }

    public function containsLike(): Closure
    {
        return function (?string $search = null, int $looseRange = 1): bool {
            /** @var \Illuminate\Support\Collection $this */

            return $this->contains(function (string $v) use ($search, $looseRange): bool {
                if (empty($search)) return false;
                
                return levenshtein($v, $search) <= $looseRange;
            });
        };
    }
}
