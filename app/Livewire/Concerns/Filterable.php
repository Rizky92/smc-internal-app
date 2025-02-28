<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Str;

trait Filterable
{
    abstract protected function defaultValues(): void;

    public function initializeFilterable(): void
    {
        $this->listeners = array_merge($this->listeners, [
            'searchData',
            'resetState',
            'resetFilters',
            'fullRefresh',
        ]);
    }

    protected function getDefaultValues(): void
    {
        collect(class_uses_recursive(static::class))
            ->filter(fn (string $v) => Str::startsWith($v, 'App\\Livewire\\Concerns\\'))
            ->map(fn (string $v) => 'defaultValues'.class_basename($v))
            ->each(function (string $method) {
                if (method_exists($this, $method)) {
                    $this->{$method}();
                }
            });
    }

    public function searchData(): void
    {
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }

        if (property_exists($this, 'isDeferred')) {
            $this->isDeferred = false;
        }

        $this->emit('$refresh');
    }

    public function resetState(): void
    {
        $this->defaultValues();

        $this->emit('$refresh');
    }

    public function resetFilters(): void
    {
        $this->defaultValues();
        $this->getDefaultValues();

        $this->searchData();
    }

    public function fullRefresh(): void
    {
        $this->forgetComputed();

        $this->resetFilters();
    }
}
