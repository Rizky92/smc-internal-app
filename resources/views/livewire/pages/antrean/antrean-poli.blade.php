@push('styles')
    <link href="{{ asset('css/fontawesome5-all.min.css') }}" rel="stylesheet">
    <style>
        .marquee {
            width: 100%;
            height: 75vh;
            overflow: hidden;
            position: relative;
        }

        .marquee-content {
            display: inline-block;
            position: absolute;
            animation: scroll-up 20s linear infinite;
        }

        @keyframes scroll-up {
            from {
                transform: translateY(100%);
            }
            to {
                transform: translateY(-100%);
            }
        }
    </style>
@endpush
<div>
    <header class="d-flex flex-wrap justify-content-center py-2 pb-2 mb-4 border-bottom shadow header">
        <div class="container-fluid d-flex justify-content-center">
            <h1 class="text-uppercase text-success">{{ $this->antrean->first()->nm_poli }}</h1>
        </div>
    </header>
    <div class="container-fluid" wire:poll.15s='call'>
        <div class="row">
            <div class="col-5 text-center">
                @if ($this->nextAntrean && $this->nextAntrean->status == '1')
                    <div class="container-toast border shadow">
                        <div class="bg-success">
                            <h1 class="display-5 text-white">ANTREAN DIPANGGIL</h1>
                        </div>
                        <div class="container-toast-content">
                            <div class="d-flex justify-content-center">
                                <div class="d-flex flex-column text-center">
                                    <h3 class="text-uppercase">{{ $this->nextAntrean->nm_dokter }}</h3>
                                    <h1 class="text-danger" style="font-size: 9rem">{{ $this->nextAntrean->no_reg }}</h1>
                                    <h4>{{ $this->nextAntrean->nm_pasien }}</h4>
                                </div>
                            </div>
                        </div>
                        <button id="playButton" class="btn btn-primary d-none">Play Voice</button>
                        <script>
                            document.addEventListener('play-voice', (event) => {
                                var textToSpeech = 'Nomor antrian ' + event.detail.no_reg + ', ' +
                                    event.detail.nm_pasien.toLowerCase() +
                                    ', silahkan menuju ke ' + event.detail.nm_poli.toLowerCase();
                                var playButton = document.getElementById('playButton');

                                if (playButton) {
                                    playButton.addEventListener('click', async () => {
                                        await new Promise((resolve) => {
                                            responsiveVoice.speak(textToSpeech, "Indonesian Female", {
                                                rate: 0.7,
                                                onend: resolve,
                                            });
                                        });

                                        if (typeof Livewire !== 'undefined') {
                                            Livewire.emit('updateStatusAfterCall');
                                        } else {
                                            console.error('Livewire is not defined');
                                        }
                                    });
                                    playButton.click();
                                } else {
                                    console.error('playButton element not found');
                                }
                            });
                        </script>
                    </div>
                @else
                    <div class="col text-center">
                        <div class="container-toast border shadow">
                            <div class="bg-success">
                                <h1 class="text-white text-uppercase">antrean dipanggil</h1>
                            </div>
                            <h1 class="text-danger" style="font-size: 9rem;">000</h1>
                            <h2 class="text-uppercase">tidak ada yang dapat ditampilkan</h2>
                        </div>
                    </div>
                @endif
            </div>
            <div class="col">
                <table width="100%" class="table table-bordered table-striped">
                    <thead class="bg-success">
                        <tr>
                            <th style="width: 12ch;">No Reg</th>
                            <th style="width: 44ch;">Nama Dokter</th>
                            <th style="width: 44ch;">Nama Pasien</th>
                        </tr>
                    </thead>
                </table>
                <div class="marquee bg-white">
                    <div class="marquee-content">
                        <table class="table table-bordered table-striped">
                            <tbody>
                                @forelse ($this->antrean as $item)
                                    <tr>
                                        <td style="width: 12ch;">{{ $item->no_reg }}</td>
                                        <td style="width: 44ch;">{{ $item->nm_dokter }}</td>
                                        <td style="width: 44ch;">{{ $item->nm_pasien }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted p-4">
                                            Tidak ada yang dapat ditampilkan saat ini
                                        </td>
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
@push('js')
    <script src="https://code.responsivevoice.org/responsivevoice.js?key=OGPOBj1g"></script>
@endpush