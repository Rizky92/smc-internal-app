<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Perawatan\RawatInap;
use App\Models\RekamMedis\Penjamin;
use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

class DPJPPiutangRanap extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    /** @var string */
    public $status;

    /** @var string */
    public $jenisBayar;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    protected function queryString(): array
    {
        return [
            'status'     => ['except' => ''],
            'jenisBayar' => ['except' => '', 'as' => 'kd_pj'],
            'tglAwal'    => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir'   => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getPenjaminProperty(): Collection
    {
        return Penjamin::query()
            ->where('status', '1')
            ->pluck('png_jawab', 'kd_pj');
    }

    /**
     * @return \Illuminate\Contracts\Pagination\Paginator|array<empty, empty>
     */
    public function getPiutangRanapProperty()
    {
        return $this->isDeferred
            ? []
            : RawatInap::query()
            ->piutangRanap($this->tglAwal, $this->tglAkhir, $this->status, $this->jenisBayar)
            ->with([
                'dpjpRanap',
                'billing' => fn (Builder $q): Builder => $q->totalBillingan(),
            ])
            ->withSum('cicilanPiutang as dibayar', 'besar_cicilan')
            ->sortWithColumns($this->sortColumns, [
                'perujuk'      => DB::raw("ifnull(rujuk_masuk.perujuk, '-')"),
                'waktu_keluar' => DB::raw("timestamp(kamar_inap.tgl_keluar, kamar_inap.jam_keluar)"),
                'ruangan'      => DB::raw("concat(kamar.kd_kamar, ' ', bangsal.nm_bangsal)"),
            ])
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.keuangan.dpjp-piutang-ranap')
            ->layout(BaseLayout::class, ['title' => 'DPJP Piutang Ranap']);
    }

    protected function defaultValues(): void
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
        $this->status = '';
        $this->jenisBayar = '';
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    public function mapData(): Collection
    {
        return RawatInap::query()
            ->piutangRanap($this->tglAwal, $this->tglAkhir, $this->status, $this->jenisBayar)
            ->with([
                'dpjpRanap',
                'billing' => fn (Builder $q): Builder => $q->totalBillingan(),
            ])
            ->withSum('cicilanPiutang as dibayar', 'besar_cicilan')
            ->get()
            ->map(function (RawatInap $ranap): array {
                $kategoriBilling = $ranap
                    ->billing
                    ->pluck('total', 'status')
                    ->mapWithKeys(fn ($total, $status): array => [Str::snake($status) => $total]);

                $billingTindakan = $kategoriBilling
                    ->only([
                        'ranap_dokter',
                        'ranap_dokter_paramedis',
                        'ranap_paramedis',
                        'ralan_dokter',
                        'ralan_dokter_paramedis',
                        'ralan_paramedis',
                    ])
                    ->sum();

                $total = $kategoriBilling->sum();

                $sisa = $total - $ranap->uangmuka - $ranap->dibayar;

                return [
                    'waktu_keluar'  => $ranap->waktu_keluar,
                    'no_nota'       => $ranap->no_nota,
                    'no_rkm_medis'  => $ranap->no_rkm_medis,
                    'nm_pasien'     => $ranap->nm_pasien,
                    'png_jawab'     => $ranap->png_jawab,
                    'perujuk'       => $ranap->perujuk,
                    'registrasi'    => (float) $kategoriBilling->get('registrasi'),
                    'tindakan'      => $billingTindakan,
                    'obat'          => (float) $kategoriBilling->get('obat'),
                    'retur_obat'    => (float) $kategoriBilling->get('retur_obat'),
                    'resep_pulang'  => (float) $kategoriBilling->get('resep_pulang'),
                    'laborat'       => (float) $kategoriBilling->get('laborat'),
                    'radiologi'     => (float) $kategoriBilling->get('radiologi'),
                    'potongan'      => (float) $kategoriBilling->get('potongan'),
                    'tambahan'      => (float) $kategoriBilling->get('tambahan'),
                    'kamar_service' => (float) $kategoriBilling->only(['kamar', 'service'])->sum(),
                    'operasi'       => (float) $kategoriBilling->get('operasi'),
                    'harian'        => (float) $kategoriBilling->get('harian'),
                    'total'         => (float) $total,
                    'uangmuka'      => (float) $ranap->uangmuka,
                    'dibayar'       => (float) $ranap->dibayar,
                    'sisa'          => (float) $sisa,
                    'ruangan'       => $ranap->ruangan,
                    'dokter_pj'     => $ranap->dpjpRanap->implode('nm_dokter', ', '),
                ];
            });
    }

    protected function dataPerSheet(): array
    {
        return [
            $this->mapData(),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            "Waktu Keluar",
            "No. Nota",
            "No. RM",
            "Pasien",
            "Jenis Bayar",
            "Asal Rujukan",
            "Registrasi",
            "Tindakan",
            "Obat + Embl. Tsl.",
            "Retur Obat",
            "Resep Pulang",
            "Laboratorium",
            "Radiologi",
            "Kamar + Layanan",
            "Operasi",
            "Harian",
            "Tambahan",
            "Potongan",
            "Uang Muka",
            "Total",
            "Dibayar",
            "Sisa",
            "Kamar",
            "Dokter P.J.",
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Laporan Piutang Rawat Inap per ' . now()->translatedFormat('d F Y'),
            'Periode ' . carbon($this->tglAwal)->translatedFormat('d F Y') . ' s.d. ' . carbon($this->tglAkhir)->translatedFormat('d F Y'),
        ];
    }
}
