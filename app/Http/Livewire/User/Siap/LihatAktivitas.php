<?php

namespace App\Http\Livewire\User\Siap;

use App\Models\Aplikasi\TrackerMenu;
use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\Filterable;
use Livewire\Component;

class LihatAktivitas extends Component
{
    use Filterable, DeferredLoading;

    public $userId;

    public function mount()
    {
        $this->defaultValues();
    }

    public function getAktivitasUserProperty()
    {
        return $this->isDeferred || empty($this->userId)
            ? []
            : TrackerMenu::query()
                ->lihatAktivitasUser($this->userId)
                ->get()
                ->groupBy(fn ($model) => carbon($model->waktu)->format('Y-m-d'));
    }

    public function render()
    {
        return view('livewire.user.siap.lihat-aktivitas');
    }

    protected function defaultValues()
    {
        $this->userId = '';
    }
}
