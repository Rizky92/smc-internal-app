<div>
    @if ($loket !== null && $antrian !== null)
        <div class="text-center">
            <h1 class="text-9xl font-bold">{{ $antrian }}</h1>
            <h2 class="text-6xl mt-4">Loket {{ $loket }}</h2>
        </div>
    @else
        <div class="text-center">
            <h1 class="text-4xl">Tidak ada antrean yang dipanggil</h1>
        </div>
    @endif

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
        </script>
    @endpush
</div>
