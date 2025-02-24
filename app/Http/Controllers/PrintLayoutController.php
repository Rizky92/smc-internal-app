<?php

namespace App\Http\Controllers;

use App\Models\Keuangan\Rekening;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrintLayoutController
{
    public function index(Request $request): View
    {
        $rekeningData = Rekening::all()->pluck('nm_rek', 'kd_rek');

        $jurnalSementara = $request->input('jurnalSementara', []);

        $jurnalSementara = json_decode($jurnalSementara, true);

        return view('print-layout', ['rekeningData' => $rekeningData, 'jurnalSementara' => $jurnalSementara]);
    }
}
