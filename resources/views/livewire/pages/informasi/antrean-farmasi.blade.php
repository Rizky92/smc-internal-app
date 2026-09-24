@push('styles')
    <link rel="stylesheet" href="{{ asset('css/antrean-farmasi.css') }}" />
@endpush

@push('js')
    <script src="{{ asset('js/jquery.marquee.min.js') }}"></script>
    <script>
        // Each list scrolls when its rows are taller than its box, and asks Livewire to
        // refresh when a pass finishes. A list that fits refreshes on a timer instead.
        // A scrolling list also keeps a longer fallback timer, and every refresh re-arms
        // its timer before sending, so a lost 'finished' event or a failed request can
        // never leave a list frozen.
        const antreanFarmasiLists = {};
        const antreanFarmasiRefreshSeconds = @unless (app()->isProduction()) parseInt(new URLSearchParams(window.location.search).get('refresh')) || @endunless 300;
        // A scrolling list's fallback waits out the whole pass with room to spare.
        const antreanFarmasiFallbackPassFactor = 1.25;
        const antreanFarmasiFallbackMarginMs = 10000;

        // 'pengerjaan' → #marquee-pengerjaan, list-pengerjaan component, marqueePengerjaanFinished listener.
        function registerAntreanFarmasiList(key) {
            const component = 'pages.informasi.antrean-farmasi.list-' + key;
            const event = 'marquee' + key.charAt(0).toUpperCase() + key.slice(1) + 'Finished';

            antreanFarmasiLists[component] = { id: 'marquee-' + key, event, timer: null };
        }

        function initAntreanFarmasiList(component) {
            const list = antreanFarmasiLists[component];
            const marquee = $('#' + list.id);
            const seconds = antreanFarmasiRefreshSeconds;
            const refresh = (delay) => {
                clearTimeout(list.timer);
                list.timer = setTimeout(() => refresh(delay), delay);
                Livewire.emitTo(component, list.event);
            };

            clearTimeout(list.timer);
            marquee.marquee('destroy');
            marquee.find('.js-marquee-wrapper').remove();

            if (marquee[0].scrollHeight > marquee[0].clientHeight) {
                marquee.marquee();
                marquee.off('finished').on('finished', function () {
                    $(this).marquee('destroy');
                    refresh(seconds * 1000);
                });
                // The fallback must outlast the pass itself, which grows with the row count.
                const style = getComputedStyle(marquee.find('.js-marquee-wrapper')[0]);
                const pass = (parseFloat(style.animationDuration) + parseFloat(style.animationDelay)) * 1000 || 0;
                list.timer = setTimeout(() => refresh(seconds * 1000), Math.max(seconds * 1000, pass * antreanFarmasiFallbackPassFactor + antreanFarmasiFallbackMarginMs));
            } else {
                list.timer = setTimeout(() => refresh(seconds * 1000), seconds * 1000);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            Object.keys(antreanFarmasiLists).forEach(initAntreanFarmasiList);
        });

        Livewire.hook('message.processed', (message, component) => {
            if (antreanFarmasiLists[component.fingerprint.name]) {
                initAntreanFarmasiList(component.fingerprint.name);
            }
        });

        // Highlight the called-number band for a few seconds whenever the number changes.
        let antreanFarmasiNomorTerakhir = null;

        Livewire.hook('message.processed', (message, component) => {
            if (component.fingerprint.name !== 'pages.informasi.antrean-farmasi.nomor-dipanggil') {
                return;
            }

            const band = component.el;
            const nomor = band.dataset.nomor;

            if (nomor && nomor !== antreanFarmasiNomorTerakhir) {
                band.classList.add('is-new');
                clearTimeout(band.highlightTimer);
                band.highlightTimer = setTimeout(() => band.classList.remove('is-new'), 8000);
            }

            antreanFarmasiNomorTerakhir = nomor;
        });

        document.addEventListener('DOMContentLoaded', () => {
            antreanFarmasiNomorTerakhir = document.getElementById('nomor-dipanggil')?.dataset.nomor ?? null;
        });
    </script>
@endpush

<div class="antrean-farmasi">
    <header class="af-header">
        <img src="{{ asset('img/logo.png') }}" alt="SMC" />
        <h1>{{ __('Antrean Farmasi Rawat Jalan') }}</h1>
    </header>
    <div class="af-lists">
        <livewire:pages.informasi.antrean-farmasi.list-pengerjaan />
        <livewire:pages.informasi.antrean-farmasi.list-penyerahan />
    </div>
    <livewire:pages.informasi.antrean-farmasi.nomor-dipanggil />
</div>
