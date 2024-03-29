<?php

namespace App\Livewire\Concerns;

use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

trait MenuTracker
{
    public function mountMenuTracker(): void
    {
        $this->recordVisitor();
    }

    private function currentRoute(): ?string
    {
        return Route::currentRouteName();
    }

    private function menuPath(): string
    {
        return Breadcrumbs::generate($this->currentRoute())
            ->map(fn (object $value): string => $value->title)
            ->join(' / ');
    }

    private function recordVisitor(): void
    {
        if (app('impersonate')->isImpersonating() || app()->runningUnitTests()) {
            return;
        }

        $breadcrumbs = $this->menuPath();
        $route = $this->currentRoute();

        DB::connection('mysql_smc')
            ->table('trackermenu')
            ->insert([
                'waktu'       => now(),
                'breadcrumbs' => $breadcrumbs,
                'route_name'  => $route,
                'user_id'     => str(user()->nik)->trim()->value(),
                'ip_address'  => request()->ip(),
            ]);
    }
}
