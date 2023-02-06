<?php

namespace App\Support\Traits\Livewire;

trait FlashComponent
{
    public function initializeFlashComponent()
    {
        $this->listeners = array_merge($this->listeners, [
            'flash',
            'flash.success' => 'flashSuccess',
            'flash.info' => 'flashInfo',
            'flash.warning' => 'flashWarning',
            'flash.error' => 'flashError',
        ]);
    }

    public function flash(array $flash)
    {
        foreach ($flash as $key => $message) {
            session()->flash($key, $message);
        }
    }

    public function flashSuccess(string $message)
    {
        $this->emit('flash', [
            'flash.type' => 'success',
            'flash.message' => $message,
        ]);
    }

    public function flashInfo(string $message)
    {
        $this->emit('flash', [
            'flash.type' => 'dark',
            'flash.message' => $message,
        ]);
    }

    public function flashWarning(string $message)
    {
        $this->emit('flash', [
            'flash.type' => 'warning',
            'flash.message' => $message,
        ]);
    }

    public function flashError(string $message)
    {
        $this->emit('flash', [
            'flash.type' => 'danger',
            'flash.message' => $message,
        ]);
    }
}