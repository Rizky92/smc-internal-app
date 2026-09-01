<?php

namespace App\Livewire\Concerns;

use Livewire\Attributes\On;

trait FlashComponent
{
    #[On('flash')]
    public function flash(array $flash): void
    {
        foreach ($flash as $key => $message) {
            session()->flash($key, $message);
        }
    }

    #[On('flash.success')]
    public function flashSuccess(string $message = 'Sukses melakukan perubahan data'): void
    {
        $this->flash([
            'flash.message' => $message,
            'flash.type'    => 'success',
            'flash.icon'    => 'check-circle',
        ]);
    }

    #[On('flash.info')]
    public function flashInfo(string $message = 'Terjadi sesuatu!'): void
    {
        $this->flash([
            'flash.message' => $message,
            'flash.type'    => 'dark',
            'flash.icon'    => 'info-circle',
        ]);
    }

    #[On('flash.warning')]
    public function flashWarning(string $message = 'Terjadi sesuatu!'): void
    {
        $this->flash([
            'flash.message' => $message,
            'flash.type'    => 'warning',
            'flash.icon'    => 'exclamation-triangle',
        ]);
    }

    #[On('flash.error')]
    public function flashError(string $message = 'Anda tidak diizinkan untuk melakukan aksi ini!'): void
    {
        $this->flash([
            'flash.message' => $message,
            'flash.type'    => 'danger',
            'flash.icon'    => 'times-circle',
        ]);
    }
}
