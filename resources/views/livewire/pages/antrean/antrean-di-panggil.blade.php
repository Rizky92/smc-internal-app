<div class="row" style="height: 60%" @if(!$isCalling) wire:poll.2000ms.keep-alive="call" @endif>
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
            <div class="card card-outline card-success d-flex justify-content-center h-100">
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
        // Fungsi untuk mengecek apakah browser support bahasa Indonesia
        function checkIndonesianVoiceSupport() {
            return new Promise((resolve) => {
                if (!('speechSynthesis' in window)) {
                    resolve(false);
                    return;
                }

                let voices = speechSynthesis.getVoices();

                // Jika voices belum loaded, tunggu event
                if (voices.length === 0) {
                    speechSynthesis.onvoiceschanged = () => {
                        voices = speechSynthesis.getVoices();
                        const hasIndonesian = voices.some((voice) => voice.lang.startsWith('id') || voice.lang.startsWith('in'));
                        resolve(hasIndonesian);
                    };
                } else {
                    const hasIndonesian = voices.some((voice) => voice.lang.startsWith('id') || voice.lang.startsWith('in'));
                    resolve(hasIndonesian);
                }
            });
        }

        // Fungsi untuk memutar suara menggunakan Web Speech API
        function playNativeSpeech(text, onEnd, onError) {
            try {
                const utterance = new SpeechSynthesisUtterance(text);
                const voices = speechSynthesis.getVoices();

                // Cari voice bahasa Indonesia
                const indonesianVoice = voices.find((voice) => voice.lang.startsWith('id') || voice.lang.startsWith('in'));

                if (indonesianVoice) {
                    utterance.voice = indonesianVoice;
                }

                utterance.lang = 'id-ID';
                utterance.rate = 0.7;
                utterance.pitch = 1;

                utterance.onend = onEnd;
                utterance.onerror = (event) => {
                    console.error('Native speech error:', event);
                    // Jika error karena NotAllowedError atau error lainnya
                    if (event.error === 'not-allowed' || event.error === 'network') {
                        console.log('Speech not allowed or network error, calling updateStatus');
                        onError(event);
                    } else {
                        onError(event);
                    }
                };

                speechSynthesis.speak(utterance);
            } catch (error) {
                console.error('Exception in playNativeSpeech:', error);
                onError(error);
            }
        }

        // Fungsi untuk memutar suara menggunakan ResponsiveVoice
        function playResponsiveVoice(text, onEnd, onError) {
            // Pastikan ResponsiveVoice sudah ready
            if (typeof responsiveVoice === 'undefined') {
                console.error('ResponsiveVoice not loaded');
                onError(new Error('ResponsiveVoice not loaded'));
                return;
            }

            try {
                // Set timeout sebagai fallback jika callback tidak dipanggil
                let callbackCalled = false;
                const timeoutId = setTimeout(() => {
                    if (!callbackCalled) {
                        console.warn('ResponsiveVoice callback timeout, forcing updateStatus');
                        callbackCalled = true;
                        onError(new Error('Timeout'));
                    }
                }, 25000); // 25 detik timeout

                responsiveVoice.speak(text, 'Indonesian Female', {
                    rate: 0.7,
                    onend: function () {
                        if (!callbackCalled) {
                            callbackCalled = true;
                            clearTimeout(timeoutId);
                            console.log('ResponsiveVoice onend triggered');
                            onEnd();
                        }
                    },
                    onerror: function (e) {
                        if (!callbackCalled) {
                            callbackCalled = true;
                            clearTimeout(timeoutId);
                            console.error('ResponsiveVoice error:', e);
                            onError(e);
                        }
                    },
                });

                // Tangani error NotAllowedError dari promise
                // ResponsiveVoice menggunakan audio element di baliknya
                setTimeout(() => {
                    const audioElements = document.querySelectorAll('audio');
                    audioElements.forEach((audio) => {
                        if (audio.src && audio.src.includes('responsivevoice')) {
                            audio.play().catch((err) => {
                                if (!callbackCalled && err.name === 'NotAllowedError') {
                                    callbackCalled = true;
                                    clearTimeout(timeoutId);
                                    console.error('NotAllowedError caught:', err);
                                    onError(err);
                                }
                            });
                        }
                    });
                }, 100);
            } catch (error) {
                console.error('Exception in playResponsiveVoice:', error);
                onError(error);
            }
        }

        // Fungsi untuk memulai efek blinking
        function startBlinking(card, numberElement) {
            if (window.blinkInterval) {
                clearInterval(window.blinkInterval);
            }

            if (card && numberElement) {
                window.blinkInterval = setInterval(() => {
                    card.classList.toggle('bg-success');
                    numberElement.classList.toggle('text-white');
                }, 1000);
            }
        }

        // Fungsi untuk menghentikan efek blinking
        function stopBlinking(card, numberElement) {
            if (window.blinkInterval) {
                clearInterval(window.blinkInterval);
                window.blinkInterval = null;
                if (card && numberElement) {
                    card.classList.remove('bg-success');
                    numberElement.classList.remove('text-white');
                }
            }
        }

        // Event listener untuk play-voice
        document.addEventListener('play-voice', async (event) => {
            let text = `Nomor antrian ${event.detail.no_reg}, ${event.detail.nm_pasien.toLowerCase()}, silahkan menuju ke ${event.detail.nm_pintu.toLowerCase()}`;
            let card = document.getElementById('calling-card');
            let numberElement = document.getElementById('calling-number');

            // Mulai efek blinking
            startBlinking(card, numberElement);

            // Callback ketika selesai berbicara
            const onEnd = () => {
                stopBlinking(card, numberElement);

                // Support untuk Livewire v2 dan v3
                if (typeof Livewire.emit === 'function') {
                    Livewire.dispatch('updateStatus');
                } else if (typeof Livewire.dispatch === 'function') {
                    Livewire.dispatch('updateStatus');
                } else {
                    // Fallback jika tidak ada method yang tersedia
                    window.Livewire.find(card.closest('[wire\\:id]').getAttribute('wire:id')).call('updateStatus');
                }
            };

            // Callback ketika terjadi error
            const onError = (e) => {
                console.error('Speech error', e);
                stopBlinking(card, numberElement);

                // Tetap panggil updateStatus untuk menghindari antrean stuck
                // Support untuk Livewire v2 dan v3
                if (typeof Livewire.emit === 'function') {
                    Livewire.dispatch('updateStatus');
                } else if (typeof Livewire.dispatch === 'function') {
                    Livewire.dispatch('updateStatus');
                } else {
                    // Fallback jika tidak ada method yang tersedia
                    window.Livewire.find(card.closest('[wire\\:id]').getAttribute('wire:id')).call('updateStatus');
                }
            };

            // Cek dukungan bahasa Indonesia di browser
            const hasNativeIndonesian = await checkIndonesianVoiceSupport();

            if (hasNativeIndonesian) {
                console.log('Menggunakan Web Speech API (Native Browser)');
                playNativeSpeech(text, onEnd, onError);
            } else {
                console.log('Menggunakan ResponsiveVoice (Fallback)');
                playResponsiveVoice(text, onEnd, onError);
            }
        });
    </script>
@endpush
