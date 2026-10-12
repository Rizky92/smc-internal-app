<?php

namespace App\Livewire\Concerns;

trait HasCheckboxes
{
    /** @var array */
    public $selectedItems;

    abstract public function keyFormat(): string;

    public function mountHasCheckboxes(): void
    {
        $this->defaultValueHasCheckboxes();
    }

    public function selectAll($data)
    {
        if (is_callable($data)) {
            $this->selectedItems = $data();
        }

        $this->dispatch('$refresh');
    }

    public function deselectAll()
    {
        $this->selectedItems = [];

        $this->dispatch('$refresh');
    }

    protected function defaultValueHasCheckboxes(): void
    {
        $this->selectedItems = [];
    }
}
