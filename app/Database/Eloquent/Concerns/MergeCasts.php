<?php

namespace App\Database\Eloquent\Concerns;

trait MergeCasts
{
    protected function initializeMergeCasts(): void
    {
        $this->casts = $this->ensureCastsAreStringValues(
            array_merge($this->casts, $this->casts())
        );
    }

    protected function casts(): array
    {
        return [];
    }

    protected function mergeCustomCasts(array $casts): self
    {
        $casts = $this->ensureCastsAreStringValues($casts);

        return $this->mergeCasts($casts);
    }

    protected function ensureCastsAreStringValues(array $casts): array
    {
        foreach ($casts as $attribute => $cast) {
            switch (true) {
                case is_array($cast):
                    $casts[$attribute] = value(function () use ($cast) {
                        if (count($cast) === 1) {
                            return $cast[0];
                        }

                        [$cast, $arguments] = [array_shift($cast), $cast];

                        return $cast.':'.implode(',', $arguments);
                    });
                    break;

                default:
                    $casts[$attribute] = $cast;
                    break;
            }
        }

        return $casts;
    }

    public function getCasts()
    {
        if ($this->getIncrementing()) {
            return array_merge([$this->getKeyName() => $this->getKeyType()], $this->casts);
        }

        return $this->casts;
    }
}
