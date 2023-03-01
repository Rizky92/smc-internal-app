<?php

namespace App\Http\Livewire\User\Khanza;

use App\Models\Aplikasi\User;
use App\Support\Traits\Livewire\DeferredModal;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\LiveTable;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class TransferHakAkses extends Component
{
    use Filterable, LiveTable, DeferredModal;

    public $nrp;

    public $nama;

    public $showChecked;

    public $checkedUsers;

    protected $listeners = [
        'khanza.show-tha' => 'showModal',
        'khanza.hide-tha' => 'hideModal',
        'khanza.prepare-transfer' => 'prepareTransfer',
        'khanza.transfer' => 'transferHakAkses',
    ];

    public function mount()
    {
        $this->defaultValues();
    }

    public function getAvailableUsersProperty()
    {
        return $this->isDeferred
            ? []
            : User::query()
                ->where('pegawai.nik', '!=', $this->nrp)
                ->when($this->showChecked, fn (Builder $query) => $query->orWhereIn('pegawai.nik', $this->checkedUsers))
                ->search($this->cari)
                ->get();
    }

    public function render()
    {
        return view('livewire.user.khanza.transfer-hak-akses');
    }

    public function prepareTransfer(string $nrp = '', string $nama = '')
    {
        $this->nrp = $nrp;
        $this->nama = $nama;
    }

    public function transferHakAkses()
    {
        if (!auth()->user()->hasRole(config('permission.superadmin_name'))) {
            $this->dispatchBrowserEvent('data-denied');
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');

            return;
        }

        $currentUser = User::rawFindByNRP($this->nrp);

        $hakAkses = collect($currentUser->getAttributes())
            ->except(['id_user', 'password'])
            ->map(fn ($value) => $value ??= 'false')
            ->all();

        tracker_start('mysql_sik');

        User::query()
            ->whereIn('pegawai.nik', $this->checkedUsers)
            ->update($hakAkses);

        tracker_end('mysql_sik');

        $this->dispatchBrowserEvent('data-saved');
        $this->emit('flash.success', "Transfer hak akses SIMRS Khanza berhasil!");
    }
    
    public function hideModal()
    {
        $this->defaultValues();

        $this->emitUp('resetState');
    }

    private function defaultValues()
    {
        $this->undefer();

        $this->cari = '';
        $this->nrp = '';
        $this->nama = '';
        $this->showChecked = false;
        $this->checkedUsers = [];
    }
}
