<?php

namespace App\Http\Livewire;

use Illuminate\View\View;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

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
        return view('livewire.job-status');
    }
}
