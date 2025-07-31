<div class="row" style="height: 60%" wire:poll.3s.keep-alive="call">
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
    @else
        <div class="col">
            <div class="card card-outline card-success d-flex justify-content-center h-100">
                <div class="card-header">
                    <h5 class="text-uppercase">antrean dipanggil</h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <h5 class="text-muted">Menunggu antrean...</h5>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('js')
    <script src="https://code.responsivevoice.org/responsivevoice.js?key=OGPOBj1g"></script>
    <script>
        let isPlaying = false;
        let lastPollTime = Date.now();
        let pollCheckInterval;

        // Update waktu polling saat ada aktivitas
        document.addEventListener('livewire:update', function() {
            lastPollTime = Date.now();
        });

        document.addEventListener('play-voice', (event) => {
            if (isPlaying) return; // Prevent multiple calls
            
            isPlaying = true;
            var textToSpeech = 'Nomor antrian ' + event.detail.no_reg + ', ' + 
                             event.detail.nm_pasien.toLowerCase() + ', silahkan menuju ke ' + 
                             event.detail.nm_pintu.toLowerCase();
            var cacheKey = event.detail.cacheKey;
            var patientData = event.detail.patient_data;
            
            // Start blinking animation
            startBlinkingAnimation();
            
            responsiveVoice.speak(textToSpeech, 'Indonesian Female', {
                rate: 0.7,
                onend: function() {
                    stopBlinkingAnimation();
                    isPlaying = false;
                    
                    // Update status tanpa reload
                    @this.call('updateStatusAfterCall', cacheKey, patientData);
                },
                onerror: function() {
                    stopBlinkingAnimation();
                    isPlaying = false;
                    console.error('Error in speech synthesis');
                }
            });
        });

        function startBlinkingAnimation() {
            var card = document.getElementById('calling-card');
            var numberElement = document.getElementById('calling-number');
            
            if (card && numberElement) {
                window.blinkInterval = setInterval(function() {
                    card.classList.toggle('bg-success');
                    numberElement.classList.toggle('text-white');
                }, 1000);
                
                // Stop after 10 seconds max
                setTimeout(stopBlinkingAnimation, 10000);
            }
        }

        function stopBlinkingAnimation() {
            if (window.blinkInterval) {
                clearInterval(window.blinkInterval);
                window.blinkInterval = null;
            }
            
            var card = document.getElementById('calling-card');
            var numberElement = document.getElementById('calling-number');
            
            if (card && numberElement) {
                card.classList.remove('bg-success');
                numberElement.classList.remove('text-white');
            }
        }

        // Monitor polling health - lebih conservative
        function startPollMonitoring() {
            if (pollCheckInterval) {
                clearInterval(pollCheckInterval);
            }
            
            pollCheckInterval = setInterval(() => {
                let currentTime = Date.now();
                let timeSinceLastPoll = (currentTime - lastPollTime) / 1000;
                
                // Jika lebih dari 15 detik tidak ada update
                if (timeSinceLastPoll > 15) {
                    console.warn('Polling issue detected. Forcing refresh...');
                    @this.call('forceRefresh');
                    lastPollTime = Date.now();
                }
            }, 10000); // Check setiap 10 detik
        }

        // Initialize monitoring when document ready
        document.addEventListener('DOMContentLoaded', function() {
            startPollMonitoring();
        });

        // Cleanup when page unloads
        window.addEventListener('beforeunload', function() {
            if (pollCheckInterval) {
                clearInterval(pollCheckInterval);
            }
            if (window.blinkInterval) {
                clearInterval(window.blinkInterval);
            }
        });
    </script>
@endpush