<div class="row" style="height: 60%" wire:poll.2000ms.keep-alive="call">
    @if ($this->antreanDiPanggil)
        <div class="col">
            <div class="card card-outline card-success d-flex justify-content-center h-100" id="calling-card">
                <div class="card-header">
                    <h5 class="text-uppercase">antrean dipanggil</h5>
                </div>
                <div class="card-body">
                    <h5>{{ $this->antreanDiPanggil->nm_poli ?? '' }}</h5>
                    <h5 class="text-uppercase">
                        {{ $this->antreanDiPanggil->nm_dokter ?? '' }}
                    </h5>
                    <h1 class="text-danger" style="font-size: 9rem" id="calling-number">
                        {{ $this->antreanDiPanggil->no_reg ?? '' }}
                    </h1>
                    <h4>{{ $this->antreanDiPanggil->nm_pasien ?? '' }}</h4>
                </div>
            </div>
        </div>
    @elseif ($this->antreanSedangPeriksa)
        <div class="col">
            <div class="card card-outline card-info d-flex justify-content-center h-100">
                <div class="card-header">
                    <h5 class="text-uppercase">antrean dipanggil</h5>
                </div>
                <div class="card-body">
                    <h5>{{ $this->antreanSedangPeriksa->nm_poli ?? '' }}</h5>
                    <h5 class="text-uppercase">{{ $this->antreanSedangPeriksa->nm_dokter ?? '' }}</h5>
                    <h1 class="text-danger" style="font-size: 9rem">
                        {{ $this->antreanSedangPeriksa->no_reg ?? '' }}
                    </h1>
                    <h4>{{ $this->antreanSedangPeriksa->nm_pasien ?? '' }}</h4>
                </div>
            </div>
        </div>
    @else
        <div class="col">
            <div class="card card-outline card-success d-flex justify-content-center h-100">
                <div class="card-header">
                    <h5 class="text-uppercase">antrean dipanggil</h5>
                </div>
                <div class="card-body">
                    <div class="text-center"></div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('js')
    <script src="https://code.responsivevoice.org/responsivevoice.js?key=LHqTOngl"></script>
    <script>
        document.addEventListener('play-voice', (event) => {
            let text = `Nomor antrian ${event.detail.no_reg}, ${event.detail.nm_pasien.toLowerCase()}, silahkan menuju ke ${event.detail.nm_pintu.toLowerCase()}`;
            let card = document.getElementById('calling-card');
            let numberElement = document.getElementById('calling-number');

            if (window.blinkInterval) {
                clearInterval(window.blinkInterval);
            }

            if (card && numberElement) {
                window.blinkInterval = setInterval(() => {
                    card.classList.toggle('bg-success');
                    numberElement.classList.toggle('text-white');
                }, 1000);
            }

            responsiveVoice.speak(text, 'Indonesian Female', {
                rate: 0.7,
                onend: () => {
                    if (window.blinkInterval) {
                        clearInterval(window.blinkInterval);
                        window.blinkInterval = null;
                        card.classList.remove('bg-success');
                        numberElement.classList.remove('text-white');
                    }

                    Livewire.emit('updateStatus');
                },
                onerror: (e) => {
                    console.error('Speech error', e);

                    if (window.blinkInterval) {
                        clearInterval(window.blinkInterval);
                        window.blinkInterval = null;
                    }

                    Livewire.emit('call');
                },
            });
        });
    </script>
@endpush
