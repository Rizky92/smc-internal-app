<?php

namespace App\Http\Controllers\RekamMedis\Excel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelWriter;

class ExportLaporanRekamMedisController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $now = now()->format('u');
        $excel = SimpleExcelWriter::create("storage/app/public/excel/export/{$now}_rekammedis.xlsx");
    }
}
