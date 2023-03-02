<?php

namespace App\Support\Mixins\Collections;

class CustomCollections
{
    public function whereLike()
    {
        return function (?string $search = null, $looseRange = 1) {
            /** @var \Illuminate\Support\Collection $this */

            return $this->filter(function ($v) use ($search, $looseRange) {
                if (empty($search)) return true;

                return levenshtein($v, $search) <= $looseRange;
            });
        };
    }

    public function containsLike()
    {
        return function (?string $search = null, $looseRange = 1) {
            /** @var \Illuminate\Support\Collection $this */

            return $this->contains(function ($v) use ($search, $looseRange) {
                if (empty($search)) return false;
                
                return levenshtein($v, $search) <= $looseRange;
            });
        };
    }
}
