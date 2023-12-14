<?php
namespace App\Http\Controllers;

use App\Models\Perawatan\Kamar;
use App\Models\Bangsal;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KamarController
{
    public function getInformasiKamarProperty()
    {
        return Bangsal::activeWithKamar()
            ->distinct()
            ->orderBy('nm_bangsal')
            ->orderBy('kelas')
            ->paginate(200);
    }

    public function countOccupiedRooms($kdBangsal): int
    {
        return Kamar::where('statusdata', '1')
            ->where('kd_bangsal', $kdBangsal)
            ->where('status', 'ISI')
            ->count();
    }

    public function countEmptyRooms($kdBangsal): int
    {
        return Kamar::where('statusdata', '1')
            ->where('kd_bangsal', $kdBangsal)
            ->where('status', 'KOSONG')
            ->count();
    }

    public function index(Request $request): View
    {
        $informasiKamar = $this->getInformasiKamarProperty();

        return view('informasi-kamar', compact('informasiKamar'));
    }
}
