<div class="row" wire:poll.60s='call'>
    @if ($this->antreanDiPanggil && $this->antreanDiPanggil->status == '1')
        <div class="col">
            <div class="container-toast border shadow">
                <div class="bg-success">
                    <h2 class="text-white text-uppercase">antrean dipanggil</h2>
                </div>
                <div class="container-toast-content" style="height: 20rem;">
                    <div class="d-flex justify-content-center">
                        <div class="d-flex flex-column text-center">
                            <h5>{{ $this->antreanDiPanggil->nm_poli }}</h5>
                            <h5 class="text-uppercase">{{ $this->antreanDiPanggil->nm_dokter }}</h5>
                            <h1 class="text-danger" style="font-size: 9rem">{{ $this->antreanDiPanggil->no_reg }}</h1>
                            <h4>{{ $this->antreanDiPanggil->nm_pasien }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="col text-center">
            <div class="container-toast border shadow">
                <div class="bg-success">
                    <h3 class="text-white text-uppercase">antrean dipanggil</h3>
                </div>
                <div class="container-toast-content" style="height: 20rem;"></div>
            </div>
        </div>
    @endif
</div>
@push('js')
    <script src="https://code.responsivevoice.org/responsivevoice.js?key=OGPOBj1g"></script>
    <script>
        document.addEventListener('play-voice', (event) => {
            var textToSpeech = 'Nomor antrian ' + event.detail.no_reg + ', ' + event.detail.nm_pasien.toLowerCase() + ', silahkan menuju ke ' + event.detail.nm_poli.toLowerCase();
            var repeatCount = 0;

            function speakAndRepeat() {
                if (repeatCount < 3) {
                    responsiveVoice.speak(textToSpeech, "Indonesian Female", {
                        rate: 0.7,
                        onend: () => {
                            repeatCount++;
                            speakAndRepeat();
                        },
                    });
                } else {
                    Livewire.emit('updateStatusAfterCall');
                }
            }
            speakAndRepeat();
        });
    </script>
@endpush