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
                    const tryExt = '.mp3';

                    const audioExists = async (url) => {
                        try {
                            const res = await fetch(url, { method: 'HEAD' });
                            return res.ok;
                        } catch (e) {
                            return false;
                        }
                    };

                    // playback control: allow cancelling current playback when a new call arrives
                    const playAudioSequence = async (urls, token) => {
                        window._announceAudioList = [];
                        for (const url of urls) {
                            // if token changed, stop early
                            if (window._announceCurrentToken !== token) return;
                            await new Promise((res) => {
                                const audio = new Audio(url);
                                window._announceAudioList.push(audio);

                                const onEnd = () => { cleanup(); res(); };
                                const onError = (e) => { console.warn('audio error', url, e); cleanup(); res(); };
                                const onPause = () => {
                                    if (window._announceCurrentToken !== token) { cleanup(); res(); }
                                };

                                function cleanup() {
                                    audio.removeEventListener('ended', onEnd);
                                    audio.removeEventListener('error', onError);
                                    audio.removeEventListener('pause', onPause);
                                }

                                audio.addEventListener('ended', onEnd);
                                audio.addEventListener('error', onError);
                                audio.addEventListener('pause', onPause);

                                audio.play().catch((e) => {
                                    console.warn('play failed', url, e);
                                    cleanup();
                                    res();
                                });
                            });
                        }
                    };

                    const stopCurrentPlayback = () => {
                        // change token so in-flight playAudioSequence exits
                        window._announceCurrentToken = Symbol();
                        if (window._announceAudioList && window._announceAudioList.length) {
                            for (const a of window._announceAudioList) {
                                try { a.pause(); a.currentTime = 0; } catch (e) {}
                            }
                        }
                        window._announceAudioList = [];
                        if (window._announceAudio) {
                            try { window._announceAudio.pause(); } catch (e) {}
                            window._announceAudio = null;
                        }
                        if (window._announceInterval) {
                            clearInterval(window._announceInterval);
                            window._announceInterval = null;
                        }
                    };

                    const buildAudioUrls = async (loket, antrian) => {
                        const base = '/suarasmc';
                        const s = String(antrian || '');
                        const prefix = s.charAt(0) || '';
                        const numberPart = s.slice(1) || '0';

                        const files = [];

                        // nomor-urut.mp3
                        const nomor = `${base}/nomor-urut${tryExt}`;
                        if (await audioExists(nomor)) files.push(nomor);

                        // PREFIX (A-F) — uppercase
                        if (prefix) {
                            const pref = `${base}/${prefix.toUpperCase()}${tryExt}`;
                            if (await audioExists(pref)) files.push(pref);
                        }

                        // digits of numberPart (each digit file exists: 0-9.mp3)
                        for (const ch of numberPart.split('')) {
                            const digitFile = `${base}/${ch}${tryExt}`;
                            if (await audioExists(digitFile)) files.push(digitFile);
                        }

                        // 'loket' word then loket number digits (e.g., loket.mp3 then 1.mp3)
                        const loketWord = `${base}/loket${tryExt}`;
                        if (await audioExists(loketWord)) files.push(loketWord);
                        const loketStr = String(loket || '');
                        for (const ch of loketStr.split('')) {
                            const lf = `${base}/${ch}${tryExt}`;
                            if (await audioExists(lf)) files.push(lf);
                        }

                        return files.length ? files : null;
                    };

                    const announce = async (loket, antrian) => {
                        // stop any previous playback immediately
                        try { stopCurrentPlayback(); } catch (e) { console.warn(e); }

                        try {
                            const urls = await buildAudioUrls(loket, antrian);
                            if (urls && urls.length) {
                                // create a token for this announcement so in-flight sequences can check
                                const token = Symbol();
                                window._announceCurrentToken = token;

                                const playOnce = () => playAudioSequence(urls, token).catch((e) => console.warn(e));
                                setTimeout(playOnce, 220);
                                // store interval so it can be cleared by stopCurrentPlayback
                                window._announceInterval = setInterval(playOnce, 12000);
                            }
                        } catch (e) {
                            console.warn('audio announce failed', e);
                        }
                    };

                    Livewire.on('queueCalled', (loket, antrian) => {
                        announce(loket, antrian);
                    });

                    Livewire.on('queueStopped', () => {
                        if (window._announceInterval) {
                            clearInterval(window._announceInterval);
                            window._announceInterval = null;
                        }
                        if (window._announceAudio) {
                            try {
                                window._announceAudio.pause();
                                window._announceAudio = null;
                            } catch (e) {}
                        }
                    });
                }
        </script>
    @endpush
</div>
