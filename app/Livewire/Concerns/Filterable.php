<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Str;
use Livewire\Attributes\On;

trait Filterable
{
    abstract protected function defaultValues(): void;

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

    #[On('searchData')]
    public function searchData(): void
    {
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }

        if (property_exists($this, 'isDeferred')) {
            $this->isDeferred = false;
        }

        $this->dispatch('$refresh');
    }

    #[On('resetState')]
    public function resetState(): void
    {
        $this->defaultValues();

        $this->dispatch('$refresh');
    }

    #[On('resetFilters')]
    public function resetFilters(): void
    {
        $this->defaultValues();
        $this->getDefaultValues();

        $this->searchData();
    }

    #[On('fullRefresh')]
    public function fullRefresh(): void
    {
        $this->forgetComputed();

        $this->resetFilters();
    }
}
