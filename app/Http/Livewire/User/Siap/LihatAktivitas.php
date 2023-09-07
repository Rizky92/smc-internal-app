<?php

namespace App\Http\Livewire\User\Siap;

use App\Models\Aplikasi\TrackerMenu;
use App\Support\Livewire\Concerns\DeferredModal;
use Illuminate\View\View;
use Livewire\Component;

class LihatAktivitas extends Component
{
    use DeferredModal;

    /** @var ?string */
    public $userId;

    /** @var ?string */
    public $nama;

    /** @var mixed */
    protected $listeners = [
        'siap.prepare-la' => 'prepareUser',
        'siap.show-la'    => 'showModal',
        'siap.hide-la'    => 'hideModal',
    ];

    public function mount(): void
    {
        $this->defaultValues();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|array<empty, empty>
     */
    public function getAktivitasUserProperty()
    {
        return $this->isDeferred || empty($this->userId)
            ? []
            : TrackerMenu::lihatAktivitasUser($this->userId)
            ->get()
            ->groupBy(fn (TrackerMenu $model): string => carbon_immutable($model->waktu)->format('Y-m-d'));
    }

    public function render(): View
    {
        return view('livewire.user.siap.lihat-aktivitas');
    }

    public function prepareUser(?string $userId = null, ?string $nama = null): void
    {
        $this->userId = $userId;
        $this->nama = $nama;
    }

    protected function defaultValues(): void
    {
        $this->userId = null;
        $this->nama = null;
    }
}
