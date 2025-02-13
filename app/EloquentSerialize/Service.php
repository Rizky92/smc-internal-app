<?php

namespace App\EloquentSerialize;

class Service
{
    use \App\EloquentSerialize\Grammars\ModelGrammar;
    use \App\EloquentSerialize\Grammars\EloquentBuilderGrammar;
    use \App\EloquentSerialize\Grammars\QueryBuilderGrammar;

    /**
     * Pack
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return string
     */
    public function serialize(\Illuminate\Database\Eloquent\Builder $builder): string
    {
        $package = $this->pack($builder);

        return serialize($package); // important!
    }

    /**
     * Unpack
     *
     * @param mixed $package
     * @throws \LogicException
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function unserialize($package): \Illuminate\Database\Eloquent\Builder
    {
        // Prepare data
        if (is_string($package)) {
            $package = unserialize($package);
        }
        if (! ($package instanceof Package)) {
            throw new \LogicException('Incorrect argument.');
        }

        // Unpack
        return $this->unpack($package);
    }
}
