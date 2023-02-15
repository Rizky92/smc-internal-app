<?php

namespace App\Http\Livewire\User\Khanza;

use App\Models\Aplikasi\User;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\LiveTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TransferHakAkses extends Component
{
    use Filterable, LiveTable;

    public $deferLoading;

    public $nrp;

    public $nama;

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
        return $this->deferLoading
            ? []
            : User::query()
            ->where('pegawai.nik', '!=', $this->nrp)
            ->when(!empty($this->checkedUsers), fn (Builder $query) => $query->orWhereIn('pegawai.nik', $this->checkedUsers))
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
            $this->emitUp('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->emitUp('resetFilters');

            return;
        }

        $currentUser = User::rawFindByNRP($this->nrp);

        $hakAkses = collect($currentUser->getAttributes())
            ->except(['id_user', 'password'])
            ->map(fn ($value) => $value ??= 'false')
            ->all();

        tracker_start();

        User::query()
            ->whereIn(DB::raw('AES_DECRYPT(user.id_user, "nur")'), $this->checkedUsers)
            ->update($hakAkses);

        tracker_end();

        $this->emitUp('flash.success', "Transfer hak akses SIMRS Khanza berhasil!");
        $this->emitUp('resetFilters');
    }

    public function showModal()
    {
        $this->deferLoading = false;
    }

    public function hideModal()
    {
        $this->defaultValues();
    }

    private function defaultValues()
    {
        $this->cari = '';
        $this->deferLoading = true;
        $this->nrp = '';
        $this->nama = '';
        $this->checkedUsers = [];
    }
}
