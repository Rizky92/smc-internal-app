<?php

namespace App\Http\Livewire\User\Khanza;

use App\Models\Aplikasi\MappingAksesKhanza;
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

        $currentUser = User::query()
            ->withoutGlobalScopes()
            ->withHakAkses()
            ->whereRaw('AES_DECRYPT(user.id_user, "nur") = ?', $this->nrp)
            ->first();

        $permittedUsers = User::query()
            ->withoutGlobalScopes()
            ->withHakAkses()
            ->whereIn(DB::raw('AES_DECRYPT(user.id_user, "nur")'), $this->checkedUsers)
            ->get();

        $hakAkses = MappingAksesKhanza::pluck('judul_menu', 'nama_field');

        tracker_start();

        foreach ($permittedUsers as $checkedUser) {
            foreach ($hakAkses as $kolom => $judul) {
                $checkedUser->setAttribute($kolom, $currentUser->getAttribute($kolom));
            }

            $checkedUser->save();
        }

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
