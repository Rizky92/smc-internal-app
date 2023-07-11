<?php

namespace App\Support\Traits\Livewire;

trait FlashComponent
{
    public function initializeFlashComponent(): void
    {
        $this->listeners = array_merge($this->listeners, [
            'flash',
            'flash.success' => 'flashSuccess',
            'flash.info'    => 'flashInfo',
            'flash.warning' => 'flashWarning',
            'flash.error'   => 'flashError',
        ]);
    }

    /**
     * @param  array<string, string> $flash
     */
    public function flash(array $flash): void
    {
        foreach ($flash as $key => $message) {
            session()->flash($key, $message);
        }
    }

    public function flashSuccess(string $message = "Sukses melakukan perubahan data"): void
    {
        $this->flash([
            'flash.message' => $message,
            'flash.type'    => 'success',
            'flash.icon'    => 'check-circle',
        ]);
    }

    public function flashInfo(string $message = "Terjadi sesuatu!"): void
    {
        $this->flash([
            'flash.message' => $message,
            'flash.type'    => 'dark',
            'flash.icon'    => 'info-circle',
        ]);
    }

    public function flashWarning(string $message = "Terjadi sesuatu!"): void
    {
        $this->flash([
            'flash.message' => $message,
            'flash.type'    => 'warning',
            'flash.icon'    => 'exclamation-triangle',
        ]);
    }

    public function flashError(string $message = "Anda tidak diizinkan untuk melakukan aksi ini!"): void
    {
        $this->flash([
            'flash.message' => $message,
            'flash.type'    => 'danger',
            'flash.icon'    => 'times-circle',
        ]);
    }
}