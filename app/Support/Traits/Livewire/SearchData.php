<?php

namespace App\Support\Traits\Livewire;

trait SearchData
{
    public function searchData()
    {
        $this->gotoPage(1);

        $this->emit('$refresh');
    }
}