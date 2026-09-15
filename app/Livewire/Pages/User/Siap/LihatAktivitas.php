<?php

namespace App\Livewire\Pages\User\Siap;

use App\Livewire\Concerns\DeferredModal;
use App\Models\Aplikasi\TrackerMenu;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class LihatAktivitas extends Component
{
    use DeferredModal;

    /** @var ?string */
    public $userId;

    /** @var ?string */
    public $nama;

    public function mount(): void
    {
        $this->defaultValues();
    }

    /**
     * @return Collection|array<empty, empty>
     */
    public function getAktivitasUserProperty()
    {
        return $this->isDeferred || empty($this->userId)
            ? []
            : TrackerMenu::lihatAktivitasUser($this->userId)
                ->get()
                ->groupBy(fn (TrackerMenu $model): string => carbon_immutable($model->waktu)->toDateString());
    }

    public function render(): View
    {
        return view('livewire.pages.user.siap.lihat-aktivitas');
    }

    #[On('siap.prepare-la')]
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
