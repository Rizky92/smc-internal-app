@push('styles')
    <style>
        #scrollingContent {
            max-height: 100vh;
            overflow-y: hidden;
            scroll-behavior: linear;
        }

        #scrollingContent table {
            /* Durasi akan diset secara dinamis via JavaScript */
            animation-timing-function: linear;
            animation-fill-mode: forwards;
            transform-origin: 0% 0%;
            display: table;
            width: 100%;
        }

        .padding {
            height: 80vh;
            z-index: 1;
            position: relative;
        }

        #scrollingContent table tbody {
            display: table-row-group;
        }

        @keyframes marqueeAnimation {
            0% {
                transform: translateY(0);
            }
            100% {
                transform: translateY(calc(-100% + 100px));
            }
        }

        /* Class untuk animasi yang akan ditambahkan via JavaScript */
        .animate-scroll {
            animation-name: marqueeAnimation;
        }
    </style>
@endpush

<div class="card">
    <div class="container-fluid d-flex justify-content-center">
        <img src="img/logo.png" alt="logo" width="120" />
        <h1 style="font-size: 8vh; padding-top: 20px">Jadwal Dokter</h1>
    </div>
    <div class="card-body p-0">
        <table class="table text-nowrap mb-0" style="font-size: 2.5vh">
            <thead>
                <tr>
                    <th style="width: 40%">Nama Dokter</th>
                    <th class="text-center" style="width: 10%">SENIN</th>
                    <th class="text-center" style="width: 10%">SELASA</th>
                    <th class="text-center" style="width: 10%">RABU</th>
                    <th class="text-center" style="width: 10%">KAMIS</th>
                    <th class="text-center" style="width: 10%">JUMAT</th>
                    <th class="text-center" style="width: 10%">SABTU</th>
                </tr>
            </thead>
        </table>
        <div id="scrollingContent">
            <table class="table text-nowrap" id="scrollTable" style="font-size: 2.5vh">
                <div class="padding"></div>
                <tbody>
                    @foreach ($collection as $poli => $jadwals)
                        <tr>
                            <td colspan="8" class="bg-green"><strong>{{ strtoupper($poli) }}</strong></td>
                        </tr>
                        @foreach ($jadwals as $dokterId => $dokterJadwal)
                            {{-- Sudah berbentuk array --}}
                            <tr>
                                <td style="width: 40%">{{ $dokterJadwal[0]['dokter']['nm_dokter'] }}</td>
                                {{-- Ambil nama dokter dari array --}}
                                @foreach (['SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU'] as $day)
                                    <td class="text-center" style="width: 10%">
                                        @php
                                            $hariJadwal = array_filter($dokterJadwal, fn ($j) => strtoupper($j['hari_kerja']) === $day);
                                        @endphp

                                        @if (! empty($hariJadwal))
                                            {!! implode('<br>', array_map(fn ($j) => date('H:i', strtotime($j['jam_mulai'])) . ' - ' . date('H:i', strtotime($j['jam_selesai'])), $hariJadwal)) !!}
                                        @else
                                            -
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const scrollTable = document.getElementById('scrollTable');
            const scrollingContent = document.getElementById('scrollingContent');
            setTimeout(() => {
                calculateAndStartScroll();
            }, 100);

            function calculateAndStartScroll() {
                const tableHeight = scrollTable.offsetHeight;
                const containerHeight = scrollingContent.offsetHeight;
                const paddingHeight = document.querySelector('.padding').offsetHeight;
                const scrollDistance = tableHeight - containerHeight + paddingHeight;
                const scrollSpeed = 50;
                const scrollDuration = Math.max(scrollDistance / scrollSpeed, 10); // minimal 10 detik
                scrollTable.style.animationDuration = scrollDuration + 's';
                scrollTable.classList.add('animate-scroll');
                const reloadDelay = (scrollDuration + 1) * 1000;
                setTimeout(() => {
                    location.reload(true);
                }, reloadDelay);
            }
        });
    </script>
@endpush
