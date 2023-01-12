<?php

namespace App\Http\Livewire\Perawatan\Utils;

use App\Models\Perawatan\RawatInap;
use Carbon\Exceptions\InvalidTypeException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;
use Livewire\Component;

class UpdateKamarPasien extends Component
{
    public $deferLoading;

    public $kamar;

    public $noRawat;

    public $tglMasuk;

    public $jamMasuk;

    public $hargaKamar;

    protected $listeners = [
        //
    ];

    public function mount()
    {
        $this->defaultValues();
    }

    public function render()
    {
        return view('livewire.perawatan.utils.update-kamar-pasien');
    }

    public function prepareData(string $noRawat, string $kamar, string $tglMasuk, string $jamMasuk, int $hargaKamar)
    {
        $this->noRawat = $noRawat;
        $this->kamar = $kamar;
        $this->tglMasuk = Carbon::parse($tglMasuk, 'Y-m-d');
        $this->jamMasuk = Carbon::parse($jamMasuk, 'H:i:s');
        $this->hargaKamar = $hargaKamar;
    }

    public function openModal()
    {
        $this->deferLoading = false;
    }

    public function updateHargaKamarPasien(int $hargaKamarBaru)
    {
        throw_if(!auth()->user()->can('perawatan.daftar-pasien-ranap.update-biaya-ranap'), AuthorizationException::class, 'Anda tidak diizinkan untuk melakukan tindakan ini!');

        $rawatInap = RawatInap::query()
            ->where('no_rawat', $this->noRawat)
            ->where('kd_kamar', $this->kamar)
            ->where('trf_kamar', $this->hargaKamar)
            ->where('tgl_masuk', $this->tglMasuk)
            ->where('jam_masuk', $this->jamMasuk)
            ->first();

        tracker_start();

        $rawatInap->trf_kamar = $hargaKamarBaru;
        $rawatInap->ttl_biaya = $rawatInap->lama * $hargaKamarBaru;

        $rawatInap->save();

        tracker_end();
    }

    public function closeModal()
    {
        $this->defaultValues();
    }

    private function defaultValues()
    {
        $this->deferLoading = true;
        $this->kamar = '';
        $this->noRawat = '';
        $this->tglMasuk = '';
        $this->jamMasuk = '';
        $this->hargaKamar = 0;
    }
}
