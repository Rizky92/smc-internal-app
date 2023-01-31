<?php

namespace App\Http\Livewire\User;

use App\Models\Aplikasi\User;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\View\Components\BaseLayout;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ManajemenUser extends Component
{
    use WithPagination, FlashComponent, Filterable, LiveTable;

    protected function queryString(): array
    {
        return [
            'cari' => ['except' => ''],
            'perpage' => ['except' => 25],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function getUsersProperty()
    {
        return User::query()
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns, [
                'jbtn' => DB::raw("coalesce(jabatan.nm_jbtn, spesialis.nm_sps, pegawai.jbtn)"),
                'jenis' => DB::raw("(case when petugas.nip is not null then 'Petugas' when dokter.kd_dokter is not null then 'Dokter' else '-' end)"),
            ])
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.user.manajemen-user')
            ->layout(BaseLayout::class, ['title' => 'Manajemen User']);
    }

    protected function defaultValues()
    {
        $this->cari = '';
        $this->perpage = 25;
    }

    public function impersonateAsUser(string $nrp = '')
    {
        if (empty($nrp)) {
            return;
        }

        if (! auth()->user()->hasRole(config('permission.superadmin_name'))) {
            return;
        }

        auth()->user()->impersonate(User::findByNRP($nrp));

        $this->flashInfo("Anda sekarang sedang login sebagai {$nrp}");
        
        return redirect('admin/');
    }
}
