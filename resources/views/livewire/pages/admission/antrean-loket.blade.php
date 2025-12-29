<div class="container-fluid">
    <div class="row p-1 g-3">
        <div class="col-lg-8">
            <div class="card card-outline card-success h-100" style="min-height: 68vh">
                <iframe
                    height="100%"
                    src="https://www.youtube.com/embed/Qgh6STbZZng?si=Ql5ajW32_iXYiA8j&amp;controls=0"
                    title="YouTube video player"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    referrerpolicy="strict-origin-when-cross-origin"
                    allowfullscreen></iframe>
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
                const makeBeep = (freq = 1200, duration = 150) => {
                    try {
                        const AudioCtx = window.AudioContext || window.webkitAudioContext;
                        if (!AudioCtx) return;
                        if (!window._audioCtx) window._audioCtx = new AudioCtx();
                        const ctx = window._audioCtx;
                        const o = ctx.createOscillator();
                        const g = ctx.createGain();
                        o.type = 'sine';
                        o.frequency.value = freq;
                        o.connect(g);
                        g.connect(ctx.destination);
                        const now = ctx.currentTime;
                        g.gain.setValueAtTime(0.0001, now);
                        g.gain.exponentialRampToValueAtTime(1, now + 0.01);
                        o.start(now);
                        g.gain.exponentialRampToValueAtTime(0.0001, now + duration / 1000);
                        o.stop(now + duration / 1000 + 0.02);
                    } catch (e) {
                        console.warn('beep failed', e);
                    }
                };

                const speakText = (text) => {
                    if (!('speechSynthesis' in window)) return;
                    try {
                        window.speechSynthesis.cancel();
                        const u = new SpeechSynthesisUtterance(text);
                        u.lang = 'id-ID';
                        u.rate = 0.95;
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
                    const text = `Nomor ${prefix} ${numberPart}, silakan ke loket ${loket}.`;

                    try {
                        makeBeep(1200, 150);
                        setTimeout(() => speakText(text), 220);
                    } catch (e) {
                        console.error(e);
                    }

                    window._announceInterval = setInterval(() => {
                        try {
                            makeBeep(1200, 150);
                            setTimeout(() => speakText(text), 220);
                        } catch (e) {
                            console.error(e);
                        }
                    }, 4000);
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
