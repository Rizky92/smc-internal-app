<?php

namespace App\Livewire\Pages\Antrean;

use App\Models\Antrian\AntriPoli;
use App\Models\Aplikasi\Pintu;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Livewire\Component;

class AntreanDiPanggil extends Component
{
    public string $kd_pintu;

    public bool $isCalling = false;

    public ?object $currentCallingPatient = null;

    protected $listeners = ['updateStatusAfterCall', 'forceRefresh'];

    public function mount()
    {
        $this->cleanupExpiredCache();
        $this->initializeState();
    }

    private function getCacheKey()
    {
        return "antrean_calling_{$this->kd_pintu}";
    }

    private function getTimestampCacheKey()
    {
        return "antrean_calling_timestamp_{$this->kd_pintu}";
    }

    private function cleanupExpiredCache(): void
    {
        $timestampKey = $this->getTimestampCacheKey();
        $lastCallTime = Cache::get($timestampKey);
        
        // Jika sudah lewat 2 menit, bersihkan cache
        if ($lastCallTime && (time() - $lastCallTime) > 120) {
            $this->removeCallingPatientFromCache();
        }
    }

    private function initializeState(): void
    {
        $callingPatient = $this->getCallingPatientFromCache();
        if ($callingPatient) {
            $this->isCalling = true;
            $this->currentCallingPatient = $callingPatient;
        } else {
            $this->isCalling = false;
            $this->currentCallingPatient = null;
        }
    }

    private function getCallingPatientFromCache(): ?object
    {
        return Cache::get($this->getCacheKey());
    }

    private function storeCallingPatientToCache(object $patient, int $ttl = 120): void
    {
        Cache::put($this->getCacheKey(), $patient, $ttl); // Kurangi TTL jadi 2 menit
        Cache::put($this->getTimestampCacheKey(), time(), $ttl);
    }

    private function removeCallingPatientFromCache(): void
    {
        Cache::forget($this->getCacheKey());
        Cache::forget($this->getTimestampCacheKey());
    }

    private function isCurrentlyCallingFromCache(): bool
    {
        return Cache::has($this->getCacheKey());
    }

    public function getAntreanDiPanggilProperty()
    {
        // Cek cache terlebih dahulu
        $callingPatient = $this->getCallingPatientFromCache();
        if ($callingPatient) {
            $this->isCalling = true;
            $this->currentCallingPatient = $callingPatient;
            return $callingPatient;
        }

        // Jika tidak ada di cache, ambil dari database
        $this->isCalling = false;
        $this->currentCallingPatient = null;
        return $this->getNextPatientFromDatabase();
    }

    public function getNextPatientFromDatabase()
    {
        $db = \DB::connection('mysql_sik')->getDatabaseName();

        $antripoli = \DB::raw("{$db}.antripoli antripoli");

        return Pintu::query()
            ->antrianPerPintu($this->kd_pintu)
            ->selectRaw('antripoli.status')
            ->leftJoin($antripoli, fn (JoinClause $join) => $join
                ->on('registrasi.no_rawat', '=', 'antripoli.no_rawat')
                ->on('poliklinik.kd_poli', '=', 'antripoli.kd_poli')
                ->on('dokter.kd_dokter', '=', 'antripoli.kd_dokter')
            )
            ->where('antripoli.status', '1')
            ->first();
    }

    public function call(): void
    {
        // Bersihkan cache expired sebelum cek
        $this->cleanupExpiredCache();
        
        if ($this->isCurrentlyCallingFromCache()) {
            return;
        }

        $antrean = $this->getNextPatientFromDatabase();

        if ($antrean && $antrean->status === '1') {
            $this->storeCallingPatientToCache($antrean);
            $this->isCalling = true;
            $this->currentCallingPatient = $antrean;
            
            $this->dispatchBrowserEvent('play-voice', [
                'no_reg'    => $antrean->no_reg,
                'nm_pasien' => $antrean->nm_pasien,
                'nm_pintu'  => $antrean->nm_pintu,
                'cache_key' => $this->getCacheKey(),
                'patient_data' => [
                    'no_rawat' => $antrean->no_rawat,
                    'kd_poli'  => $antrean->kd_poli,
                    'kd_dokter' => $antrean->kd_dokter,
                ]
            ]);
        }
    }

    public function updateStatusAfterCall($cacheKey = null, $patientData = null): void
    {
        if ($cacheKey && $cacheKey !== $this->getCacheKey()) {
            return;
        }

        $callingPatient = $this->getCallingPatientFromCache();

        if ($callingPatient) {
            // Update status di database
            AntriPoli::query()
                ->where('no_rawat', $callingPatient->no_rawat)
                ->where('kd_poli', $callingPatient->kd_poli)
                ->where('kd_dokter', $callingPatient->kd_dokter)
                ->update(['status' => '0']);

            // Bersihkan cache
            $this->removeCallingPatientFromCache();
        }

        // Reset state
        $this->isCalling = false;
        $this->currentCallingPatient = null;
        
        // Refresh component tanpa reload page
        $this->render();
    }

    public function clearCallingCache()
    {
        $this->removeCallingPatientFromCache();
        $this->isCalling = false;
        $this->currentCallingPatient = null;
    }
    public function forceRefresh()
    {
        $this->cleanupExpiredCache();
        $this->initializeState();
    }

    public function render(): View
    {
        return view('livewire.pages.antrean.antrean-di-panggil');
    }
}
