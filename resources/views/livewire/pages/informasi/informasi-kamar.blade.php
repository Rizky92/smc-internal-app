@push('styles')
    <link rel="stylesheet" href="{{ asset('css/bed.css') }}" />
@endpush

@section('informasi-kamar')
    <header class="d-flex flex-wrap justify-content-center mb-4 border-bottom shadow">
        <div class="container-fluid d-flex justify-content-center">
            <img src="{{ asset('img/logo.png') }}" alt="logo" width="100vh" height="auto" />
            <span class="header">KETERSEDIAAN KAMAR</span>
        </div>
    </header>
    <table class="table table-bordered table-striped text-white">
        <thead>
            <tr>
                <th width="40%">Bangsal</th>
                <th width="30%">Kelas</th>
                <th width="30%">Status</th>
            </tr>
        </thead>
    </table>
    <div id="scrollingContent">
        <table class="table table-bordered">
            <div class="padding"></div>
            <tbody>
                @forelse ($this->dataInformasiKamar as $item)
                    <tr>
                        <td width="40%">{{ $item->nm_bangsal }}</td>
                        <td width="30%">{{ $item->kelas }}</td>
                        <td width="30%">
                            Terisi: {{ $item->total_terisi }} | Tersedia:
                            {{ $item->total_tersedia }}
                        </td>
                    </tr>
                @empty
                    <tr colspan="10" style="height: 20%"></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @push('js')
        <script>
            function refreshPage() {
                setTimeout(function () {
                    location.reload(true);
                }, 40000);
            }
            document.addEventListener('DOMContentLoaded', function () {
                refreshPage();
            });
        </script>
    @endpush
@endsection
