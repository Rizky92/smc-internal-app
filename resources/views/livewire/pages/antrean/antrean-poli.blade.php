@push('styles')
    <link href="{{ asset('css/fontawesome5-all.min.css') }}" rel="stylesheet" />
    <style>
        .marquee {
            width: 100%;
            overflow-y: scroll;
            overflow-y: hidden;
            height: calc(75vh);
        }
    </style>
@endpush

<div>
    <header class="d-flex flex-wrap justify-content-center py-2 pb-2 mb-4 border-bottom shadow header">
        <div class="container-fluid d-flex justify-content-center">
            <h1 class="text-uppercase text-success">
                {{ \App\Models\Perawatan\Poliklinik::where('kd_poli', $this->kd_poli)->first()->nm_poli }}
            </h1>
        </div>
    </header>
    <div class="container-fluid">
        <div class="row">
            <div class="col-5 text-center">
                <div class="row">
                    @if ($this->nextAntrean && $this->nextAntrean->status == '1')
                        <div class="col">
                            <div class="container-toast border shadow">
                                <div class="bg-success">
                                    <h1 class="display-5 text-white">ANTREAN DIPANGGIL</h1>
                                </div>
                                <div class="container-toast-content" style="height: 12rem">
                                    <div class="d-flex justify-content-center">
                                        <div class="d-flex flex-column text-center">
                                            <h3 class="text-uppercase">
                                                {{ $this->nextAntrean->nm_dokter }}
                                            </h3>
                                            <h1 class="text-danger" style="font-size: 9rem">
                                                {{ $this->nextAntrean->no_reg }}
                                            </h1>
                                            <h4>
                                                {{ $this->nextAntrean->nm_pasien }}
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="col text-center">
                            <div class="container-toast border shadow">
                                <div class="bg-success">
                                    <h1 class="text-white text-uppercase">antrean dipanggil</h1>
                                </div>
                                <div class="container-toast-content" style="height: 12rem"></div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="row">
                    <div class="col">
                        <div class="container-toast border shadow">
                            <div class="bg-success">
                                <h1 class="text-white text-uppercase">jadwal dokter</h1>
                            </div>
                            <div class="container-toast-content">
                                <div class="d-flex justify-content-center">
                                    <div class="d-flex flex-column text-center">
                                        @php
                                            \Illuminate\Support\Carbon::setLocale('id');

                                            $jadwal = \App\Models\Antrian\Jadwal::with('dokter')
                                                ->where('kd_poli', $this->kd_poli)
                                                ->where('hari_kerja', strtoupper(\Illuminate\Support\Carbon::now()->translatedFormat('l')))
                                                ->get();
                                        @endphp

                                        <table width="100%" class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th style="width: 44ch">Nama Dokter</th>
                                                    <th style="width: 44ch">Jam Praktek</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($jadwal as $item)
                                                    <tr>
                                                        <td style="width: 44ch">
                                                            {{ $item->dokter->nm_dokter }}
                                                        </td>
                                                        <td style="width: 44ch">
                                                            {{ $item->jam_mulai }}
                                                            -
                                                            {{ $item->jam_selesai }}
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted p-4">Tidak ada yang dapat ditampilkan saat ini</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <table width="100%" class="table table-bordered table-striped">
                    <thead class="bg-success">
                        <tr>
                            <th style="width: 12ch">No Reg</th>
                            <th style="width: 44ch">Nama Dokter</th>
                            <th style="width: 44ch">Nama Pasien</th>
                        </tr>
                    </thead>
                </table>
                <div class="marquee bg-white" data-direction="up" data-duration="20000" startVisible="true" data-gap="10" data-duplicated="false">
                    <table class="table table-bordered table-striped">
                        <tbody>
                            @forelse ($this->antrean as $item)
                                <tr>
                                    <td style="width: 12ch">
                                        {{ $item->no_reg }}
                                    </td>
                                    <td style="width: 44ch">
                                        {{ $item->nm_dokter }}
                                    </td>
                                    <td style="width: 44ch">
                                        {{ $item->nm_pasien }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted p-4">Tidak ada yang dapat ditampilkan saat ini</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@push('js')
    <script src="https://code.responsivevoice.org/responsivevoice.js?key=OGPOBj1g"></script>
    <script src="{{ asset('js/jquery.marquee.min.js') }}"></script>
    <script>
        document.addEventListener('play-voice', (event) => {
            var textToSpeech = 'Nomor antrian ' + event.detail.no_reg + ', ' + event.detail.nm_pasien.toLowerCase() + ', silahkan menuju ke ' + event.detail.nm_poli.toLowerCase();
            var callCount = 0;

            function callVoice() {
                responsiveVoice.speak(textToSpeech, 'Indonesian Female', {
                    rate: 0.7,
                    onend: () => {
                        callCount++;
                        if (callCount < 3) {
                            callVoice();
                        } else {
                            if (typeof Livewire !== 'undefined') {
                                Livewire.emit('updateStatusAfterCall');
                                Livewire.emit('updateAntrean');
                            } else {
                                console.error('Livewire is not defined');
                            }
                        }
                    },
                });
            }

            callVoice();
        });

        document.addEventListener('DOMContentLoaded', function () {
            let marquee = $('.marquee');
            var callCount = 0;

            function initializeMarquee() {
                marquee.marquee();
                marquee.off('finished').on('finished', function () {
                    callCount++;
                    if (callCount < 3) {
                        initializeMarquee();
                    } else {
                        Livewire.emit('updateAntrean');
                        Livewire.emit('call');
                        callCount = 0; // Reset call count for next cycle
                    }
                });
            }

            initializeMarquee();

            window.addEventListener('updateMarqueeData', function () {
                marquee.marquee('destroy');
                initializeMarquee();
            });
        });
    </script>
@endpush
