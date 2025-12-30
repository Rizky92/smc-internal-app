<div class="container-fluid">
    <div class="row p-1 g-3">
        <div class="col-lg-8">
            <div class="card card-outline card-success h-100" style="min-height: 68vh">
                <img src="data:image/jpeg;base64, {{ base64_encode($this->iklan->gambar) }}" alt="" style="height: 100%" />
            </div>
        </div>
        <div class="col-lg-4">
            <div class="d-flex flex-column gap-3 h-100">
                @if ($loket !== null && $antrian !== null)
                    <div class="card card-outline card-success flex-fill">
                        <div class="card-header">
                            <h5 class="text-success">No. Antrean</h5>
                        </div>
                        <div class="card-body text-center p-2 justify-content-center d-flex align-items-center">
                            <h1 class="m-0 text-success" style="font-size: 9rem">{{ $antrian }}</h1>
                        </div>
                    </div>
                    <div class="card card-outline card-success flex-fill">
                        <div class="card-header">
                            <h5 class="text-success">Loket</h5>
                        </div>
                        <div class="card-body text-center p-2 justify-content-center d-flex align-items-center">
                            <h1 class="m-0 text-success" style="font-size: 9rem">{{ $loket }}</h1>
                        </div>
                    </div>
                @else
                    <div class="card card-outline card-success flex-fill">
                        <div class="card-header">
                            <h5 class="text-success">No. Antrean</h5>
                        </div>
                        <div class="card-body text-center p-2"></div>
                    </div>
                    <div class="card card-outline card-success flex-fill">
                        <div class="card-header">
                            <h5 class="text-success">Loket</h5>
                        </div>
                        <div class="card-body text-center p-2"></div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <h5 class="text-success">Antrean Terakhir</h5>
    <livewire:pages.admission.antrean-loket-terakhir />

    @push('js')
        <script src="https://js.pusher.com/8.4/pusher.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>
        <script>
            window.Echo = new Echo({
                broadcaster: 'pusher',
                key: 'local-key',
                cluster: 'mt1',
                wsHost: window.location.hostname,
                wsPort: 6001,
                forceTLS: false,
                disableStats: true,
                enabledTransports: ['ws'],
            });

            Echo.channel('antrean-loket-smc')
                .listen('.PanggilAntreanLoketSmc', (e) => {
                    Livewire.emit('queueCalled', e.loket, e.antrian);
                })
                .listen('.StopAntreanLoketSmc', () => {
                    Livewire.emit('queueStopped');
                });

            if (window.Livewire) {
                const speakText = (text) => {
                    if (!('speechSynthesis' in window)) return;
                    try {
                        window.speechSynthesis.cancel();
                        const u = new SpeechSynthesisUtterance(text);
                        u.lang = 'id-ID';
                        u.rate = 0.75;
                        window.speechSynthesis.speak(u);
                    } catch (e) {
                        console.warn('speak failed', e);
                    }
                };

                const announce = (loket, antrian) => {
                    if (window._announceInterval) {
                        clearInterval(window._announceInterval);
                        window._announceInterval = null;
                    }
                    if ('speechSynthesis' in window) window.speechSynthesis.cancel();

                    const s = String(antrian || '');
                    const prefix = s.charAt(0) || '';
                    const numberPart = s.slice(1).replace(/^0+/, '') || '0';
                    const text = `Nomor urut ${prefix} ${numberPart}, silakan ke loket ${loket}.`;

                    try {
                        setTimeout(() => speakText(text), 220);
                    } catch (e) {
                        console.error(e);
                    }

                    window._announceInterval = setInterval(() => {
                        try {
                            setTimeout(() => speakText(text), 220);
                        } catch (e) {
                            console.error(e);
                        }
                    }, 12000);
                };

                Livewire.on('queueCalled', (loket, antrian) => {
                    announce(loket, antrian);
                });

                Livewire.on('queueStopped', () => {
                    if (window._announceInterval) {
                        clearInterval(window._announceInterval);
                        window._announceInterval = null;
                    }
                    if ('speechSynthesis' in window) window.speechSynthesis.cancel();
                });
            }
        </script>
    @endpush
</div>
