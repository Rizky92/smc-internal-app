<?php
namespace App\Http\Controllers;

use App\Models\Perawatan\Kamar;
use App\Models\Bangsal;
// use App\View\Components\BaseLayout;
use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;


class KamarController extends Component
{
    public function getInformasiKamarProperty(): Paginator
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

    return view('informasi-kamar', compact('informasiKamar'))
        ->layout(BaseLayout::class, ['title' => 'Informasi Kamar']);
}
}
