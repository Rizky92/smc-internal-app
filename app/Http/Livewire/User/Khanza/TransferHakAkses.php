<?php

namespace App\Http\Livewire\User\Khanza;

use App\Models\Aplikasi\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TransferHakAkses extends Component
{
    public $deferLoading;

    public $nrp;

    public $nama;

    public $checkedUsers;

    public $cari;

    protected $listeners = [
        'khanza.show-tha' => 'showModal',
        'khanza.hide-tha' => 'hideModal',
        'khanza.prepare-transfer' => 'prepareTransfer',
        'khanza.transfer' => 'transferHakAkses',
    ];

    protected function queryString()
    {
        return [
            'cari' => ['except' => '', 'as' => 'q'],
        ];
    }

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
            $this->emitTo('user.manajemen-user', 'flashError', 'Anda tidak diizinkan untuk melakukan tindakan ini!');

            return;
        }

        $currentUser = User::rawFindByNRP($this->nrp);

        $hakAkses = collect($currentUser->getAttributes())
            ->except(['id_user', 'password'])
            ->map(fn ($value) => $value ??= 'false')
            ->all();

        $checkedUsers = collect($this->checkedUsers)
            ->map(fn ($value) => trim($value))
            ->all();

        tracker_start();

        User::query()
            ->whereIn(DB::raw('AES_DECRYPT(user.id_user, "nur")'), $checkedUsers)
            ->update($hakAkses);

        tracker_end();

        $this->emitTo('user.manajemen-user', 'flashSuccess', "Transfer hak akses SIMRS Khanza berhasil!");
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
        $this->deferLoading = true;
        $this->cari = '';
        $this->nrp = '';
        $this->nama = '';
        $this->checkedUsers = [];
    }
}
