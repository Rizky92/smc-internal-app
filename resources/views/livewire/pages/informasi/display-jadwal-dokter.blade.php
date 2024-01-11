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
    <table class="table table-bordered">
        <thead class="thead bg-pandan text-white">
            <tr>
                <th width="30%">Nama Dokter</th>
                <th width="20%">Poliklinik</th>
                <th width="15%">Jam Mulai</th>
                <th width="15%">Jam Selesai</th>
                <th width="10%">Register</th>
                <th width="10%">Kuota</th>
            </tr>
        </thead>
    </table>
    <div id="scrollingContent">
        <table class="table table-bordered">
            <div class="padding"></div>
            <tbody>
                @forelse ($this->dataJadwalDokter as $item)
                    <tr>
                        
                        <td style="text-align: left;" width="30%">{{ $item->nm_dokter }}</td>
                        <td width="20%">{{ $item->nm_poli }}</td>
                        <td width="15%">{{ $item->jam_mulai }}</td>
                        <td width="15%">{{ $item->jam_selesai }}</td>
                        <td width="10%">{{ $item->total_registrasi }}</td>
                        <td width="10%">{{ $item->kuota }}</td>
                        @empty
                        <tr></tr>
                        @endforelse
                    </tr>
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
@endsection

