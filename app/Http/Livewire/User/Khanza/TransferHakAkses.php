<?php

namespace App\Http\Livewire\User\Khanza;

use App\Models\Aplikasi\MappingAksesKhanza;
use App\Models\Aplikasi\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TransferHakAkses extends Component
{
    public $nrp;

    public $nama;

    public $checkedUsers;

    public $khanzaCariUser;

    protected $listeners = [
        'khanzaPrepareTransfer',
        'khanzaTransferHakAkses',
    ];

    protected function queryString()
    {
        return [
            'khanzaCariUser' => [
                'except' => '',
                'as' => 'khanza_search',
            ],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function getHakAksesTersediaProperty()
    {
        return MappingAksesKhanza::pluck('judul_menu', 'nama_field');
    }

    public function getAvailableUsersProperty()
    {
        return User::query()
            ->where('petugas.nip', '!=', $this->nrp)
            ->search($this->khanzaCariUser)
            ->when(!empty($this->checkedUsers), function (Builder $query) {
                return $query->orWhereIn('petugas.nip', $this->checkedUsers);
            })
            ->limit(50)
            ->get();
    }

    public function render()
    {
        return view('livewire.user.khanza.transfer-hak-akses');
    }

    public function khanzaPrepareTransfer(string $nrp, string $nama)
    {
        $this->nrp = $nrp;
        $this->nama = $nama;
    }

    public function khanzaTransferHakAkses()
    {
        if (!auth()->user()->hasRole(config('permission.superadmin_name'))) {
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

        // dd([$permittedUsers, $currentUser]);

        foreach ($permittedUsers as $checkedUser) {
            foreach ($this->hakAksesTersedia as $kolom => $hakAkses) {
                $checkedUser->setAttribute($kolom, $currentUser->getAttribute($kolom));

                // dd([$kolom, $checkedUser->getAttribute($kolom), $checkedUser, $currentUser]);
            }

            $checkedUser->save();
        }

        $this->emit('flash', [
            'flash.type' => 'success',
            'flash.message' => 'Transfer hak akses berhasil!',
        ]);
    }

    public function defaultValues()
    {
        $this->khanzaCariUser = '';
        $this->nrp = '';
        $this->nama = '';
        $this->checkedUsers = [];
    }
}
