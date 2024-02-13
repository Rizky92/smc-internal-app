<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Keuangan\Jurnal\PostingJurnal;
use App\Livewire\Pages\Keuangan\Jurnal\Modal\InputPostingJurnal;

class PrintLayoutController
{
    public function printPage(Request $request, $no_jurnal)
    {
        $postingJurnal = PostingJurnal::with(['jurnal', 'detail.rekening'])
            ->where('no_jurnal', $no_jurnal)
            ->first();
    
        return view('print-layout', ['postingJurnal' => $postingJurnal]);
    }
    
    
}
