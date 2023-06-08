<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ErrorLayout extends Component
{
    public ?int $code;

    public ?string $title;

    public ?string $message;

    public string $level;

    /**
     * Create a new component instance.
     */
    public function __construct(?int $code, ?string $title, ?string $message, string $level = 'warning')
    {
        $this->code = $code;
        $this->title = $title;
        $this->message = $message;
        $this->level = $level;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('layouts.error');
    }
}
