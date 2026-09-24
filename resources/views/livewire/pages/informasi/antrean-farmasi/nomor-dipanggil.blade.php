<div class="col-12" wire:poll.30s>
    <div class="card bg-primary mb-0" style="height: 15vh">
        <div class="card-body d-flex flex-column justify-content-center align-items-center p-2">
            <h4 class="font-weight-bold text-uppercase mb-0" style="font-size: 2.5vh">
                {{ __('Nomor Antrean Dipanggil') }}
            </h4>
            <span class="font-weight-bold" style="font-size: 8vh; line-height: 1">
                {{ $this->antrean->nomor ?? '–' }}
            </span>
            <span style="font-size: 2vh">
                {{ $this->antrean ? __('Dipanggil pukul :jam', ['jam' => $this->antrean->jam_panggil_singkat]) : __('Belum ada panggilan') }}
            </span>
        </div>
    </div>
</div>
