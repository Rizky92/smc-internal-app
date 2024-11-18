@push('styles')
    <link href="{{ asset('css/fontawesome5-all.min.css') }}" rel="stylesheet">
    <style>
      .marquee {
        width: 100%;
        overflow-y: scroll;
        overflow: hidden;
        height:calc(75vh);
      }
    </style>
@endpush
<div>
    <header class="d-flex flex-wrap justify-content-center py-2 pb-2 mb-4 border-bottom shadow header">
        <div class="container-fluid d-flex justify-content-center">
            <h1 class="text-uppercase text-success">{{ $this->poli->nm_poli }}</h1>
        </div>
    </header>
    @if ($this->antrean->isNotEmpty())
        <div class="container-fluid">
            <div class="row">
                @if ($this->nextAntrean)
                    <div class="col-5 text-center" wire:poll.10s="call">
                        <div class="container-toast border shadow">
                            <div class="bg-success">
                                <h1 class="text-white text-uppercase">antrean dipanggil</h1>
                            </div>
                            <h1 class="text-danger" style="font-size: 12rem">
                            {{ $this->nextAntrean->registrasi->no_reg }}</h1>
                            <h2 class="text-uppercase">{{ $this->nextAntrean->dokter->nm_dokter }}</h2>
                        </div>
                        <script src="https://code.responsivevoice.org/responsivevoice.js?key=OGPOBj1g"></script>
                        <script>
                            document.addEventListener('play-voice', event => {
                                var textToSpeech = 'Panggilan untuk nomor antrian ' + event.detail.no_reg +
                                    ', silahkan menuju ke ' + event.detail.nm_poli;
                                responsiveVoice.speak(textToSpeech, "Indonesian Female", { rate: 0.7 });
                            });
                        </script>
                    </div>
                @else
                    <div class="col-5 text-center">
                        <div class="container-toast border shadow">
                            <div class="bg-success">
                                <h1 class="text-white text-uppercase">antrean dipanggil</h1>
                            </div>
                            <i class="fas fa-times-circle text-danger display-1 p-5"></i>
                            <h2 class="text-uppercase">tidak ada yang dapat ditampilkan</h2>
                        </div>
                    </div>
                @endif
                <div class="col">
                    <div class="row">
                        <div class="col-12">
                            <table width="100%" class="table table-bordered table-striped">
                                <thead class="bg-success">
                                    <tr>
                                        <th style="width: 12ch;">No Reg</th>
                                        <th style="width: 44ch;">Nama Dokter</th>
                                        <th style="width: 44ch;">Nama Pasien</th>
                                    </tr>
                                </thead>
                            </table>
                            <div class="marquee bg-white" data-direction="up" data-duration="20000" startVisible="true" data-gap="10" data-duplicated="false">
                                <table class="table table-bordered table-striped">
                                    <tbody>
                                        @forelse ($this->antrean as $item)
                                            <tr>
                                                <td style="width: 12ch;">{{ $item->no_reg }}</td>
                                                <td style="width: 44ch;">{{ $item->dokterPoli->nm_dokter }}</td>
                                                <td style="width: 44ch;">{{ $item->pasien->nm_pasien }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="text-center text-muted p-4">Tidak ada yang dapat
                                                    ditampilkan saat ini</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <script src="{{ asset('js/jquery.min.js') }}"></script>
                            <script src="{{ asset('js/jquery.marquee.min.js') }}"></script>
                            <script>
                                $(".marquee").marquee() 
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="container-fluid">
            <div class="row">
                <div class="col-5 text-center">
                    <div class="container-toast border shadow">
                        <div class="bg-success">
                            <h1 class="text-white text-uppercase">antrean dipanggil</h1>
                        </div>
                        <i class="fas fa-times-circle text-danger display-1 p-5"></i>
                        <h2 class="text-uppercase">tidak ada yang dapat ditampilkan</h2>
                    </div>
                </div>
                <div class="col">
                    <div class="row">
                        <div class="col-12">
                            <table width="100%" class="table table-striped">
                                <thead class="bg-success">
                                    <tr>
                                        <th style="width: 12ch;">No Reg</th>
                                        <th style="width: 44ch;">Nama Dokter</th>
                                        <th style="width: 44ch;">Nama Pasien</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted p-4">Tidak ada yang dapat
                                            ditampilkan saat ini</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>