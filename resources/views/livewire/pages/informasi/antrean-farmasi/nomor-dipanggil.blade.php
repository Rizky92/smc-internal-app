<section id="nomor-dipanggil" class="af-called" data-nomor="{{ $this->antrean->nomor ?? '' }}" wire:poll.10s>
    <span class="af-called-overline">{{ __('Nomor antrean dipanggil') }}</span>
    @if ($this->antrean)
        <span class="af-called-number">{{ $this->antrean->nomor }}</span>
        <span class="af-called-action">
            <span class="af-called-cta">{{ __('Silakan ke Loket Farmasi') }}</span>
            <span class="af-called-time">
                {{ __('Dipanggil pukul :jam', ['jam' => $this->antrean->jam_panggil_singkat]) }}
            </span>
        </span>
    @else
        <span class="af-called-empty">{{ __('Belum ada panggilan') }}</span>
    @endif
</section>
