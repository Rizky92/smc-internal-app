<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge" />
        <link rel="stylesheet" href="{{ asset('css/dataTables.bootstrap4.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('css/responsive.bootstrap4.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('css/adminlte.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('css/jadwal.css') }}" />
        <title>{{ config('app.name') }}</title>
    </head>
    <body>
        <header class="d-flex flex-wrap justify-content-center py-3 mb-4 border-bottom shadow">
            <div class="container-fluid d-flex justify-content-center">
                <img src="{{ asset('img/logo.png') }}" alt="logo" width="120" />
                <span>RS SAMARINDA MEDIKA CITRA</span>
            </div>
        </header>
        @if ($jadwal->isNotEmpty())
            <div id="scrollingContent">
                <table class="table">
                    <thead class="thead bg-pandan text-white">
                        <tr>
                            <th>Nama Dokter</th>
                            <th>Poli Klinik</th>
                            <th>Jam Mulai</th>
                            <th>Jam Selesai</th>
                            <th>Register</th>
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
                                <td>{{ $data->poliklinik->nm_poli }}</td>
                                <td>{{ $data->jam_mulai }}</td>
                                <td>{{ $data->jam_selesai }}</td>
                                <td>
                                    {{ \App\Models\Perawatan\RegistrasiPasien::hitungData($data->kd_poli, $data->kd_dokter, now()->toDateString()) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p>Tidak ada jadwal dokter untuk hari ini.</p>
        @endif
    </body>
</html>
