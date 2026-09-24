@push('styles')
    <style>
        .marquee {
            width: 100%;
            overflow-y: hidden;
            height: calc(60vh);
        }
    </style>
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
        const antreanFarmasiRefreshSeconds = @if (app()->isProduction()) 300 @else parseInt(new URLSearchParams(window.location.search).get('refresh')) || 300 @endif;

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
                list.timer = setTimeout(() => refresh(seconds * 1000), Math.max(seconds * 1000, pass * 1.25 + 10000));
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
    </script>
@endpush

<div class="container-fluid">
    <div class="container-fluid d-flex justify-content-center border-bottom shadow">
        <img src="img/logo.png" alt="logo" width="120" />
        <h1 style="font-size: 4vh" class="pt-4">{{ __('Antrean Farmasi Rawat Jalan') }}</h1>
    </div>
    <div class="row">
        <livewire:pages.informasi.antrean-farmasi.list-pengerjaan />
        <livewire:pages.informasi.antrean-farmasi.list-penyerahan />
    </div>
    <div class="row">
        <livewire:pages.informasi.antrean-farmasi.nomor-dipanggil />
    </div>
</div>
