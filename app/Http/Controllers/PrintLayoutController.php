<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Keuangan\Rekening;
use App\Models\Keuangan\Jurnal\PostingJurnal;
use App\Livewire\Pages\Keuangan\Jurnal\Modal\InputPostingJurnal;

class PrintLayoutController
{
    public function index(Request $request)
    {
        $rekeningData = Rekening::all()->pluck('nm_rek', 'kd_rek');

        $jurnalSementara = $request->input('jurnalSementara', []);

        $jurnalSementara = json_decode($jurnalSementara, true);
    
        return view('print-layout',  ['rekeningData' => $rekeningData, 'jurnalSementara' => $jurnalSementara]);
    }
    
    
}
