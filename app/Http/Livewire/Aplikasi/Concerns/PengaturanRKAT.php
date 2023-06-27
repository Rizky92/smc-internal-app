<?php

namespace App\Http\Livewire\Aplikasi\Concerns;

use App\Models\Keuangan\RKAT\AnggaranBidang;
use App\Rules\DoesntExist;
use App\Settings\RKATSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Fluent;

trait PengaturanRKAT
{
    /** @var string */
    public $tahunRKAT;

    /** @var string */
    public $tglAwalBatasInputRKAT;

    /** @var string */
    public $tglAkhirBatasInputRKAT;

    /** @var string */
    public $tglAwalPenetapanRKAT;

    /** @var string */
    public $tglAkhirPenetapanRKAT;

    public function mountPengaturanRKAT(): void
    {
        $this->defaultValuesPengaturanRKAT();
    }

    public function getDataTahunProperty(): array
    {
        $firstRKAT = AnggaranBidang::query()
            ->orderBy('tahun', 'asc')
            ->limit(1)
            ->value('tahun');

        return collect(range($firstRKAT, (int) now()->addYears(5)->format('Y'), 1))
            ->mapWithKeys(fn (int $v, $_): array => [$v => $v])
            ->all();
    }

    public function updatePengaturanRKAT(): void
    {
        if (!Auth::user()->hasRole(config('permission.superadmin_name'))) {
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');
            $this->dispatchBrowserEvent('pengaturan-rkat.data-denied');

            return;
        }

        $validated = $this->validate([
            'tahunRKAT' => ['required', new DoesntExist],
            'tglAwalPenetapanRKAT' => ['required', 'date'],
            'tglAkhirPenetapanRKAT' => ['required', 'date'],
            'tglAwalBatasInputRKAT' => ['required', 'date'],
            'tglAkhirBatasInputRKAT' => ['required', 'date'],
        ], [
            'tahunRKAT.' . DoesntExist::class => 'Tahun RKAT tersebut sudah ada anggarannya!',
        ]);

        $validated = new Fluent($validated);

        $settings = app(RKATSettings::class);

        $settings->tahun = $validated->tahunRKAT;
        $settings->batas_penetapan_awal = carbon($validated->tglAwalPenetapanRKAT);
        $settings->batas_penetapan_akhir = carbon($validated->tglAkhirPenetapanRKAT);
        $settings->batas_input_awal = carbon($validated->tglAwalInputRKAT);
        $settings->batas_input_akhir = carbon($validated->tglAkhirInputRKAT);

        $settings->save();

        $this->emit('flash.success', 'Pengaturan RKAT berhasil diupdate!');
        $this->dispatchBrowserEvent('pengaturan-rkat.data-saved');
    }

    protected function defaultValuesPengaturanRKAT(): void
    {
        $settings = app(RKATSettings::class);

        $this->tahunRKAT = $settings->tahun;
        $this->tglAwalPenetapanRKAT = $settings->batas_penetapan_awal->format('Y-m-d');
        $this->tglAkhirPenetapanRKAT = $settings->batas_penetapan_akhir->format('Y-m-d');
        $this->tglAwalInputRKAT = $settings->batas_penetapan_awal->format('Y-m-d');
        $this->tglAkhirInputRKAT = $settings->batas_penetapan_akhir->format('Y-m-d');
    }
}