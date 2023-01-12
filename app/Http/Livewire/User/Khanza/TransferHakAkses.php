<?php

namespace App\Http\Livewire\User\Khanza;

use App\Models\Aplikasi\MappingAksesKhanza;
use App\Models\Aplikasi\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TransferHakAkses extends Component
{
    public $deferLoading = true;

    public $nrp;

    public $nama;

    public $khanzaCheckedUsers;

    public $cariUser;

    protected $listeners = [
        'khanzaPrepareTransfer',
        'khanzaTransferHakAkses',
    ];

    protected function queryString()
    {
        return [
            'cariUser' => [
                'except' => '',
                'as' => 'qu',
            ],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function getKhanzaHakAksesTersediaProperty()
    {
        return MappingAksesKhanza::pluck('judul_menu', 'nama_field');
    }

    public function getAvailableUsersProperty()
    {
        return User::query()
            ->where('petugas.nip', '!=', $this->nrp)
            ->search($this->cariUser)
            ->when(!empty($this->khanzaCheckedUsers), function (Builder $query) {
                return $query->orWhereIn('petugas.nip', $this->khanzaCheckedUsers);
            })
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
            ->whereIn(DB::raw('AES_DECRYPT(user.id_user, "nur")'), $this->khanzaCheckedUsers)
            ->get();

        tracker_start();

        foreach ($permittedUsers as $checkedUser) {
            foreach ($this->khanzaHakAksesTersedia as $kolom => $hakAkses) {
                $checkedUser->setAttribute($kolom, $currentUser->getAttribute($kolom));
            }

            $checkedUser->save();
        }

        tracker_end();

        $this->emit('flash', [
            'flash.type' => 'success',
            'flash.message' => 'Transfer hak akses berhasil!',
        ]);
    }

    public function resetModal()
    {
        $this->defaultValues();

        $this->dispatchBrowserEvent('hide.bs.modal');
    }

    private function defaultValues()
    {
        $this->cariUser = '';
        $this->nrp = '';
        $this->nama = '';
        $this->khanzaCheckedUsers = [];
    }
}
