<?php

namespace App\Support\Traits\Livewire;

use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Str;

trait MenuTracker
{
    public function mountMenuTracker()
    {
        $this->recordVisitor();
    }

    private function currentRoute(): string
    {
        return Route::currentRouteName();
    }

    private function menuPath(): string
    {
        return Breadcrumbs::generate($this->currentRoute())
            ->map(fn ($value) => $value->title)
            ->join(' / ');
    }

    private function recordVisitor(): void
    {
        // Jangan melakukan tracking ketika sedang impersonasi user
        if (app('impersonate')->isImpersonating()) {
            return;
        }

        $breadcrumbs = $this->menuPath();
        $route = $this->currentRoute();

        DB::connection('mysql_smc')
            ->table('trackermenu')
            ->insert([
                'waktu' => now(),
                'breadcrumbs' => $breadcrumbs,
                'route_name' => $route,
                'user_id' => (string) Str::of(auth()->user()->nik)->trim(),
                'ip_address' => request()->ip(),
            ]);
    }
}