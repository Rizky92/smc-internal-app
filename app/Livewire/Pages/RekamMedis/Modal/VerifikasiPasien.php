<?php

namespace App\Livewire\Pages\RekamMedis\Modal;

use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Services\SatuSehatService;
use Illuminate\View\View;
use Livewire\Component;

class VerifikasiPasien extends Component
{
    use DeferredModal;
    use Filterable;
    use FlashComponent;

    /** @var int */
    public $userId;

    /** @var string */
    public $noKtp;

    /** @var string */
    public $tglLahir;

    /** @var string */
    public $name;

    public $nameVerified = false;

    public $birthDateVerified = false;

    public $noKtpVerified = false;

    /** @var mixed */
    protected $listeners = [
        'prepare',
        'verifikasi-pasien.hide-modal' => 'hideModal',
        'verifikasi-pasien.show-modal' => 'showModal',
    ];

    protected function rules(): array
    {
        return [
            'noKtp'    => ['required', 'string'],
            'tglLahir' => ['required', 'date'],
            'name'     => ['required', 'string'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): View
    {
        return view('livewire.pages.rekam-medis.modal.verifikasi-pasien');
    }

    public function prepare(array $options): void
    {
        $this->userId   = $options['userId'] ?? -1;
        $this->noKtp    = $options['noKtp'] ?? '';
        $this->tglLahir = $options['tglLahir'] ?? '';
        $this->name     = $options['name'] ?? '';
    }

    public function isUpdating(): bool
    {
        return $this->userId !== -1;
    }

    public function verifikasi(): void
    {
        if (user()->cannot('verifikasi-pasien')) {
            $this->flashError('Anda tidak memiliki akses untuk melakukan verifikasi pasien.');
            $this->dispatchBrowserEvent('data-denied');
            return;
        }
    
        $this->validate();
    
        try {
            $satusehat = new SatuSehatService();
    
            $pasien = $satusehat->cekPasienByNikNameBirthDate(
                $this->name,
                $this->tglLahir,
                $this->noKtp
            );
    
            if (empty($pasien['entry'])) {
                $this->flashError('Pasien tidak ditemukan di SATUSEHAT.');
                return;
            }
    
            $dataPasien = $pasien['entry'][0]['resource'];
    
            // Verifikasi tiap field
            $this->nameVerified = strtolower(trim($dataPasien['name'][0]['text'] ?? '')) === strtolower(trim($this->name));
            $this->birthDateVerified = ($dataPasien['birthDate'] ?? '') === $this->tglLahir;
            $this->noKtpVerified = collect($dataPasien['identifier'] ?? [])
                ->where('system', 'https://fhir.kemkes.go.id/id/nik')
                ->first()['value'] === $this->noKtp;
    
        } catch (\Exception $e) {
            $this->nameVerified = false;
            $this->birthDateVerified = false;
            $this->noKtpVerified = false;
        }
    }    

    protected function defaultValues(): void
    {
        $this->userId   = -1;
        $this->noKtp    = '';
        $this->tglLahir = '';
        $this->name     = '';
    }
}
