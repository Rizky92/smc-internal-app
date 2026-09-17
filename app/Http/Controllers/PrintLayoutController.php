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

        // The journal arrives as a JSON string in the query, so the parameter has
        // to survive everything a URL can do to it. It used to default to [] and
        // hand that straight to json_decode(), which only takes a string, so
        // simply opening the page without it was a 500 — as was repeating the
        // parameter, since PHP then makes an array of it.
        $raw = $request->input('jurnalSementara');

        $decoded = is_string($raw) ? json_decode($raw, true) : null;

        $jurnalSementara = is_array($decoded) ? $decoded : [];

        return view('print-layout', ['rekeningData' => $rekeningData, 'jurnalSementara' => $jurnalSementara]);
    }
}
