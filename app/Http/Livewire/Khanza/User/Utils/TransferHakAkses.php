<?php

namespace App\Http\Livewire\Khanza\User\Utils;

use App\Models\Aplikasi\User;
use App\Models\Khanza\HakAkses;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TransferHakAkses extends Component
{
    public $nrp;

    public $nama;

    public $checkedUsers;

    public $cariUser;

    protected $listeners = [
        'khanzaTransferHakAkses',
    ];

    public function mount()
    {
        $this->defaultValues();
    }

    public function getHakAksesTersediaProperty()
    {
        Return HakAkses::pluck('judul_menu', 'nama_field');
    }

    public function getAvailableUsersProperty()
    {
        return User::query()
            ->where('petugas.nip', '!=', $this->nrp)
            ->search($this->cariUser)
            ->when(!empty($this->checkedUsers), function (Builder $query) {
                return $query->orWhereIn('petugas.nip', $this->checkedUsers);
            })
            ->limit(50)
            ->get();
    }

    public function render()
    {
        return view('livewire.khanza.user.utils.transfer-hak-akses');
    }

    public function prepareUser(string $nrp, string $nama)
    {
        $this->nrp = $nrp;
        $this->nama = $nama;
    }

    public function khanzaTransferHakAkses(bool $hardTransfer = false)
    {
        if (! auth()->user()->hasRole(config('permission.superadmin_name'))) {
            $this->emit('flash', [
                'flash.type' => 'danger',
                'flash.message' => 'Anda tidak diizinkan untuk melakukan tindakan ini!',
            ]);

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

        dd([$permittedUsers, $currentUser]);

        foreach ($this->checkedUsers as $nrp) {
            $user = User::rawFindByNRP($nrp);

            foreach ($this->hakAksesTersedia as $kolom => $hakAkses) {
                $user->setAttribute($kolom, $currentUser->getAttribute($kolom));

                dd([$kolom, $user->getAttribute($kolom), $user, $currentUser]);
            }

            $user->save();
        }

        $this->emit('flash', [
            'flash.type' => 'success',
            'flash.message' => 'Transfer hak akses berhasil!',
        ]);
    }

    public function defaultValues()
    {
        $this->cariUser = '';
        $this->nrp = '';
        $this->nama = '';
        $this->checkedUsers = [];
    }
}
