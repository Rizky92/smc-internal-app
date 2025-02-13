<?php

namespace App\EloquentSerialize\Grammars;

use Illuminate\Database\Eloquent\Relations\HasOneOrMany;

trait ModelGrammar
{
    /**
     * Pack
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \App\EloquentSerialize\Package
     */
    protected function pack(\Illuminate\Database\Eloquent\Builder $builder): \App\EloquentSerialize\Package
    {
        $this->setup();

        return new \App\EloquentSerialize\Package([
            'model' => get_class($builder->getModel()),
            'connection' => $builder->getModel()->getConnectionName(),
            'eloquent' => $this->packEloquentBuilder($builder),
            'query' => $this->packQueryBuilder($builder->getQuery()),
        ]);
    }

    /**
     * Unpack
     *
     * @param \App\EloquentSerialize\Package $package
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function unpack(\App\EloquentSerialize\Package $package): \Illuminate\Database\Eloquent\Builder
    {
        $this->setup();

        $builder = $package->get('model');
        $builder = $builder::on($package->get('connection'));

        $this->unpackEloquentBuilder($package->get('eloquent'), $builder);
        $this->unpackQueryBuilder($package->get('query'), $builder->getQuery());

        return $builder;
    }

    /**
     * init
     *
     * @return void
     */
    private function setup(): void
    {
        \Illuminate\Database\Eloquent\Relations\Relation::macro('importExtraParametersForSerialize', function (array $params) {
            foreach ($params as $key => $value) {
                $this->$key = $value;
            }
        });

        \Illuminate\Database\Eloquent\Relations\Relation::macro('exportExtraParametersForSerialize', function () {
            if ($this instanceof \Illuminate\Database\Eloquent\Relations\MorphTo) {
                return [
                    'morphableEagerLoads' => $this->morphableEagerLoads,
                    'morphableEagerLoadCounts' => $this->morphableEagerLoadCounts,
                    'morphableConstraints' => $this->morphableConstraints,
                ];
            }

            if (
                $this instanceof HasOneOrMany
                && in_array(\Illuminate\Database\Eloquent\Relations\Concerns\SupportsInverseRelations::class, class_uses(HasOneOrMany::class)) // @TODO: >= 11.22
            ) {
                return [
                    'inverseRelationship' => $this->inverseRelationship,
                ];
            }

            return null;
        });
    }
}
