<?php

namespace App\Http\Livewire\HakAkses\Khanza;

use App\Models\Aplikasi\HakAkses;
use Illuminate\View\View;
use Livewire\Component;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ModalHakAksesBaru extends Component
{
    /** @var string */
    public $namaField;

    /** @var string */
    public $judulMenu;

    // protected $rules = [
    //     'namaField' => ['required', 'string', 'max:255', Rule::unique('khanza_mapping_akses', 'nama_field')->ignore($this->namaField, 'nama_field')],
    //     'judulMenu' => ['required', 'string', 'max:255', Rule::unique('khanza_mapping_akses', 'nama_field')->ignore($this->namaField, 'nama_field')],
    // ];

    public function render(): View
    {
        return view('livewire.hak-akses.khanza.modal-hak-akses-baru');
    }

    public function save(): void
    {
        HakAkses::updateOrCreate(['nama_field' => $this->namaField], ['judul_menu' => $this->judulMenu]);
    }
}
