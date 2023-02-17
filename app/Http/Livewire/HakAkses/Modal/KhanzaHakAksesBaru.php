<?php

namespace App\Http\Livewire\HakAkses\Modal;

use App\Models\Aplikasi\MappingAksesKhanza;
use App\View\Components\BaseLayout;
use Illuminate\Validation\Rule;
use Livewire\Component;

class KhanzaHakAksesBaru extends Component
{
    public $namaField;

    public $judulMenu;

    protected $rules = [
        'namaField' => ['required', 'string', 'max:255', Rule::unique('khanza_mapping_akses', 'nama_field')->ignore($this->namaField, 'nama_field')],
        'judulMenu' => ['required', 'string', 'max:255', Rule::unique('khanza_mapping_akses', 'nama_field')->ignore($this->namaField, 'nama_field')],
    ];

    public function render()
    {
        return view('livewire.hak-akses.modal.khanza-hak-akses-baru');
    }

    public function save()
    {
        MappingAksesKhanza::updateOrCreate(['nama_field' => $this->namaField], ['judul_menu' => $this->judulMenu]);
    }
}
