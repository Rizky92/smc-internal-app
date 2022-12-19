<?php

namespace App\Support\Traits\Livewire;

trait FlashComponent
{
    public function initializeFlashComponent()
    {
        $this->listeners = array_merge($this->listeners, [
            'flash',
        ]);
    }

    /**
     * Emit flash event to component.
     * 
     * @param  array<string,string> $flash
     * @return void
     */
    public function flash(array $flash)
    {
        foreach ($flash as $key => $message) {
            session()->flash($key, $message);
        }
    }

    /**
     * Emit success flash event to component.
     * 
     * @param  string $message
     * @return void
     */
    public function flashSuccess(string $message)
    {
        $this->emit('flash', [
            'type' => 'success',
            'message' => $message,
        ]);
    }

    /**
     * Emit info flash event to component.
     * 
     * @param  string $message
     * @return void
     */
    public function flashInfo(string $message)
    {
        $this->emit('flash', [
            'type' => 'info',
            'message' => $message,
        ]);
    }

    /**
     * Emit warning flash event to component.
     * 
     * @param  string $message
     * @return void
     */
    public function flashWarning(string $message)
    {
        $this->emit('flash', [
            'type' => 'warning',
            'message' => $message,
        ]);
    }

    /**
     * Emit error flash event to component.
     * 
     * @param  string $message
     * @return void
     */
    public function flashError(string $message)
    {
        $this->emit('flash', [
            'type' => 'danger',
            'message' => $message,
        ]);
    }
}