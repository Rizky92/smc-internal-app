<?php

namespace App\Livewire\Pages\Admin;

use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class JobCleaner extends Component
{
    use FlashComponent;
    use MenuTracker;

    public function getJobsProperty(): int
    {
        return DB::table('jobs')->count();
    }

    public function cleanJobs(): void
    {
        \Artisan::call('queue:clear');

        $this->flashSuccess('Jobs berhasil dibersihkan');
    }

    public function render(): View
    {
        return view('livewire.pages.admin.job-cleaner')
            ->layout(BaseLayout::class, ['title' => 'Job Cleaner']);
    }
}
