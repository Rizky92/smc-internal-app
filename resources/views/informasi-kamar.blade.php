<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link rel="stylesheet" href="{{ asset('css/dataTables.bootstrap4.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('css/responsive.bootstrap4.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('css/adminlte.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('css/bed.css') }}" />
        <title>{{ config('app.name') }}</title>
    </head>
    <body>
        <header class="d-flex flex-wrap justify-content-center py-3 mb-4 border-bottom shadow">
            <div class="container-fluid d-flex justify-content-center">
                <img src="img/logo.png" alt="logo" width="120" />
                <span>BED MANAGEMENT</span>
            </div>
        </header>
        @if ($informasiKamar->count() > 0)
            <table class="table table-bordered table-striped text-white">
                <thead>
                    <tr>
                        <th width="30%">Bangsal</th>
                        <th width="20%">Kelas</th>
                        <th>Status</th>
                    </tr>
                </thead>
            </table>
            <div id="scrollingContent">
                <table class="table table-bordered table-striped">
                    <div class="padding"></div>
                    <tbody>
                        @foreach ($informasiKamar as $bangsal)
                            <tr>
                                <td width="30%">{{ $bangsal->nm_bangsal }}</td>
                                <td width="20%">{{ $bangsal->kelas }}</td>
                                <td>
                                    Terisi :
                                    {{ app(App\Http\Controllers\KamarController::class)->countOccupiedRooms($bangsal->kd_bangsal) }}
                                    | Tersedia :
                                    {{ app(App\Http\Controllers\KamarController::class)->countEmptyRooms($bangsal->kd_bangsal) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p>No data available</p>
        @endif

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
    </body>
</html>
