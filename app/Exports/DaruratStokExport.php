<?php

namespace App\Exports;

use App\DataBarang;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class DaruratStokExport implements FromView
{
    public function view(): View
    {
        return view('admin.farmasi.darurat-stok.table', [
            'daruratStok' => DataBarang::daruratStok()->get()
        ]);
    }
}
