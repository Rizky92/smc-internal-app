<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="stylesheet" href="{{ asset('css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/jadwal.css') }}">


    <title>{{ config('app.name') }}</title>
</head>
<body>

    <header class="d-flex flex-wrap justify-content-center py-3 mb-4 border-bottom shadow">
        <div class="container-fluid d-flex justify-content-center">
            <img src="{{ asset('img/logo.png') }}" alt="" width="100" height="80" class="d-inline-block align-text-top">
            <h1 class="text-pandan bold">RS SAMARINDA MEDIKA CITRA</h1>
        </div>
    </header>
    @if ($jadwal->isNotEmpty()) 
    <div id="scrollingContent">
        <table class="table">
            <thead class="thead bg-pandan text-white">
                <tr>
                    <th scope="col">Nama Dokter</th>
                    <th scope="col">Poli Klinik</th>
                    <th scope="col">Jam Mulai</th>
                    <th scope="col">Jam Selesai</th>
                    <th scope="col">Register</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($jadwal as $data)
                    <tr>
                        <td>
                            <a href="{{ route('antrian.show', ['kd_poli' => $data->kd_poli, 'kd_dokter' => $data->kd_dokter]) }}">
                                {{ $data->dokter->nm_dokter }}
                            </a>
                        </td>
                        <td>{{ $data->poliklinik->nm_poli}}</td>
                        <td> {{ $data->jam_mulai }}</td>
                        <td> {{ $data->jam_selesai }}</td>
                        <td>{{ \App\Models\Perawatan\RegistrasiPasien::hitungData($data->kd_poli, $data->kd_dokter, now()->format('Y-m-d')) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Tidak ada jadwal dokter untuk hari ini.</p>
    @endif
    </div>
</body>
</html>