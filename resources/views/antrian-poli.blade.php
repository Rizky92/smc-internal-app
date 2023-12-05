@if ($antrianPasien)
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('css/adminlte.min.css') }}">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/antrian-poli.css') }}">
    <title>{{ config('app.name') }}</title>
  </head>
  <body>
    <header class="d-flex flex-wrap justify-content-center pb-3 mb-4 border-bottom shadow header">
        <div class="container-fluid d-flex justify-content-center">
            <img src="{{ asset('img/logo.png') }}" alt="" width="100" height="80" class="d-inline-block align-text-top">
            <a class="text-pandan text-decoration-none" href="#">ANTRIAN POLIKLINIK RS SMC</a>
        </div>
    </header>
    <div class="container-fluid">
        <div class="row">
            <div class="col text-center">
                <div class="container-toast border shadow">
                    <div class="bg-pandan">
                        <h1 class="display-5 text-white">ANTREAN DIPANGGIL</h1>
                    </div>
                    @if ($nextAntrian)
                        <span>{{ $nextAntrian->no_reg }}</span>
                        <script>
                            function callPasien() {
                                let noAntrian = '{{ $nextAntrian->no_reg }}';
                                let poli = '{{ $namaPoli }}';
                                    
                                let message = `Nomor antrian ${noAntrian}, ${poli}`;
                        
                                responsiveVoice.speak(message, 'Indonesian Female', {
                                    rate: 0.7
                                });
                            }
                            document.addEventListener('DOMContentLoaded', function () {
                                callPasien();
                             });
                        </script>
                    @endif
                    <h2><strong>{{ $namaPoli }}</strong></h2>
                    <h2><strong>{{ $namaDokter }}</strong></h2>
                </div>
            </div>  
          <div class="col text-white scrollable-content">
            <div class="row">
                <div class="col-12">
                @foreach ($antrianPasien as $pasien)
                    <div class="container pb-3">
                        <div class="row bg-pandan">
                            <div class="col-5 align-self-center px-4">
                                <h1 class="display-1"><strong>{{ $pasien->no_reg }}</strong></h1>
                            </div>
                            <div class="col-7">
                                <div class="row">
                                    <div class="col-12">
                                        <h1 class="display-4">
                                            {{ $namaPoli }}
                                        </h1>
                                    </div>
                                    <div class="col-12">
                                        <h1 class="display-3">{{ $namaDokter }}</h1>
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
    <script src="https://code.responsivevoice.org/responsivevoice.js?key=OGPOBj1g"></script>
  </body>
</html>                       
@endif