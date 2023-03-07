<?php

namespace App\Http\Livewire\User\Siap;

use App\Models\Aplikasi\TrackerMenu;
use App\Support\Traits\Livewire\DeferredModal;
use Livewire\Component;

class LihatAktivitas extends Component
{
    use DeferredModal;

    public $userId;

    public $nama;

    protected $listeners = [
        'siap.prepare-la' => 'prepare',
        'siap.show-la' => 'showModal',
        'siap.hide-la' => 'hideModal',
    ];

    public function mount()
    {
        $this->defaultValues();
    }

    public function getAktivitasUserProperty()
    {
        return $this->isDeferred || empty($this->userId)
            ? []
            : TrackerMenu::lihatAktivitasUser($this->userId)
                ->get()
                ->groupBy(fn ($model) => carbon_immutable($model->waktu)->format('Y-m-d'));
    }

    public function render()
    {
        return view('livewire.user.siap.lihat-aktivitas');
    }

    public function prepare(?string $userId = null, ?string $nama = null)
    {
        $this->userId = $userId;
        $this->nama = $nama;
    }

    protected function defaultValues()
    {
        $this->userId = null;
        $this->nama = null;
    }
}
