@if ($antrianPasien)
    <!DOCTYPE html>
    <html>
        <head>
            <meta charset="utf-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1" />
            <link rel="stylesheet" href="{{ asset('css/adminlte.min.css') }}" />
            <link rel="stylesheet" href="{{ asset('css/antrian-poli.css') }}" />
            <link rel="stylesheet" href="{{ asset('css/dataTables.bootstrap4.min.css') }}" />
            <link rel="stylesheet" href="{{ asset('css/responsive.bootstrap4.min.css') }}" />
            <title>{{ config('app.name') }}</title>
        </head>
        <body>
            <header class="d-flex flex-wrap justify-content-center py-2 pb-2 mb-4 border-bottom shadow header">
                <div class="container-fluid d-flex justify-content-center">
                    <img src="{{ asset('img/logo.png') }}" alt="" width="120" />
                    <span>ANTREAN POLIKLINIK RS SMC</span>
                </div>
            </header>
            <div class="container-fluid">
                <div class="row">
                    <div class="col text-center">
                        @if ($nextAntrian)
                            <div class="container-toast border shadow" id="callContainer">
                                <div class="bg-pandan">
                                    <h1 class="display-5 text-white">ANTREAN DIPANGGIL</h1>
                                </div>
                                <a class="text-danger">
                                    {{ $nextAntrian->no_reg }}
                                </a>
                                <h2>
                                    <strong id="namaPoli">
                                        {{ $namaPoli }}
                                    </strong>
                                </h2>
                                <script>
                                    var namaPoli = '{{ $namaPoli }}';
                                </script>
                                <h2>
                                    <strong id="namaDokter">
                                        {{ $namaDokter }}
                                    </strong>
                                </h2>
                                <script>
                                    function checkDataChanges() {
                                        var lastNoReg = localStorage.getItem('lastNoReg');
                                        $.ajax({
                                            url: '{{ route('antrian.checkDataChanges', ['kd_poli' => $kd_poli, 'kd_dokter' => $kd_dokter]) }}',
                                            method: 'POST',
                                            data: {
                                                _token: '{{ csrf_token() }}',
                                                lastNoReg: lastNoReg,
                                                namaPoli: namaPoli,
                                            },
                                            success: function (response) {
                                                console.log('Response from server:', response);
                                                if (response.changed) {
                                                    console.log('Data Changed:', response.data);
                                                    localStorage.setItem('lastNoReg', response.data.no_reg);
                                                    playVoice(response.data.no_reg, response.data);
                                                } else {
                                                    console.log('No Data Change');
                                                }
                                            },
                                            error: function (error) {
                                                console.error('Error checking data changes:', error);
                                            },
                                        });
                                    }
                                    function playVoice(noReg, data) {
                                        var textToSpeech = 'Nomor antrian ' + noReg + ' pada ' + namaPoli;
                                        responsiveVoice.speak(textToSpeech, 'Indonesian Female', { rate: 0.7 });
                                    }
                                    document.addEventListener('DOMContentLoaded', function () {
                                        checkDataChanges();
                                    });
                                    setInterval(function () {
                                        location.reload();
                                    }, 20000);
                                </script>
                            </div>
                        @endif
                    </div>
                    <div class="col text-white scrollable-content">
                        <div class="row">
                            <div class="col-12">
                                <div style="height: 1000px; z-index: 1; position: relative"></div>
                                @foreach ($antrianPasien as $pasien)
                                    <div class="container pb-3">
                                        <div class="row bg-pandan">
                                            <div class="col-5 align-self-center px-4">
                                                <h1 class="display-1">
                                                    <strong>
                                                        {{ $pasien->no_reg }}
                                                    </strong>
                                                </h1>
                                            </div>
                                            <div class="col-7">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <h1 class="display-4">
                                                            {{ $namaPoli }}
                                                        </h1>
                                                    </div>
                                                    <div class="col-12">
                                                        <h1 class="display-3">
                                                            {{ $namaDokter }}
                                                        </h1>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                function refreshPage() {
                    setTimeout(function () {
                        location.reload(true);
                    }, 32000);
                }
                document.addEventListener('DOMContentLoaded', function () {
                    refreshPage();
                });
            </script>
            <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
            <script src="https://code.responsivevoice.org/responsivevoice.js?key=OGPOBj1g"></script>
        </body>
    </html>
@endif
