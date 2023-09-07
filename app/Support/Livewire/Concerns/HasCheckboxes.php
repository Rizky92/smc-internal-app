<?php

namespace App\Support\Livewire\Concerns;

trait HasCheckboxes
{
    /** @var array */
    public $selectedItems;

    abstract public function keyFormat(): string;

    public function bootHasCheckboxes(): void
    {
        //
    }

    public function mountHasCheckboxes(): void
    {
        $this->defaultValueHasCheckboxes();
    }

    public function selectAll($data)
    {
        if (is_callable($data)) {
            $this->selectedItems = $data();
        }

        $this->emit('$refresh');
    }

    public function deselectAll()
    {
        $this->selectedItems = [];

        $this->emit('$refresh');
    }

    protected function defaultValueHasCheckboxes(): void
    {
        $this->selectedItems = [];
    }
}
