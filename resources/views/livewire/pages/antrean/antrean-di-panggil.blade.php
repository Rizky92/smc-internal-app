<div class="row" style="height: 60%" @if (!$isCalling) wire:poll.keep-alive="call" @endif>
    @if ($currentPatient)
        <div class="col">
            <div class="card card-outline card-success d-flex justify-content-center h-100">
                <div class="card-header">
                    <h5 class="text-uppercase">antrean dipanggil</h5>
                </div>
                <div class="card-body">
                    <h5>{{ $currentPatient->nm_poli ?? '' }}</h5>
                    <h5 class="text-uppercase">
                        {{ $currentPatient->nm_dokter ?? '' }}
                    </h5>
                    <h1 class="text-danger" style="font-size: 9rem">
                        {{ $currentPatient->no_reg ?? '' }}
                    </h1>
                    <h4>{{ $currentPatient->nm_pasien ?? '' }}</h4>
                </div>
            </div>
        </div>
    @else
        <div class="col">
            <div class="card card-outline card-success d-flex justify-content-center h-100">
                <div class="card-header">
                    <h5 class="text-uppercase">antrean dipanggil</h5>
                </div>
                <div class="card-body"></div>
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
            let processesCompleted = 0; // Counter untuk tracking proses

            function checkAndRefresh() {
                processesCompleted++;
                if (processesCompleted === 2) {
                    // Tunggu kedua proses selesai
                    window.location.reload(); // Full page refresh
                }
            }

            function speakAndRepeat() {
                try {
                    if (repeatCount < 3) {
                        responsiveVoice.speak(textToSpeech, 'Indonesian Female', {
                            rate: 0.7,
                            onend: () => {
                                repeatCount++;
                                speakAndRepeat();
                            },
                        });
                    } else {
                        Livewire.emit('updateStatusAfterCall');
                        checkAndRefresh(); // Proses 1 selesai (voice)
                    }
                } catch (error) {
                    console.error('Error pada text-to-speech:', error);
                    Livewire.emit('updateStatusAfterCall');
                    checkAndRefresh(); // Skip voice dan langsung lanjut refresh
                }
            }

            speakAndRepeat();

            var card = document.querySelector('.card-outline.card-success');
            var numberElement = document.querySelector('.text-danger');
            if (card && numberElement) {
                var blinkInterval = setInterval(function () {
                    card.classList.toggle('bg-success');
                    numberElement.classList.toggle('text-white');
                }, 1000);

                setTimeout(function () {
                    clearInterval(blinkInterval);
                    card.classList.remove('bg-success');
                    numberElement.classList.remove('text-white');
                    checkAndRefresh(); // Proses 2 selesai (blink)
                }, 5000);
            }
        });

        let lastCallTime = Date.now();
        setInterval(() => {
            let currentTime = Date.now();
            let timeElapsed = (currentTime - lastCallTime) / 1000;

            if (timeElapsed > 30) {
                // Jika tidak ada polling dalam 30 detik, lakukan refresh
                console.warn('Polling berhenti total! Melakukan refresh halaman...');
                window.location.reload();
            }
        }, 180000);

        document.addEventListener('livewire:poll', () => {
            lastCallTime = Date.now();
            console.log('Polling berjalan normal, terakhir diperbarui:', new Date(lastCallTime).toLocaleTimeString());
        });
    </script>
@endpush
