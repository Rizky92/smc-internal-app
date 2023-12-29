@section('display-jadwal-dokter')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/jadwal.css') }}">
@endpush

<header class="d-flex flex-wrap justify-content-center py-3 mb-4 border-bottom shadow">
    <div class="container-fluid d-flex justify-content-center">
        <img src="{{ asset('img/logo.png') }}" alt="logo" width="120">
        <span>JADWAL DOKTER HARI INI</span>
    </div>
</header>
@if ($jadwal->isNotEmpty()) 
    <table class="table table-bordered">
        <thead class="thead bg-pandan text-white">
            <tr>
                <th width="30%">Nama Dokter</th>
                <th width="20%">Poliklinik</th>
                <th width="20%">Jam Mulai</th>
                <th width="20%">Jam Selesai</th>
                <th width="10%">Register</th>
            </tr>
        </thead>
    </table>
    <div id="scrollingContent">
        <table class="table table-bordered">
            <div class="padding"></div>
            <tbody>
                @foreach ($jadwal as $data)
                    <tr>
                        <td style="text-align: left;" width="30%">{{ $data->dokter->nm_dokter }}</td>
                        <td width="20%">{{ $data->poliklinik->nm_poli }}</td>
                        <td width="20%">{{ $data->jam_mulai }}</td>
                        <td width="20%">{{ $data->jam_selesai }}</td>
                        <td width="10%">{{ $data->register }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <script>
        function refreshPage() {
            setTimeout(function () {
                location.reload(true);
            }, 65000);
        }
        document.addEventListener('DOMContentLoaded', function () {
            refreshPage();
        });
    </script>
@else
    <p>Tidak ada jadwal dokter untuk hari ini.</p>
@endif
@endsection
