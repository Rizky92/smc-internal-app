<?php

namespace App\Livewire\Components;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class JobStatus extends Component
{
    /** @var bool */
    public $pollForJob;

    public function mount(): void
    {
        $this->pollForJob = false;
    }

    public function checkJobStatus(): bool
    {
        return DB::table('failed_jobs')
            ->get()
            ->isEmpty();
    }

    public function render(): View
    {
        return view('livewire.components.job-status');
    }
}
