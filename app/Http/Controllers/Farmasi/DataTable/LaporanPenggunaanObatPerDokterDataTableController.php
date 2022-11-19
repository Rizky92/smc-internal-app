<?php

namespace App\Http\Controllers\Farmasi\DataTable;

use App\Http\Controllers\Controller;
use App\Http\Resources\Farmasi\DataTable\LaporanPenggunaanObatPerDokterResource;
use App\Resep;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class LaporanPenggunaanObatPerDokterDataTableController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        dd($request);

        $dateMin = Carbon::parse($request->get('datemin'))->format('Y-m-d');
        $dateMax = Carbon::parse($request->get('datemax'))->format('Y-m-d');

        // $searchQuery = $request->get('search');

        $penggunaanObatPerDokter = Resep::query()
            ->penggunaanObatPerDokter($dateMin, $dateMax)
            // ->when(! is_null($searchQuery), function (Builder $query) use ($searchQuery) {
            //     return $query->search($searchQuery);
            // })
            ->orderBy('databarang.kode_brng');

        return LaporanPenggunaanObatPerDokterResource::collection($penggunaanObatPerDokter->paginate())
            ->additional([
                'recordsTotal' => $penggunaanObatPerDokter->count()
            ]);
    }
}
