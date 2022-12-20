<?php

namespace App\Support\Traits\Livewire;

trait SearchData
{
    public function searchData()
    {
        $this->resetPage();

        $this->emit('$refresh');
    }
}