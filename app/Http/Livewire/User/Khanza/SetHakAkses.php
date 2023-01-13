<?php

namespace App\Http\Livewire\User\Khanza;

use App\Models\Aplikasi\MappingAksesKhanza;
use App\Models\Aplikasi\User;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class SetHakAkses extends Component
{
    public $deferLoading;

    public $nrp;

    public $nama;

    public $cari;

    public $checkedHakAkses;

    protected $listeners = [
        'khanza.open-modal' => 'openModal',
        'khanza.prepare-user' => 'prepareUser',
        'khanza.save' => 'syncHakAkses',
        'khanza.reset-modal' => 'defaultValues',
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

    public function getHakAksesKhanzaProperty()
    {
        return MappingAksesKhanza::query()
            ->when(!$this->deferLoading, function (Builder $query) {
                return $query
                    ->where('nama_field', 'like', "%{$this->cari}%")
                    ->orWhere('judul_menu', 'like', "%{$this->cari}%")
                    ->orWhereIn('nama_field', $this->checkedHakAkses);
            })
            ->pluck('judul_menu', 'nama_field');
    }

    public function render()
    {
        return view('livewire.user.khanza.set-hak-akses');
    }

    public function openModal()
    {
        $this->deferLoading = false;

        $user = User::rawFindByNRP($this->nrp);

        $this->checkedHakAkses = $this->hakAksesKhanza->keys()->filter(function ($field) use ($user) {
            return $user->getAttribute($field) == 'true';
        })->toArray();
    }

    public function prepareUser(string $nrp = '', string $nama = '')
    {
        $this->nrp = $nrp;
        $this->nama = $nama;
    }

    public function syncHakAkses()
    {
        if (!auth()->user()->hasRole(config('permission.superadmin_name'))) {
            $this->emit('flash', [
                'flash.type' => 'danger',
                'flash.message' => 'Anda tidak diizinkan untuk melakukan tindakan ini!',
            ]);

            return;
        }

        $user = User::rawFindByNRP($this->nrp);

        tracker_start();

        foreach ($this->checkedHakAkses as $hakAkses) {
            $user->setAttribute($hakAkses, 'true');
        }

        $falsyHakAkses = $this->hakAksesKhanza->reject(function ($value, $key) {
            return in_array($key, $this->checkedHakAkses);
        });

        foreach ($falsyHakAkses as $field => $judul) {
            $user->setAttribute($field, 'false');
        }

        $user->save();

        tracker_end();

        $this->emit('flash', [
            'flash.type' => 'success',
            'flash.message' => "Hak akses SIMRS Khanza untuk user {$this->nrp} telah diupdate!",
        ]);

        $this->defaultValues();
    }

    public function defaultValues()
    {
        $this->deferLoading = true;
        $this->nrp = '';
        $this->nama = '';
        $this->cari = '';
        $this->checkedHakAkses = [];
    }
}
